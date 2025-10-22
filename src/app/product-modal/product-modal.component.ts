import { Component, Input, Output, EventEmitter, OnInit, DoCheck, SimpleChanges } from '@angular/core';
import { ProductsService } from '../services/products.service';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute, NavigationEnd, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
//start dalia
import { IndexeddbService } from '../services/indexeddb.service';
import { ChangeDetectorRef } from '@angular/core';
//end dalia

@Component({
  selector: 'app-product-modal',
  imports: [CommonModule],
  templateUrl: './product-modal.component.html',
  styleUrl: './product-modal.component.css'
})
export class ProductModalComponent implements OnInit {
  @Input() item: any;
  product: any;
  selectedProduct: any;
  @Input() src: any;
  selectedSize: any = null;
  quantity: number = 1;
  finalPrice: number = 0;
  note: string = ''; // Store user notes
  selectedAddonsByCategory: { [key: string]: any[] } = {}; // Stores selected addons per category
  addonValidationErrors: {
    [key: string]: {
      minError: boolean;
      maxError: boolean;
      maxLabel: string;
    }
  } = {};
  selectedAddons: any[] = [];
  addonCategories: any;
  @Input() orderId: string = '';
  currentRoute!: string;
  constructor(
    public activeModal: NgbActiveModal,
    private productService: ProductsService,
    private router: Router,
    //start dalia
    private dbService: IndexeddbService,
    private cdr: ChangeDetectorRef,
    //end dalia

    private route: ActivatedRoute
  ) {
    this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        this.currentRoute = event.url;
      }
    });

    this.currentRoute = this.router.url;
    console.log(this.currentRoute, 'currentRoute')
  }
  ngOnChanges(changes: SimpleChanges): void {
    const updatedOrders = JSON.parse(
      localStorage.getItem('savedOrders') || '[]'
    );
    this.productService.updateSavedOrders(updatedOrders);
  }


  ngOnInit(): void {
    console.log('iit');
    
    this.productService.product$.subscribe(product => {
      this.selectedProduct = product;

      if (!this.selectedSize && this.selectedProduct?.sizes?.length) {
        const defaultSize = this.selectedProduct.sizes.find((size: { default_size: any; }) => size.default_size);
        this.selectedSize = defaultSize || null;
      }

      this.updatePrice();
      this.initializeAddonValidation();
    });
    this.productService.savedOrders$.subscribe((orders) => {
      if (!orders || !this.orderId) return;

      const order = orders.find((o) => o.orderId === this.orderId);
      // console.log(this.orderId, order, ' this.orderId');
      if (!order) return;

      const item = order.items.find(
        (i: { dish_id: any }) => i.dish_id === this.selectedProduct?.id
      );
      if (!item) return;

      this.quantity = item.quantity;
      this.note = item.note;
      this.selectedSize = item.sizeId
        ? this.selectedProduct.sizes.find(
          (s: { id: any }) => s.id === item.sizeId
        )
        : null;

      this.selectedAddonsByCategory = {};
      if (item.addon_categories?.length) {
        item.addon_categories.forEach((cat: any) => {
          this.selectedAddonsByCategory[cat.id.toString()] = cat.addons || [];
        });
      }

      // Update the flat selectedAddons array (all addons across categories)
      this.selectedAddons = [];
      Object.values(this.selectedAddonsByCategory).forEach((addons: any[]) => {
        this.selectedAddons.push(...addons);
      });

      this.updatePrice();
    });

    if (this.selectedAddons?.length > 0) {
      this.initializeSelectedAddons();
    }

  }
  initializeSelectedAddons(): void {
    if (!this.selectedAddons?.length || !this.selectedProduct?.addon_categories)
      return;

    this.selectedAddonsByCategory = {};

    this.selectedProduct.addon_categories.forEach(
      (category: { id: { toString: () => any }; addons: any[] }) => {
        const categoryId = category.id.toString();
        this.selectedAddonsByCategory[categoryId] = [];

        category.addons.forEach((addon) => {
          const isSelected = this.selectedAddons.some(
            (selected) => +selected.id === +addon.id
          );
          if (isSelected) {
            this.selectedAddonsByCategory[categoryId].push(addon);
          }
        });
      }
    );
  }
  initializeAddonValidation(): void {
    if (!this.selectedProduct || !Array.isArray(this.selectedProduct.addon_categories)) {
      console.error("🚨 Error: addon_categories is missing or not an array!");
      return;
    }

    this.selectedAddonsByCategory = {}; // Reset selections
    this.addonValidationErrors = {}; // Reset validation errors

    this.selectedProduct.addon_categories.forEach((category: { id: any; min_addons: number; max_addons: number }) => {
      const categoryId = category.id.toString();

      this.selectedAddonsByCategory[categoryId] = []; // Initialize empty array for selections
      this.addonValidationErrors[categoryId] = {
        minError: category.min_addons > 0, // Invalid if min addons > 0
        maxError: false, // Assume max is valid initially
        maxLabel: `حد أقصي: ${category.max_addons}`, // Max limit label
      };

      console.log(`✅ Initialized category ${categoryId} (Min: ${category.min_addons}, Max: ${category.max_addons})`);
    });

    console.log("🚀 Addon validation initialized:", this.selectedAddonsByCategory);
  }
  toggleAddon(addon: any, event: any, category: { id: string | number; min_addons: number; max_addons: number }): void {
    const categoryId = category.id.toString();

    if (!this.selectedAddonsByCategory[categoryId]) {
      this.selectedAddonsByCategory[categoryId] = [];
    }

    if (event.target.checked) {
      if (this.selectedAddonsByCategory[categoryId].length < category.max_addons) {
        this.selectedAddonsByCategory[categoryId].push(addon);
        this.selectedAddons.push(addon);
      } else {
        // 🚨 If max reached, uncheck the new selection
        event.target.checked = false;
        return;
      }
    } else {
      // Remove the addon if unchecked
      this.selectedAddonsByCategory[categoryId] = this.selectedAddonsByCategory[categoryId].filter(a => a.id !== addon.id);
      this.selectedAddons = this.selectedAddons.filter(a => a.id !== addon.id);
    }

    this.validateMinMaxAddons(category);
    this.updatePrice();
  }


  validateMinMaxAddons(category: { id: string | number; min_addons: number; max_addons: number }): void {
    const categoryId = category.id.toString();
    const selectedCount = this.selectedAddonsByCategory[categoryId]?.length || 0;

    // 🚨 Check min & max errors
    this.addonValidationErrors[categoryId] = {
      minError: selectedCount < category.min_addons,
      maxError: selectedCount > category.max_addons,
      maxLabel: `حد أقصي: ${category.max_addons}`,
    };
  }

  canAddToCart(): boolean {
    return Object.values(this.addonValidationErrors).every(errors => !errors.minError && !errors.maxError);
  }


  // isAddonDisabled(category: any): boolean {
  //   return this.selectedAddonsByCategory[category.id]?.length >= category.max_addons;
  // }


  addToCart(): void {
    if (!this.canAddToCart()) {
      console.warn("🚨 Cannot add to cart: Minimum addon requirement not met!");
      return;
    }
    console.log("🚀 addToCart() called with:", {
      product: this.selectedProduct,
    });

    // Prepare dish details
    const dish = {
      id: this.selectedProduct.id,
      name: this.selectedProduct.name,
      description: this.selectedProduct.description,
      price: this.selectedProduct.price,
      currency_symbol: this.selectedProduct.currency_symbol || "ج.م",
      has_size: this.selectedProduct.sizes?.length > 0,
      has_addon: this.selectedProduct.addon_categories?.length > 0,
      image: this.selectedProduct.image,
      share_link: this.selectedProduct.share_link || '',
      is_favorites: this.selectedProduct.is_favorites || false,
      mostOrdered: this.selectedProduct.mostOrdered || false
    };

    // Prepare sizes data
    const sizes = this.selectedProduct.sizes?.map((size: { id: any; name: any; price: any; currency_symbol: any; default_size: any; }) => ({
      id: size.id,
      name: size.name,
      price: size.price,
      currency_symbol: size.currency_symbol || "ج.م",
      default_size: size.default_size || false
    })) || [];

    // Prepare addons data
    const addon_categories = this.selectedProduct.addon_categories?.map((category: { id: any; name: any; min_addons: any; max_addons: any; addons: { id: any; name: any; price: any; currency_symbol: any; }[]; }) => ({
      id: category.id,
      name: category.name,
      min_addons: category.min_addons || 0,
      max_addons: category.max_addons || 0,
      addons: category.addons?.map((addon: { id: any; name: any; price: any; currency_symbol: any; }) => ({
        id: addon.id,
        name: addon.name,
        price: addon.price,
        currency_symbol: addon.currency_symbol || "ج.م"
      })) || []
    })) || [];

    // Construct the final object
    const productToAdd = {
      dish: dish,
      sizes: sizes,
      addon_categories: addon_categories,
      selectedSize: this.selectedSize || null,
      selectedAddons: this.selectedAddons,
      quantity: this.quantity,
      finalPrice: this.finalPrice,
      note: this.note,
    };

    // Send data to cart service
    this.productService.addToCart(productToAdd);
    this.activeModal.dismiss(); // Close modal
    this.addNote = false;

  }

  addToHoldCart(): void {
    if (!this.canAddToCart()) {
      console.warn("🚨 Cannot add to cart: Minimum addon requirement not met!");
      return;
    }
    console.log("🚀 hold() called with:", {
      product: this.selectedProduct,
    });

    // Prepare dish details
    const dish = {
      id: this.selectedProduct.id,
      name: this.selectedProduct.name,
      description: this.selectedProduct.description,
      price: this.selectedProduct.price,
      currency_symbol: this.selectedProduct.currency_symbol || "ج.م",
      has_size: this.selectedProduct.sizes?.length > 0,
      has_addon: this.selectedProduct.addon_categories?.length > 0,
      image: this.selectedProduct.image,
      share_link: this.selectedProduct.share_link || '',
      is_favorites: this.selectedProduct.is_favorites || false,
      mostOrdered: this.selectedProduct.mostOrdered || false
    };

    // Prepare sizes data
    const sizes = this.selectedProduct.sizes?.map((size: { id: any; name: any; price: any; currency_symbol: any; default_size: any; }) => ({
      id: size.id,
      name: size.name,
      price: size.price,
      currency_symbol: size.currency_symbol || "ج.م",
      default_size: size.default_size || false
    })) || [];

    // Prepare addons data
    const addon_categories = this.selectedProduct.addon_categories?.map((category: { id: any; name: any; min_addons: any; max_addons: any; addons: { id: any; name: any; price: any; currency_symbol: any; }[]; }) => ({
      id: category.id,
      name: category.name,
      min_addons: category.min_addons || 0,
      max_addons: category.max_addons || 0,
      addons: category.addons?.map((addon: { id: any; name: any; price: any; currency_symbol: any; }) => ({
        id: addon.id,
        name: addon.name,
        price: addon.price,
        currency_symbol: addon.currency_symbol || "ج.م"
      })) || []
    })) || [];

    // Construct the final object
    const productToAdd = {
      dish: dish,
      sizes: sizes,
      addon_categories: addon_categories,
      selectedSize: this.selectedSize || null,
      selectedAddons: this.selectedAddons,
      quantity: this.quantity,
      finalPrice: this.finalPrice,
      note: this.note,
    };

    // Send data to cart service
    this.productService.addToHoldCart(productToAdd);
    this.activeModal.dismiss(); // Close modal
    this.addNote = false;

  }
  updateNote(event: any): void {
    this.note = event.target.value;
  }

  selectSize(size: any): void {
    this.selectedSize = size;
    this.updatePrice();
  }

  // toggleAddon(addon: any, event: any): void {
  //   if (event.target.checked) {
  //     this.selectedAddons.push(addon);
  //   } else {
  //     this.selectedAddons = this.selectedAddons.filter(a => a.id !== addon.id);
  //   }
  //   this.updatePrice();
  // }

  increaseQuantity(): void {
    this.quantity++;
    this.updatePrice();
  }

  decreaseQuantity(): void {
    if (this.quantity > 1) {
      this.quantity--;
      this.updatePrice();
    }
  }
  isAdding = false;

  // start dalia
  // handleAddToCart() {
  //   if (this.isAdding) return;   // ⛔ block double fire
  //   this.isAdding = true;

  //   const currentUrl = this.router.url;
  //   console.log('🧭 Current route:', currentUrl);

  //   if (currentUrl.includes('/onhold-orders/')) {
  //     console.log('📝 Adding to ON HOLD order');
  //     this.addToHoldCart();
  //   } else {
  //     console.log('🛍️ Adding to NEW cart');
  //     this.addToCart();
  //   }

  //   setTimeout(() => this.isAdding = false, 300); // reset guard
  // }

  handleAddToCart() {
    if (this.isAdding) return;   // ⛔ block double fire
    this.isAdding = true;

    const currentUrl = this.router.url;
    console.log('🧭 Current route:', currentUrl);

    const isHoldOrder = currentUrl.includes('/onhold-orders/');
    console.log(isHoldOrder ? '📝 Adding to ON HOLD order' : '🛍️ Adding to NEW cart');

    if (!this.canAddToCart()) {
      console.warn("🚨 Cannot add to cart: Minimum addon requirement not met!");
      this.isAdding = false;
      return;
    }

    // Prepare the cart item for IndexedDB
    const cartItem = this.prepareCartItemForIndexedDB(isHoldOrder);

    if (navigator.onLine) {
      // Online: Use the original functions and also store in IndexedDB
      try {
        if (isHoldOrder) {
          this.addToHoldCart(); // Call the original method
        } else {
          this.addToCart(); // Call the original method
        }

        // Also store in IndexedDB with sync status
        cartItem.isSynced = true;
        this.dbService.addToCart(cartItem)
          .then(() => {
            console.log('✅ Item also stored in IndexedDB');
          })
          .catch(error => {
            console.error('❌ Error storing in IndexedDB:', error);
          });

      } catch (error) {
        console.error('❌ Online cart failed, storing offline:', error);

        // Fallback to offline storage
        cartItem.isSynced = false;
        this.storeOfflineCartItem(cartItem, isHoldOrder);
      }

      this.cdr.detectChanges();
    } else {
      // Offline: Store in IndexedDB only
      cartItem.isSynced = false;
      this.storeOfflineCartItem(cartItem, isHoldOrder);
      this.cdr.detectChanges();
    }

    setTimeout(() => this.isAdding = false, 300); // reset guard
  }
  // Helper method to prepare cart item for IndexedDB
  private prepareCartItemForIndexedDB(isHoldOrder: boolean = false): any {
    // Prepare dish details
    const dish = {
      id: this.selectedProduct.id,
      name: this.selectedProduct.name,
      description: this.selectedProduct.description,
      price: this.selectedProduct.price,
      currency_symbol: this.selectedProduct.currency_symbol || "ج.م",
      has_size: this.selectedProduct.sizes?.length > 0,
      has_addon: this.selectedProduct.addon_categories?.length > 0,
      image: this.selectedProduct.image,
      share_link: this.selectedProduct.share_link || '',
      is_favorites: this.selectedProduct.is_favorites || false,
      mostOrdered: this.selectedProduct.mostOrdered || false
    };

    // Prepare sizes data
    const sizes = this.selectedProduct.sizes?.map((size: any) => ({
      id: size.id,
      name: size.name,
      price: size.price,
      currency_symbol: size.currency_symbol || "ج.م",
      default_size: size.default_size || false
    })) || [];

    // Prepare addons data
    const addon_categories = this.selectedProduct.addon_categories?.map((category: any) => ({
      id: category.id,
      name: category.name,
      min_addons: category.min_addons || 0,
      max_addons: category.max_addons || 0,
      addons: category.addons?.map((addon: any) => ({
        id: addon.id,
        name: addon.name,
        price: addon.price,
        currency_symbol: addon.currency_symbol || "ج.م"
      })) || []
    })) || [];

    // Generate unique ID for this specific combination
    const generateUniqueId = (): string => {
      const baseId = this.selectedProduct.id;
      const sizeId = this.selectedSize ? `-size-${this.selectedSize.id}` : '';
      const addonsIds = this.selectedAddons && this.selectedAddons.length > 0
        ? `-addons-${this.selectedAddons.map((a: any) => a.id).sort().join('-')}`
        : '';
      return `${baseId}${sizeId}${addonsIds}`;
    };

    return {
      dish: dish,
      sizes: sizes,
      addon_categories: addon_categories,
      selectedSize: this.selectedSize || null,
      selectedAddons: this.selectedAddons || [],
      quantity: this.quantity || 1,
      finalPrice: this.finalPrice,
      note: this.note || '',
      uniqueId: generateUniqueId(),
      addedAt: new Date().toISOString(),
      isSynced: navigator.onLine,
      isHoldOrder: isHoldOrder,
      holdOrderId: isHoldOrder ? this.extractHoldOrderIdFromUrl() : null
    };
  }
  // Helper method to extract hold order ID from URL
  private extractHoldOrderIdFromUrl(): string {
    const url = this.router.url;
    const match = url.match(/\/onhold-orders\/([^\/]+)/);
    return match ? match[1] : '';
  }
  // Helper method to store offline cart item
  private storeOfflineCartItem(cartItem: any, isHoldOrder: boolean) {
    this.dbService.addToCart(cartItem)
      .then(cartItemId => {
        console.log('✅ Item stored in IndexedDB with ID:', cartItemId);

        // Show appropriate message and close modal
        const message = isHoldOrder
          ? 'تم إضافة المنتج إلى الطلب المؤقت (وضع عدم الاتصال)'
          : 'تم إضافة المنتج إلى السلة (وضع عدم الاتصال)';
        this.showNotification(message);

        // Close modal only for offline storage (online will be closed by original methods)
        this.activeModal.dismiss();
        this.addNote = false;
      })
      .catch(error => {
        console.error('❌ Error storing in IndexedDB:', error);
        const message = isHoldOrder
          ? 'فشل في إضافة المنتج إلى الطلب المؤقت'
          : 'فشل في إضافة المنتج إلى السلة';
        this.showNotification(message, 'error');

        // Close modal on error too
        this.activeModal.dismiss();
        this.addNote = false;
      });
  }
  // Helper method to show notifications
  private showNotification(message: string, type: 'success' | 'error' = 'success') {
    console.log(type === 'success' ? '✅' : '❌', message);
    // You can implement a proper notification system here
    // Example: this.toastr.success(message) or this.toastr.error(message)
  }

  // end dalia
  // addToHoldCart(): void {
  //   if (!this.canAddToCart()) {
  //     console.warn("🚨 Cannot add to cart: Minimum addon requirement not met!");
  //     return;
  //   }
  //   console.log("🚀 addToCart() called with:", {
  //     product: this.selectedProduct,
  //   });

  //   // Prepare dish details
  //   const dish = {
  //     id: this.selectedProduct.id,
  //     name: this.selectedProduct.name,
  //     description: this.selectedProduct.description,
  //     price: this.selectedProduct.price,
  //     currency_symbol: this.selectedProduct.currency_symbol || "ج.م",
  //     has_size: this.selectedProduct.sizes?.length > 0,
  //     has_addon: this.selectedProduct.addon_categories?.length > 0,
  //     image: this.selectedProduct.image,
  //     share_link: this.selectedProduct.share_link || '',
  //     is_favorites: this.selectedProduct.is_favorites || false,
  //     mostOrdered: this.selectedProduct.mostOrdered || false
  //   };

  //   // Prepare sizes data
  //   const sizes = this.selectedProduct.sizes?.map((size: { id: any; name: any; price: any; currency_symbol: any; default_size: any; }) => ({
  //     id: size.id,
  //     name: size.name,
  //     price: size.price,
  //     currency_symbol: size.currency_symbol || "ج.م",
  //     default_size: size.default_size || false
  //   })) || [];

  //   // Prepare addons data
  //   const addon_categories = this.selectedProduct.addon_categories?.map((category: { id: any; name: any; min_addons: any; max_addons: any; addons: { id: any; name: any; price: any; currency_symbol: any; }[]; }) => ({
  //     id: category.id,
  //     name: category.name,
  //     min_addons: category.min_addons || 0,
  //     max_addons: category.max_addons || 0,
  //     addons: category.addons?.map((addon: { id: any; name: any; price: any; currency_symbol: any; }) => ({
  //       id: addon.id,
  //       name: addon.name,
  //       price: addon.price,
  //       currency_symbol: addon.currency_symbol || "ج.م"
  //     })) || []
  //   })) || [];

  //   // Construct the final object
  //   const productToAdd = {
  //     dish: dish,
  //     sizes: sizes,
  //     addon_categories: addon_categories,
  //     selectedSize: this.selectedSize || null,
  //     selectedAddons: this.selectedAddons,
  //     quantity: this.quantity,
  //     finalPrice: this.finalPrice,
  //     note: this.note,
  //   };

  //   // Send data to cart service
  //   this.productService.addToCart(productToAdd);
  //   this.activeModal.dismiss(); // Close modal
  //   this.addNote=false;

  // }
  //   addToHoldCart(): void {
  //   const orderId = this.orderId || localStorage.getItem('onHoldItemId');
  //   const savedOrdersRaw = localStorage.getItem('savedOrders');
  //   const savedOrders = savedOrdersRaw ? JSON.parse(savedOrdersRaw) : [];

  //   if (!orderId) {
  //     console.warn('❌ Missing orderId.');
  //     return;
  //   }

  //   const orderIndex = savedOrders.findIndex((order: any) => {
  //     const id = order.orderId ?? order.order_id;
  //     return id?.toString() === orderId.toString();
  //   });

  //   if (orderIndex === -1) {
  //     console.warn('⚠️ Order not found in savedOrders:', orderId);
  //     return;
  //   }

  //   const order = savedOrders[orderIndex];

  //   // Create new item
  //   const newItem = {
  //     dish_name: this.selectedProduct.name,
  //     dish_order: -1,
  //     dish_image: this.selectedProduct.image,
  //     dish_desc: this.selectedProduct.description,
  //     dish_price: this.selectedProduct.price,
  //     final_price: this.finalPrice,
  //     dish_id: this.selectedProduct.id,
  //     quantity: this.quantity,
  //     size_name: this.selectedSize?.name || null,
  //     size_price: this.selectedSize?.price || null,
  //     sizeId: this.selectedSize?.id || null,
  //     note: this.note || '',
  //     addon_categories: this.selectedProduct.addon_categories.map((cat: any) => ({
  //       id: cat.id,
  //       name: cat.name,
  //       addons: this.selectedAddonsByCategory[cat.id.toString()] || [],
  //     })),
  //   };

  //   // Helper: flatten addons for comparison
  //   const flattenAddons = (addonCats: any[]) =>
  //     addonCats
  //       ?.flatMap((cat: any) =>
  //         (cat.addons || []).map((a: any) => `${a.id}-${a.name}-${a.price}`)
  //       )
  //       .sort() || [];

  //   const newItemAddonsFlat = flattenAddons(newItem.addon_categories);

  //   // Check if the item already exists
  //   const existingIndex = order.items.findIndex((item: any) => {
  //     const sameAddons =
  //       JSON.stringify(flattenAddons(item.addon_categories)) === JSON.stringify(newItemAddonsFlat);
  //     return (
  //       item.dish_id === newItem.dish_id &&
  //       item.sizeId === newItem.sizeId &&
  //       item.note === newItem.note &&
  //       sameAddons
  //     );
  //   });

  //   if (existingIndex !== -1) {
  //     // Update quantity
  //     const existingItem = order.items[existingIndex];
  //     existingItem.quantity += newItem.quantity;

  //     if (existingItem.quantity <= 0) {
  //       order.items.splice(existingIndex, 1); // Remove item if quantity <= 0
  //       console.log('🗑️ Removed item due to zero quantity.');
  //     } else {
  //       console.log('✏️ Updated item quantity:', existingItem);
  //     }
  //   } else {
  //     // Add new item
  //     order.items.push(newItem);
  //     console.log('✅ Added new item:', newItem);
  //   }

  //   // Update savedOrders and sync
  //   savedOrders[orderIndex] = order;
  //   localStorage.setItem('savedOrders', JSON.stringify(savedOrders));
  //   this.productService.updateSavedOrders(savedOrders);

  //   // Close modal
  //   this.activeModal.dismiss();
  // }
  updatePrice(): void {
    let basePrice = this.selectedProduct?.price || 0;
    if (this.selectedSize) {
      basePrice = this.selectedSize.price;
    }
    const addonPrice = this.selectedAddons.reduce((sum, addon) => sum + addon.price, 0);
    this.finalPrice = (basePrice + addonPrice) * this.quantity;
  }
  addNote: boolean = false;
  ShowAddNote() {
    this.addNote = true
  }

}
