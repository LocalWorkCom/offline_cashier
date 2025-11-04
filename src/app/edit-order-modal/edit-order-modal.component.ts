import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { baseUrl } from '../environment';
import { AuthService } from '../services/auth.service';
import { ProductsService } from '../services/products.service';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute, NavigationEnd, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { finalize } from 'rxjs';
declare var bootstrap: any;

@Component({
  selector: 'app-edit-order-modal',
  imports: [CommonModule, FormsModule],
  templateUrl: './edit-order-modal.component.html',
  styleUrl: './edit-order-modal.component.css',
})
export class EditOrderModalComponent implements OnInit {
    isSubmitting: boolean = false;

  @Input() itemId!: number;
  apiUrl = `${baseUrl}`;
  @Input() public selectedItem: any;
  isLoading = true;
  error: string | null = null;
  note: string = '';
  orderId!: string;
  quantity: number = 1;
  finalPrice: number = 0;
  selectedSize: any = null;
  selectedAddonsByCategory: { [key: string]: any[] } = {};
  selectedAddons: any[] = [];
  addonValidationErrors: any = {};
  addNote!: boolean;
  loading: boolean = false;

  constructor(
    private http: HttpClient,
    public actvModal: NgbActiveModal,
    authService: AuthService
  ) {}

  ngOnInit(): void {
    if (this.itemId) this.loadItem();
    console.log(this.selectedItem, this.itemId);

    if (!this.selectedItem?.dish) return;

    const dish = this.selectedItem;

    this.quantity = dish.quantity || 1;
    this.note = dish.note || '';

    // ✅ set default size
    if (!this.selectedSize && dish?.sizes?.length) {
      const defaultSize = dish.sizes.find((s: any) => s.default_size);
      this.selectedSize = this.selectedItem.selected_size
        ? dish.sizes.find((s: any) => s.id === this.selectedItem.selected_size)
        : defaultSize || null;
    }

    // ✅ preload addons if already selected
    this.selectedAddonsByCategory = {};
    if (dish.addon_categories?.length) {
      dish.addon_categories.forEach((cat: any) => {
        this.selectedAddonsByCategory[cat.id.toString()] =
          cat.addons?.filter((a: any) => a.selected) || [];
      });
    }

    // Flatten into one array
    this.selectedAddons = [];
    Object.values(this.selectedAddonsByCategory).forEach((addons: any[]) => {
      this.selectedAddons.push(...addons);
    });
    if (this.note && this.note.trim() !== '') {
      this.addNote = true;
    } else {
      this.addNote = false;
    }

    this.updatePrice();
    this.initializeAddonValidation();
  }
  private loadItem() {
    console.log('AAAAAAAAAAAAAAAAAA');
    this.loading = true;
    const url = `${this.apiUrl}api/orders/cashier/order-view-item/api/${this.itemId}`;

    this.http
      .get(url)
      .pipe(
        finalize(() => {
          this.loading = false;
        })
      )
      .subscribe({
        next: (res: any) => {
          if (res.status) {
            this.selectedItem = res.data;
            this.hydrateFromApi(); // ✅ run after data arrives
          } else {
            this.error = 'لم يتم العثور على تفاصيل الطلب';
          }
          this.isLoading = false;
        },
        error: (err) => {
          console.error('❌ Error fetching item details', err);
          this.error = 'حدث خطأ أثناء تحميل البيانات';
          this.isLoading = false;
        },
      });
  }
  private hydrateFromApi(): void {
    if (!this.selectedItem?.dish) return;

    const dish = this.selectedItem.dish;

    // qty & note
    this.quantity = Number(dish.quantity) || 1;
    this.note = dish.note || '';
    this.addNote = !!this.note;
    // sizes → prefer checked, then default_size
    const preSize =
      this.selectedItem.sizes?.find((s: any) => s.checked) ||
      this.selectedItem.sizes?.find((s: any) => s.default_size) ||
      null;

    this.selectedSize = preSize;
    this.selectedItem.selected_size = preSize?.id ?? null;

    // addons → from checked
    this.selectedAddonsByCategory = {};
    this.selectedAddons = [];

    (this.selectedItem.addon_categories || []).forEach((cat: any) => {
      const selected = (cat.addons || []).filter((a: any) => a.checked);
      this.selectedAddonsByCategory[cat.id.toString()] = selected;
      this.selectedAddons.push(...selected);
    });

    this.initializeAddonValidation();
    this.updatePrice(); // ✅ compute initial price now
  }

  selectSize(size: any): void {
    this.selectedSize = size;
    this.selectedItem.selected_size = size?.id ?? null;
    (this.selectedItem.sizes || []).forEach(
      (s: any) => (s.checked = s.id === size?.id)
    );
    this.updatePrice();
  }

  increaseQty(): void {
    this.selectedItem.dish.quantity++;
    this.quantity = this.selectedItem.dish.quantity;
    this.updatePrice();
  }

  decreaseQty(): void {
    if (this.selectedItem.dish.quantity > 1) {
      this.selectedItem.dish.quantity--;
      this.quantity = this.selectedItem.dish.quantity;
      this.updatePrice();
    }
  }

  updatePrice(): void {
    if (!this.selectedItem?.dish) {
      this.finalPrice = 0;
      return;
    }

    const dish = this.selectedItem.dish;

    // base (size price if selected, else dish price)
    const sizePrice = this.selectedItem.selected_size
      ? this.selectedItem.sizes?.find(
          (s: any) => s.id === this.selectedItem.selected_size
        )?.price ?? dish.price
      : this.selectedSize?.price ?? dish.price;

    // addons total (use checked)
    const addonsTotal = (this.selectedItem.addon_categories || [])
      .flatMap((c: any) => c.addons || [])
      .reduce(
        (sum: number, a: any) => sum + (a.checked ? Number(a.price) || 0 : 0),
        0
      );

    const qty = Number(dish.quantity) || 1;

    this.finalPrice = (Number(sizePrice) + addonsTotal) * qty;
  }
  initializeAddonValidation(): void {
    const dish = this.selectedItem;
    if (!dish || !Array.isArray(dish.addon_categories)) return;

    this.selectedAddonsByCategory = {};
    this.addonValidationErrors = {};

    dish.addon_categories.forEach(
      (category: { id: any; min_addons: number; max_addons: number }) => {
        const categoryId = category.id.toString();

        this.selectedAddonsByCategory[categoryId] =
          this.selectedAddonsByCategory[categoryId] || [];
        this.addonValidationErrors[categoryId] = {
          minError:
            this.selectedAddonsByCategory[categoryId].length <
            category.min_addons,
          maxError:
            this.selectedAddonsByCategory[categoryId].length >
            category.max_addons,
          maxLabel: `حد أقصي: ${category.max_addons}`,
        };
      }
    );
  }

  validateMinMaxAddons(category: {
    id: string | number;
    min_addons: number;
    max_addons: number;
  }): void {
    const categoryId = category.id.toString();
    const selectedCount =
      this.selectedAddonsByCategory[categoryId]?.length || 0;

    this.addonValidationErrors[categoryId] = {
      minError: selectedCount < category.min_addons,
      maxError: selectedCount > category.max_addons,
      maxLabel: `حد أقصي: ${category.max_addons}`,
    };
  }

  canAddToCart(): boolean {
    return Object.values(this.addonValidationErrors).every(
      (errors: any) => !errors.minError && !errors.maxError
    );
  }

  toggleAddon(
    addon: any,
    event: any,
    category: { id: string | number; min_addons: number; max_addons: number }
  ): void {
    const categoryId = category.id.toString();

    if (!this.selectedAddonsByCategory[categoryId]) {
      this.selectedAddonsByCategory[categoryId] = [];
    }

    if (event.target.checked) {
      if (
        this.selectedAddonsByCategory[categoryId].length < category.max_addons
      ) {
        this.selectedAddonsByCategory[categoryId].push(addon);
        this.selectedAddons.push(addon);
        addon.checked = true; // ✅ update API model
      } else {
        event.target.checked = false;
        return;
      }
    } else {
      this.selectedAddonsByCategory[categoryId] = this.selectedAddonsByCategory[
        categoryId
      ].filter((a) => a.id !== addon.id);
      this.selectedAddons = this.selectedAddons.filter(
        (a) => a.id !== addon.id
      );
      addon.checked = false; // ✅ update API model
    }

    this.validateMinMaxAddons(category);
    this.updatePrice();
  }

  editItem(): void {
    // 🔑 منع الضغط المتعدد
    if (this.isSubmitting) {
      return;
    }

    if (!this.selectedItem?.dish) return;

    const url = `${this.apiUrl}api/orders/cashier/order-edit-item/api`;

    // ✅ build addons in correct format
    const addon_categories = Object.keys(this.selectedAddonsByCategory).map(
      (catId: string) => ({
        id: Number(catId),
        addon: this.selectedAddonsByCategory[catId].map((a: any) => a.id),
      })
    );

    const body = {
      order_id: this.selectedItem.order_id,
      branch_id: Number(localStorage.getItem('branch_id')),
      item_id: this.selectedItem.dish.item_id,
      dish_order: String(this.selectedItem.dish.dish_order ?? '-1'),
      size_id: this.selectedSize?.id ?? null,
      quantity: this.selectedItem.dish.quantity ?? 1,
      note: this.note || '',
      addon_categories: addon_categories,
    };

    this.isSubmitting = true;

    this.isLoading = true;
    this.http.post(url, body).subscribe({
      next: (res: any) => {
        this.isLoading = false;
        if (res.status) {
          console.log('✅ Item updated', res);
          this.actvModal.close('updated');
        } else {
          const apiError =
            res.errorData?.error?.[0] || res.message || 'تعذر تعديل الطلب';

          console.warn('❌ Edit failed:', apiError);
          this.error = apiError;

          // 🔑 auto-hide after 3 seconds
          setTimeout(() => {
            this.error = null;
          }, 3000);
        }
      },
      error: (err) => {
        this.isSubmitting = false; 
        this.isLoading = false;
        console.error('❌ API error', err);

        const apiError =
          err.error?.errorData?.error || err.error?.errorData?.error?.[0] ||
          err.error?.message ||
          'خطأ أثناء الاتصال بالخادم';

        this.error = apiError;

        // 🔑 auto-hide after 3 seconds
        setTimeout(() => {
          this.error = null;
        }, 3000);
      },
    });
  }

  closeModal() {
    console.log('llll', this.actvModal);
    this.actvModal.dismiss('cancel');
    // this.m.hide()
  }

  showAddNote() {
    this.addNote = true;
  }
}
