import { CommonModule } from '@angular/common';
import { Component, Input, OnDestroy, OnInit, ViewChild, NgZone } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { ProductCardComponent } from '../product-card/product-card.component';
import { ProductsService } from '../services/products.service';
import { OfferCardComponent } from '../offer-card/offer-card.component';
import { FormsModule } from '@angular/forms';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { NewDishService } from '../services/pusher/newDish';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { finalize } from 'rxjs';
import { IndexeddbService } from '../services/indexeddb.service';

import { ChangeDetectorRef } from '@angular/core';

@Component({
  selector: 'app-categoriesLite',
  imports: [
    CommonModule,
    ProductCardComponent,
    FormsModule,
    ShowLoaderUntilPageLoadedDirective
  ],
  templateUrl: './categoriesLite.component.html',
  styleUrls: ['./categoriesLite.component.css'],
})
export class CategoriesLiteComponent implements OnInit, OnDestroy {
  products: any;
  offers: any;
  categories: any[] = [];
  categoriesLite: any[] = [];
  selectedCategory: any = null;
  filteredOrders: any[] = [];
  filterCategories: any[] = [];
  searchOrderCategoreis: string | number | any = '';
  selectedCategoryProducts: any[] = [];
  searchOrderNumber: string | number | any = '';
  isAllLoading: boolean = true;
  isCategoryLoading: boolean = false;
  isOnline: boolean = navigator.onLine;
  errorMessage: string = '';
  @Input() item: any;
  @Input() offer: any;
  @ViewChild('closebutton') closebutton: any;
  constructor(private productsRequestService: ProductsService, private modalService: NgbModal,
    private newDish: NewDishService,private dbService: IndexeddbService,
    private cdr: ChangeDetectorRef, private ngZone: NgZone) { }

  ngOnInit(): void {
    //start dalia
    // Add event listeners for online/offline status
    window.addEventListener('online', this.handleOnlineStatus.bind(this));
    window.addEventListener('offline', this.handleOnlineStatus.bind(this));


    //end dalia
    // const hasCachedCategories = this.loadCategoriesFromCache();
    // this.fetchMenuData(hasCachedCategories);
    this.fetchMenuData();
    this.listenTonewDish()
  }
  // ngDoCheck(){
  //   this.fetchMenuData();
  // }
  ngOnChanges(): void {
    this.fetchMenuData();
  }

  // start dalia
  // fetchMenuData(): void {
  //   this.isAllLoading = false;
  //   this.productsRequestService.getMenuDishes().pipe(
  //     finalize(() => {
  //       this.isAllLoading = true
  //     })
  //   ).subscribe((response: any) => {
  //     if (response && response.status && response.data) {
  //       this.categories = response.data;
  //       this.filterCategories = [...this.categories]; // Update filtered categories
  //       this.filteredOrders = []; // Clear previous orders
  //       if (this.categories.length > 0) {
  //         this.onCategorySelect(this.categories[0]); // Select first category by default
  //       }
  //     } else {
  //       console.error("Invalid response format", response);
  //     }
  //   });

  // }

  fetchMenuData() {
    if (!this.isOnline) {
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
      this.loadCategoriesFromIndexedDB();
      // return;
    }
    else{
      this.fetchFromAPILite();
      //

    }
  }
  private loadCategoriesFromIndexedDB() {
    this.dbService.getAll('categories')
      .then(categories => {
        if (categories && categories.length > 0) {
          this.categories = categories;
          this.processCategories();
        }
      })
      .catch(error => {
        console.error('Error loading categories from IndexedDB:', error);
      });
  }

  private backgroundSyncInProgress = false;

    private fetchFromAPI_all(runSilently: boolean = false) {

      console.log('fetchFromAPI_all');
    if (!runSilently) {
      this.isAllLoading = false;
    }
    this.productsRequestService.getMenuDishes().pipe(
      finalize(() => {
        if (!runSilently) {
          this.isAllLoading = true;
        }
        if (runSilently) {
          this.backgroundSyncInProgress = false;
        }
      })
    ).subscribe((response: any) => {
      if (response && response.status && response.data) {
        this.dbService.saveData('categories', response.data);

        console.log('saved to indexeddb');

        if (!runSilently) {
          this.errorMessage = '';
        }
      } else {
        console.error("Invalid response format", response);
        if (!runSilently) {
          this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
        }

      }
    }, (error) => {
      console.error('API fetch failed, trying offline data:', error);
      if (!runSilently) {
        this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
      }

    });
  }


  private fetchFromAPILite() {
    this.errorMessage = '';
    this.productsRequestService.getMenuCategoriesLite().pipe(
      finalize(() => {
        this.isAllLoading = true;
      })
    ).subscribe((response: any) => {
      if (response && response.status && Array.isArray(response.data)) {
        this.categories = response.data;
        this.processCategories();
        this.errorMessage = '';
      } else {
        console.error("Invalid response format", response);
        this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
      }
    }, (error) => {
      console.error('API fetch failed, trying offline data:', error);
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
    });
  }

  getProduct(product: any): any {

    // console.log('Original Product:', product);
    if (localStorage.getItem('selectedOrderType') === 'talabat') {
      if (Array.isArray(product.Id_menus_integrations) && product.Id_menus_integrations.length > 0) {
        for (let integration of product.Id_menus_integrations) {
          if (integration.name_en?.toLowerCase().includes('talabat')) {
            console.log('✅ Talabat integration found:', integration);

            // تحديث السعر الأساسي للطبق
            const newPrice = integration.menus_integration_dishs?.[0]?.price || product.price;
            product.price = parseFloat(newPrice);

            // تحديث الأسعار داخل الـ sizes
            if (Array.isArray(product.sizes) && Array.isArray(integration.menus_integration_dish_sizes)) {
              product.sizes = product.sizes.map((size: any) => {
                const matchedSize = integration.menus_integration_dish_sizes.find(
                  (s: any) => s.branch_menu_size_id === size.id
                );
                if (matchedSize) {
                  return { ...size, price: parseFloat(matchedSize.price) };
                }
                return size;
              });
            }

            // تحديث الأسعار داخل الـ addons
            if (Array.isArray(product.addon_categories) && Array.isArray(integration.menus_integration_dish_addons)) {
              product.addon_categories = product.addon_categories.map((category: any) => ({
                ...category,
                addons: category.addons.map((addon: any) => {
                  const matchedAddon = integration.menus_integration_dish_addons.find(
                    (a: any) => a.branch_menu_addon_id === addon.id
                  );
                  if (matchedAddon) {
                    return { ...addon, price: parseFloat(matchedAddon.price) };
                  }
                  return addon;
                }),
              }));
            }
          }
        }
      }
    }

    return product;
  }
  private processCategories() {
    if(localStorage.getItem('selectedOrderType') === 'talabat'){
      this.categories = this.categories.filter(cat => cat.is_integration === true);
    }

    this.categories = this.categories.map(category => {
      if (Array.isArray(category.dishes) && category.dishes.length > 0) {
        const normalizedDishes = this.normalizeDishesPayload(category.dishes)
          .map(dish => this.getProduct({ ...dish }));

        return {
          ...category,
          dishes: normalizedDishes
        };
      }

      return category;
    });

    this.filterCategories = [...this.categories];

    console.log('Fetched Categories:', this.categories);
    this.filteredOrders = [];

    if (this.categories.length > 0) {
      const currentCategoryId = this.selectedCategory?.id;
      const categoryToSelect = currentCategoryId
        ? this.categories.find(cat => cat.id === currentCategoryId)
        : this.categories[0];

      if (categoryToSelect) {
        this.onCategorySelect(categoryToSelect, true);
      } else {
        this.onCategorySelect(this.categories[0], true);
      }
    }
  }

  private handleOnlineStatus() {
    this.isOnline = navigator.onLine;
    if (this.isOnline) {
      this.errorMessage = '';
      this.fetchMenuData();
      this.runFullCategoriesSyncInBackground();
    } else {
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
    }
    this.cdr.detectChanges();
  }

  private runFullCategoriesSyncInBackground(): void {
    if (this.backgroundSyncInProgress || !this.isOnline) {
      return;
    }

    this.backgroundSyncInProgress = true;
    this.ngZone.runOutsideAngular(() => this.fetchFromAPI_all(true));
  }
  //end dalia


  onCategorySelect(category: any, skipModalClose: boolean = false): void {
    if (!category) {
      this.selectedCategory = null;
      this.selectedCategoryProducts = [];
      this.filteredOrders = [];
      return;
    }

    this.selectedCategory = category;

    console.log("cat ddd" ,category);

    if (Array.isArray(category.dishes) && category.dishes.length > 0) {
      this.applyCategoryDishes(category, category.dishes, skipModalClose);
    }

    if (!Array.isArray(category.dishes) || category.dishes.length === 0) {
      this.selectedCategoryProducts = [];
      this.filteredOrders = [];
    }

    this.fetchCategoryDishes(category, skipModalClose);
  }

  private fetchCategoryDishes(category: any, skipModalClose: boolean): void {
    if (!category?.id || !this.isOnline) {
      return;
    }

    this.isCategoryLoading = true;
    this.productsRequestService.getMenuDishesLite(category.id).pipe(
      finalize(() => {
        this.isCategoryLoading = false;
        this.cdr.detectChanges();
      })
    ).subscribe((response: any) => {
      if (response && response.status) {
        const data = response.data;
        const dishesPayload = Array.isArray(data)
          ? data
          : Array.isArray(data?.dishes)
            ? data.dishes
            : Array.isArray(data?.items)
              ? data.items
              : [];
              // this.dbService.saveData('categories', data);
        if (Array.isArray(dishesPayload)) {
          this.applyCategoryDishes(category, dishesPayload, skipModalClose);
          return;
        }
      }

      console.error('Invalid dishes response for category', category, response);
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
    }, (error) => {
      console.error(`Failed to fetch dishes for category ${category.id}`, error);
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
    });
  }

  private applyCategoryDishes(category: any, dishesPayload: any[], skipModalClose: boolean): void {
    const normalizedDishes = this.normalizeDishesPayload(dishesPayload);
    const pricedDishes = normalizedDishes.map(dish => this.getProduct({ ...dish }));

    category.dishes = pricedDishes;
    // this.errorMessage = '';

    if (this.selectedCategory?.id !== category.id) {
      return;
    }



    this.selectedCategoryProducts = pricedDishes;
    if(localStorage.getItem('selectedOrderType') === 'talabat'){
      this.selectedCategoryProducts = this.selectedCategoryProducts.filter(dish => dish.is_integration === true);
    }

    console.log('Selected Category Products:', this.selectedCategoryProducts);


    this.filteredOrders = [...this.selectedCategoryProducts];
    this.filterCategories = [...this.categories];

    if (!skipModalClose && this.closebutton?.nativeElement) {
      this.closebutton.nativeElement.click();
    }
  }

  private normalizeDishesPayload(dishesPayload: any[]): any[] {
    return (Array.isArray(dishesPayload) ? dishesPayload : [])
      .map((entry: any) => {
        if (entry && entry.dish) {
          return {
            ...entry.dish,
            sizes: Array.isArray(entry.sizes) ? entry.sizes : [],
            addon_categories: Array.isArray(entry.addon_categories) ? entry.addon_categories : [],
          };
        }
        return {
          ...entry,
          sizes: Array.isArray(entry?.sizes) ? entry.sizes : [],
          addon_categories: Array.isArray(entry?.addon_categories) ? entry.addon_categories : [],
        };
      })
      .filter((dish: any) => this.isDishActive(dish));
  }

  isDishActive(dish: any): boolean {
    return dish.is_active !== false; // Adjust based on API response field
  }


  filterOrders() {
    const searchId = Number(this.searchOrderNumber);

    let filtered = [...this.selectedCategoryProducts].filter((dish) =>
      this.isDishActive(dish) // ✅ Remove inactive dishes
    );

    if (this.searchOrderNumber && !isNaN(searchId)) {
      filtered = filtered.filter((order) => order.id === searchId);
    } else if (this.searchOrderNumber) {
      filtered = filtered.filter((order) =>
        order.name.toLowerCase().includes(this.searchOrderNumber.toLowerCase())
      );
    }

    this.filteredOrders = filtered;
  }



  filterOrderCategories() {
    const searchId = Number(this.searchOrderCategoreis);

    if (this.searchOrderCategoreis && !isNaN(searchId)) {
      this.filterCategories = this.categories.filter(
        (order) => order.id === searchId
      );
    } else if (this.searchOrderCategoreis) {
      this.filterCategories = this.categories.filter((order) =>
        order.name.toLowerCase().includes(this.searchOrderCategoreis.toLowerCase())
      );
    } else {
      this.filterCategories = [...this.categories];
    }

  }

  recieveFromProduct(id: any): void {
    this.products = this.products.filter(
      (product: { id: any }) => product.id !== id
    );
  }

  clearSelection(): void {
    this.selectedCategory = null;
    this.filteredOrders = [];
  }

  trackById(index: number, product: any): any {
    return product.id;
  }
  listenTonewDish() {
    this.newDish.listenToNewdish();

    this.newDish.dishAdded$.subscribe((newDishWrapper) => {
      const newDish = newDishWrapper.dish;
      console.log('New dish received:', newDish);

      const targetCategoryId = newDish.branch_menu_category_id;
      const targetCategory = this.categories.find((cat: any) => cat.id === targetCategoryId);

      if (targetCategory) {
        // Push the dish into the right category
        targetCategory.dishes.unshift({
          dish: newDish,
          sizes: [],
          addon_categories: []
        });

        // If user is currently viewing that category, update the view
        if (this.selectedCategory && this.selectedCategory.id === targetCategoryId) {
          this.selectedCategoryProducts = targetCategory.dishes
            .filter((d: any) => d && d.dish)
            .map((d: any) => ({
              ...d.dish,
              sizes: Array.isArray(d.sizes) ? d.sizes : [],
              addon_categories: Array.isArray(d.addon_categories) ? d.addon_categories : [],
            }))
            .filter((dish: any) => this.isDishActive(dish));

          this.filteredOrders = [...this.selectedCategoryProducts];
        }

        console.log('Dish inserted into category:', targetCategory.name);
      } else {
        console.warn('Category not found for new dish:', newDish);
      }
    });
  }
  ngOnDestroy(): void {
    this.newDish.stopListening();
  }
}
