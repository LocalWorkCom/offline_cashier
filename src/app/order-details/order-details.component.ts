import { Component, OnInit, OnDestroy, Input } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { OrderListDetailsService } from '../services/order-list-details.service';
import { CommonModule, Location } from '@angular/common';
import { Subject } from 'rxjs';
import { finalize, takeUntil } from 'rxjs/operators';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { baseUrl } from '../environment';
import { IndexeddbService } from '../services/indexeddb.service';
import { TablesService } from '../services/tables.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { EditOrderModalComponent } from '../edit-order-modal/edit-order-modal.component';
import { PhoneCheckService } from '../services/phoneCheck';

declare var bootstrap: any;

@Component({
  selector: 'app-order-details',
  templateUrl: './order-details.component.html',
  styleUrls: ['./order-details.component.css'],
  imports: [CommonModule, FormsModule, ShowLoaderUntilPageLoadedDirective, RouterLink],
})
export class OrderDetailsComponent implements OnInit, OnDestroy {
  orderId: any;
  orderDetails: any = {};
  orderSummary: any = {};
  orderItems: any[] = [];
  loading: boolean = true;
  error: string = '';
  paymenMethod!: string;
  currencySymbol!: string;
  @Input() selectedItem: any;
  private destroy$ = new Subject<void>();
  deliveryData: any;
  deliveryFees: any;
  isAllLoading: boolean = true;
  errorMessage: string = '';
  /** When true (e.g. opened from "view original order" after split), coupon is removed from summary so it is not shown on primary order. */
  clearCouponAfterSplit: boolean = false;
  /** Loading state for cancel-item request (set to order_detail_id while loading). */
  removeItemLoading: number | null = null;

  /** Delete item confirmation modal (على صفحة التفاصيل) */
  itemToDelete: any = null;
  deleteItemErrMsg: string = '';

  /** Change Order Type modal (على صفحة التفاصيل) */
  currentOrderForTypeChange: any = null;
  selectedNewOrderType: string = '';
  selectedTableIdForTypeChange: string = '';
  availableTables: any[] = [];
  isChangeTypeSubmitting: boolean = false;

  /** Delivery state for change-type → Delivery flow (mirrors OrdersComponent) */
  deliveryAreas: any[] = [];
  changeTypeDeliveryName: string = '';
  changeTypeDeliveryPhone: string = '';
  changeTypeDeliveryCountryCode: string = '';
  changeTypeDeliveryCountrySearchTerm: string = '';
  changeTypeDeliveryFoundAddresses: any[] = [];
  changeTypeDeliverySelectedAddressId: string = '';
  changeTypeDeliveryPhoneTouched: boolean = false;
  changeTypeDeliveryPhoneMessage: string = '';
  changeTypeDeliverySearchPhoneIdle: boolean = true;
  changeTypeDeliveryDeliveryFormSubmitted: boolean = false;
  changeTypeDeliveryAreaId: string = '';
  changeTypeDeliveryBuildingType: string = 'apartment';
  changeTypeDeliveryBuilding: string = '';
  changeTypeDeliveryApartment: string = '';
  changeTypeDeliveryFloor: string = '';
  changeTypeDeliveryAddress: string = '';
  changeTypeDeliveryNotes: string = '';
  changeTypeDeliveryHotelId: any = '';
  changeTypeDeliveryHotelName: string = '';
  changeTypeDeliveryHotels: any[] = [];
  changeTypeDeliveryUseSameWhatsapp: boolean = true;
  changeTypeDeliveryWhatsapp: string = '';
  changeTypeDeliveryWhatsappCode: string = '';
  changeTypeDeliveryCountryList: any[] = [];
  changeTypeDeliveryFilteredCountries: any[] = [];
  changeTypeDeliverySelectedCountry: any = null;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private orderListById: OrderListDetailsService,
    private http: HttpClient,
    private location: Location,
    private dbService: IndexeddbService,
    private tablesService: TablesService,
    private ngbModal: NgbModal,
    private phoneCheckService: PhoneCheckService
  ) { }
  ngOnInit(): void {
    this.route.paramMap.subscribe({
      next: (params) => {
        this.orderId = params.get('id');
        if (this.orderId) {
          const forceRefresh = this.route.snapshot.queryParamMap.get('refresh') === 'true';
          this.clearCouponAfterSplit = this.route.snapshot.queryParamMap.get('clearCoupon') === '1';
          if (forceRefresh && navigator.onLine) {
            console.log("🔄 Refresh requested - fetching order from API");
            this.fetchOrderDetails();
            return;
          }
          if (!navigator.onLine) {
            this.loadOrderDetailsFromIndexedDB();
            return;
          }
          this.fetchOrderDetails();
        }
      },
      error: (err) => {
        this.error = 'Error retrieving order ID from route.';
      },
    });
  }


  /** تحميل تفاصيل الطلب من IndexedDB عند العمل offline */
  loadOrderDetailsFromIndexedDB(): void {
    this.loading = true;
    this.error = '';
    const id = typeof this.orderId === 'string' ? parseInt(this.orderId, 10) : this.orderId;
    if (isNaN(id)) {
      this.error = 'رقم الطلب غير صالح';
      this.loading = false;
      return;
    }
    this.dbService.getOrderById(id).then((order) => {
      if (order && (order.order_details || order.details_order)) {
        this.displayOrderDetails(order);
      } else {
        this.error = 'الطلب غير متوفر في الوضع offline. تم حفظه وسيظهر بعد عودة الاتصال.';
        this.loading = false;
      }
    }).catch((err) => {
      console.error('Error loading order from IndexedDB:', err);
      this.error = 'تعذر تحميل تفاصيل الطلب من الذاكرة المحلية.';
      this.loading = false;
    });
  }

  /** Branch default delivery_fees from dashboard (fixes wrong fee after change-type-to-delivery). */
  private getBranchDeliveryFees(): number | null {
    try {
      const raw = localStorage.getItem('branchData');
      if (!raw) return null;
      const branch = JSON.parse(raw);
      const fee = branch?.delivery_fees;
      if (fee === undefined || fee === null) return null;
      const num = Number(fee);
      return isNaN(num) ? null : num;
    } catch {
      return null;
    }
  }

  /** Ensure a numeric value for display (avoid NaN). */
  private safeNum(v: any): number {
    const n = Number(v);
    return v != null && !isNaN(n) ? n : 0;
  }

  private normalizePaymentMethod(method: any): string {
    return String(method ?? '')
      .trim()
      .toLowerCase()
      .replace(/\s+/g, '_');
  }

  /** Payment status for display (safe, normalized). */
  get displayPaymentStatus(): string {
    const raw =
      this.orderDetails?.transactions?.[0]?.payment_status ??
      this.orderDetails?.payment_status ??
      '';
    const s = String(raw ?? '').trim().toLowerCase();
    return s || 'unpaid';
  }

  /** Payment method label in Arabic (cash/visa/card/online/etc). */
  get displayPaymentMethodLabel(): string {
    if (this.displayPaymentStatus !== 'paid') return 'غير محدد';
    const m = this.normalizePaymentMethod(
      this.orderDetails?.transactions?.[0]?.payment_method ?? this.paymenMethod
    );
    return this.getPaymentMethodLabel(m);
  }

  getPaymentMethodLabel(method: any): string {
    const m = this.normalizePaymentMethod(method);
    if (m === 'cash') return 'كاش';
    if (['credit', 'visa', 'card', 'mastercard', 'mada'].includes(m)) return 'فيزا';
    if (['deferred', 'later', 'postpaid'].includes(m)) return 'آجل';
    if (m === 'online') return 'أونلاين';
    return 'غير محدد';
  }

  /** Compute grand total from summary parts when total/total_price are missing or invalid. */
  private computeTotalFromSummary(s: any, deliveryFees: number): number {
    if (!s) return 0;
    const sub = this.safeNum(s.subtotal_price_before_coupon ?? s.subtotal ?? s.total_dish_price);
    const coupon = this.safeNum(s.coupon_value);
    const service = this.safeNum(s.service_fees);
    const tax = this.safeNum(s.tax_value);
    const delivery = this.safeNum(deliveryFees);
    return sub - coupon + service + tax + delivery;
  }

  /** Apply branch delivery_fees only when order has no valid fee (e.g. after change-type-to-delivery). Do not override when order already has a valid delivery_fees (e.g. 25) so paid/correct orders show the same as printed invoice. */
  private applyBranchDeliveryFees(summary: any, currentDeliveryFees: number): { deliveryFees: number; orderSummary: any } {
    const branchFee = this.getBranchDeliveryFees();
    if (summary == null) return { deliveryFees: currentDeliveryFees, orderSummary: summary };
    const oldFee = this.safeNum(summary.delivery_fees);
    // If order already has a valid delivery fee (e.g. from area or paid invoice), keep it — do not replace with branch default.
    if (oldFee > 0) return { deliveryFees: oldFee, orderSummary: summary };
    if (branchFee === null) return { deliveryFees: currentDeliveryFees, orderSummary: summary };
    if (oldFee === branchFee) return { deliveryFees: currentDeliveryFees, orderSummary: summary };
    const delta = branchFee - oldFee;
    const currentTotal = this.safeNum(summary.total) || this.safeNum(summary.total_price) || this.computeTotalFromSummary(summary, this.safeNum(summary.delivery_fees));
    const newTotal = currentTotal + delta;
    return {
      deliveryFees: branchFee,
      orderSummary: { ...summary, delivery_fees: branchFee, total: newTotal, total_price: newTotal },
    };
  }

  /** For non-Delivery orders, zero delivery_fees and adjust total so invoice is correct after type change. */
  private normalizeSummaryByOrderType(orderType: string, summary: any, currentDeliveryFees: number): { deliveryFees: number; orderSummary: any } {
    const isDelivery = orderType === 'Delivery';
    if (isDelivery) {
      return this.applyBranchDeliveryFees(summary, currentDeliveryFees);
    }
    const oldFee = this.safeNum(summary?.delivery_fees);
    const rawTotal = this.safeNum(summary?.total) || this.safeNum(summary?.total_price);
    const fallbackTotal = this.computeTotalFromSummary(summary, 0);
    const newTotal = (rawTotal || fallbackTotal) - oldFee;
    const safeTotal = typeof newTotal === 'number' && !isNaN(newTotal) ? newTotal : fallbackTotal;
    const normalizedSummary = summary ? { ...summary, delivery_fees: 0, total: safeTotal, total_price: safeTotal } : summary;
    return { deliveryFees: 0, orderSummary: normalizedSummary };
  }

  /** تصحيح total_dish_price للعناصر (طلبات أوفلاين قد تكون محفوظة بخطأ: السعر للوحدة بدل الإجمالي) */
  private normalizeOfflineOrderItems(items: any[]): any[] {
    if (!items || !Array.isArray(items)) return [];
    return items.map((item: any) => {
      const qty = Number(item.quantity) || 1;
      const unitPrice = this.safeNum(item.final_price ?? item.dish_price);
      const storedTotal = this.safeNum(item.total_dish_price);
      const totalDishPrice = (unitPrice > 0 && qty > 0) ? (unitPrice * qty) : storedTotal;
      return { ...item, total_dish_price: totalDishPrice };
    });
  }

  // Display order details from IndexedDB (طلبات محفوظة محلياً أو من الـ API ثم الـ cache)
  private displayOrderDetails(order: any): void {
    try {
      const details = order.details_order || order;
      this.currencySymbol = details.currency_symbol || order.currency_symbol || 'ج.م';

      this.paymenMethod = (details.transactions && details.transactions[0]) ? details.transactions[0].payment_method : (order.order_details?.payment_method || 'Unknown');
      this.deliveryData = details?.delivery_data ?? order.formdata_delivery ?? null;
      const rawDeliveryFees = details?.order_summary?.delivery_fees ?? order.delivery_fees_amount ?? 0;
      const summaryFromOrder = details?.order_summary || {
        total_dish_price: order.total_price || 0,
        total: order.total_price || 0,
        delivery_fees: order.delivery_fees_amount || 0,
        coupon_value: order.order_details?.coupon_value || 0,
        service_percentage: 0,
      };
      const orderType = details?.order_type || order.order_details?.order_type || '';
      const applied = this.normalizeSummaryByOrderType(orderType, summaryFromOrder, Number(rawDeliveryFees));

      this.deliveryFees = applied.deliveryFees;
      let itemsArray = details?.order_details || order.order_items || [];
      itemsArray = this.normalizeOfflineOrderItems(itemsArray);
      this.orderDetails = {
        ...details,
        order_id: order.order_details?.order_id ?? order.order_number ?? this.orderId,
        order_type: orderType,
        order_summary: applied.orderSummary,
      };
      this.orderItems = this.filterMovedOrderItems(itemsArray);
      this.orderSummary = this.recalculateSummaryFromDisplayedItems(applied.orderSummary, this.orderItems);

      const totalPrice = Number(this.orderSummary?.total_price ?? this.orderSummary?.total ?? 0);
      const isPaidByTotal = !isNaN(totalPrice) && totalPrice <= 0;
      if (!this.orderDetails.transactions || !Array.isArray(this.orderDetails.transactions) || this.orderDetails.transactions.length === 0) {
        const isPaid = this.orderDetails.payment_status === 'paid' || isPaidByTotal;
        this.orderDetails.transactions = [{
          payment_method: this.paymenMethod ?? 'cash',
          payment_status: isPaid ? 'paid' : (this.orderDetails.payment_status ?? 'unpaid'),
          paid: isPaid ? totalPrice : 0
        }];
      } else if (isPaidByTotal && this.orderDetails.transactions[0]?.payment_status === 'unpaid') {
        this.orderDetails.transactions[0].payment_status = 'paid';
        this.orderDetails.transactions[0].paid = totalPrice;
      }

      console.log("orderitems", this.orderItems);

      if (!this.deliveryData && (order.formdata_delivery || order.order_details?.client_name)) {
        this.deliveryData = {
          delivery_name: order.order_details?.client_name || order.formdata_delivery?.client_name || '—',
          delivery_phone: order.order_details?.client_phone || order.formdata_delivery?.address_phone || '',
        };
      }
      if (this.deliveryData && (this.deliveryData.delivery_name === ' ' || !this.deliveryData.delivery_name)) {
        this.deliveryData.delivery_name = this.deliveryData.delivery_name || order.order_details?.client_name || '—';
      }

      console.log('Order details from IndexedDB:', this.orderDetails);
      console.log('Order items from IndexedDB:', this.orderItems);
      console.log('Order summary from IndexedDB:', this.orderSummary);

      this.loading = false;

    } catch (error) {
      console.error('Error processing order data from IndexedDB:', error);
      this.error = 'Error displaying order details from storage';
      this.loading = false;
    }
  }

  fetchOrderDetailsFromAPI(): void {
    this.loading = true;
    this.error = '';
    console.log("orderId -dalia",this.orderId);

    this.orderListById.getOrderById(this.orderId)
      .pipe(
        finalize(() => {
          this.isAllLoading = true;
        }),
        takeUntil(this.destroy$)
      )
      .subscribe({
        next: (response) => {
          if (response && response.data) {
            const order = response.data.orderDetails[0];

            console.log("order -dalia",response.data);
            this.processOrderData(order);

            // Save to IndexedDB for future access
            // this.saveOrderToIndexedDB(order);
          } else {
            this.error = 'No order details available.';
            this.loading = false;
          }
        },
        error: (error: any) => {
          console.error('Error fetching order details from API:', error);
          this.error = 'Failed to fetch order details.';
          this.loading = false;
        },
      });
  }

  /** Show all items: active (qty > 0) and cancelled (so they appear in yellow). Exclude only moved/split (qty 0 and not cancelled). */
  private filterMovedOrderItems(items: any[]): any[] {
    if (!items || !Array.isArray(items)) return [];
    return items.filter((item: any) => {
      const qty = Number(item.quantity) || 0;
      const status = (item.dish_status ?? item.status ?? '').toString().toLowerCase();
      const isCancelled = status === 'cancel' || status === 'cancelled';
      return qty > 0 || isCancelled;
    });
  }

  /** Whether this item is cancelled (display in yellow, no edit/delete). */
  isItemCancelled(item: any): boolean {
    const status = (item?.dish_status ?? item?.status ?? '').toString().toLowerCase();
    return status === 'cancel' || status === 'cancelled';
  }

  /**
   * Recalculate order summary from displayed items when backend summary is stale (e.g. after split).
   * Ensures "view original order" and invoice show the correct amount for the remaining items only.
   */
  private recalculateSummaryFromDisplayedItems(summary: any, items: any[]): any {
    if (!summary || !items || items.length === 0) return summary;
    const itemsSubtotal = items
      .filter((item: any) => !this.isItemCancelled(item))
      .reduce((sum: number, item: any) => sum + this.safeNum(item.total_dish_price), 0);
    const summarySubtotal = this.safeNum(summary.subtotal_price_before_coupon ?? summary.subtotal ?? summary.total_dish_price);
    const diff = Math.abs(itemsSubtotal - summarySubtotal);
    if (diff < 0.02) return summary;
    const coupon = this.safeNum(summary.coupon_value);
    const delivery = this.safeNum(summary.delivery_fees);
    let service = this.safeNum(summary.service_fees);
    const servicePct = this.safeNum(summary.service_percentage);
    if (servicePct > 0) service = (itemsSubtotal - coupon) * (servicePct / 100);
    let tax = this.safeNum(summary.tax_value);
    const taxPct = this.safeNum(summary.tax_percentage);
    if (taxPct > 0 && !summary.tax_application) {
      const afterCouponAndService = itemsSubtotal - coupon + service;
      tax = afterCouponAndService * (taxPct / 100);
    }
    const total = itemsSubtotal - coupon + service + tax + delivery;
    return {
      ...summary,
      subtotal_price_before_coupon: itemsSubtotal,
      subtotal: itemsSubtotal,
      total_dish_price: itemsSubtotal,
      service_fees: service,
      tax_value: tax,
      total: total,
      total_price: total,
    };
  }

  // Process order data from API
  private processOrderData(order: any): void {
    this.currencySymbol = order.currency_symbol;
    this.paymenMethod = order.transactions?.[0]?.payment_method || 'Unknown';
    if (order.order_type === 'Delivery') {
      // Prefer structured delivery_data from API, but fall back to formdata_delivery or basic fields
      let delivery: any = order.delivery_data ?? order.formdata_delivery ?? null;
      const od = order.order_details || {};

      if (!delivery) {
        delivery = {
          client_name: od.client_name || '',
          client_phone: od.client_phone || '',
          client_address: od.delivery_address || od.address || '',
          client_address_phone: od.client_phone || '',
          delivery_name: (od.delivery_name ?? '').trim() || undefined,
        };
      }

      this.deliveryData = delivery;
    } else {
      this.deliveryData = null;
    }
    const rawFee = order.order_summary?.delivery_fees ?? 0;
    const summary = order.order_summary || {};
    const orderType = order.order_type || '';
    const applied = this.normalizeSummaryByOrderType(orderType, summary, Number(rawFee));

    this.deliveryFees = applied.deliveryFees;
    this.orderDetails = order;
    this.orderItems = this.filterMovedOrderItems(order.order_details || []);
    this.orderSummary = this.recalculateSummaryFromDisplayedItems(applied.orderSummary, this.orderItems);
    // After split or merge, coupon must not apply; remove it from displayed summary when requested
    if (this.clearCouponAfterSplit) {
      const sub = this.safeNum(this.orderSummary.subtotal ?? this.orderSummary.subtotal_price_before_coupon ?? this.orderSummary.total_dish_price);
      const coupon = this.safeNum(this.orderSummary.coupon_value);
      const service = this.safeNum(this.orderSummary.service_fees);
      const tax = this.safeNum(this.orderSummary.tax_value);
      const delivery = this.safeNum(this.orderSummary.delivery_fees);
      const total = sub - coupon + service + tax + delivery;
      this.orderSummary = {
        ...this.orderSummary,
        coupon_id: null,
        coupon_value: 0,
        coupon_title: null,
        total: total,
        total_price: total,
      };
    }
    // Persist corrected summary so saveOrderToIndexedDB stores correct totals (e.g. after split)
    order.order_summary = this.orderSummary;

    if (this.deliveryData?.delivery_name === ' ') {
      this.deliveryData.delivery_name = 'لا يوجد';
    }

    this.loading = false;
  }

  // Save order to IndexedDB
  private saveOrderToIndexedDB(order: any): void {
    // Prepare the order data in the same format as stored in IndexedDB
    const orderToSave = {
      ...order,
      order_items: order.order_details || [], // Store items in order_items field
      total_price: order.order_summary?.total || 0,
      currency_symbol: order.currency_symbol,
      savedAt: new Date().toISOString(),
      isSynced: true
    };

    this.dbService.saveOrder(orderToSave).then(() => {
      console.log('Order saved to IndexedDB:', order.order_id);
    }).catch(err => {
      console.error('Error saving order to IndexedDB:', err);
    });
  }

  //end dalia

  fetchOrderDetails(): void {
    this.loading = true;
    this.error = '';
    this.isAllLoading = false;
    this.orderListById
      .getOrderById(this.orderId)
      .pipe(
        finalize(() => {
          this.isAllLoading = true;
        })
      )
      .subscribe({
        next: (response) => {
          if (response) {
            const order = response.data.orderDetails[0];
            this.currencySymbol = order.currency_symbol;
            this.paymenMethod = order.transactions?.[0]?.payment_method ?? 'Unknown';
            this.deliveryData = order.order_type === 'Delivery' ? response.data.orderDetails[0].delivery_data : null;
            const summary = order.order_summary || {};
            const rawFee = summary.delivery_fees ?? 0;
            const orderType = order.order_type || '';
            const applied = this.normalizeSummaryByOrderType(orderType, summary, Number(rawFee));
            this.deliveryFees = applied.deliveryFees;

            this.orderDetails = order;
            this.orderItems = this.filterMovedOrderItems(order.order_details || []);
            // Recalculate summary from displayed items so sub amount matches all items (e.g. after merge/split)
            this.orderSummary = this.recalculateSummaryFromDisplayedItems(applied.orderSummary, this.orderItems);
            // After merge (clearCoupon=1): remove coupon from display and recalc total without coupon
            if (this.clearCouponAfterSplit) {
              const sub = this.safeNum(this.orderSummary.subtotal ?? this.orderSummary.subtotal_price_before_coupon ?? this.orderSummary.total_dish_price);
              const service = this.safeNum(this.orderSummary.service_fees);
              const tax = this.safeNum(this.orderSummary.tax_value);
              const delivery = this.safeNum(this.orderSummary.delivery_fees);
              const total = sub + service + tax + delivery;
              this.orderSummary = {
                ...this.orderSummary,
                coupon_id: null,
                coupon_value: 0,
                coupon_title: null,
                total,
                total_price: total,
              };
            }
            order.order_summary = this.orderSummary;
            if (this.deliveryData?.delivery_name == ' ') {
              this.deliveryData.delivery_name = 'لا يوجد';
            }
            this.loading = false;
          } else {
            this.error = 'No order details available.';
            this.loading = false;
          }
        },
        error: (error: any) => {
          console.error('Error fetching order details:', error);
          this.error = 'Failed to fetch order details.';
          this.loading = false;
        },
      });
  }
  /** Hide coupon row when there is no coupon (no id or value is zero). Avoids showing stale coupon after merge/split. */
  get isCouponZero(): boolean {
    const id = this.orderSummary?.coupon_id;
    if (id == null || id === '') return true;
    const val = this.orderSummary?.coupon_value;
    return val == null || Number(val) === 0;
  }
  get hasServiceFees(): boolean {
    return Number(this.orderSummary.service_percentage) > 0;
  }

  /** Safe grand total for display (never NaN). */
  get displayTotalPrice(): number {
    const v = this.orderSummary?.total_price ?? this.orderSummary?.total;
    const n = Number(v);
    return v != null && !isNaN(n) ? n : 0;
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
  status_order: any;
  cancelOrde(): void {
    if (!this.orderId) return;


    const cancelUrl = `${baseUrl}api/orders/cashier/order-cancel`;

    const token = localStorage.getItem('authToken');

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });
    const body = {
      order_id: this.orderId,
      type: 1, // 1 to delete all the dishes
    };

    this.http.post(cancelUrl, body, { headers }).subscribe({
      next: (response: any) => {
        console.log('Order cancelled successfully:', response);
        this.errorMessage = response.message;
        this.status_order = response.status;
        setTimeout(() => {
          this.errorMessage = '';
        }, 2000);
        this.fetchOrderDetails();
      },
      error: (error) => {
        console.error('Failed to cancel order:', error);
      },
    });
  }
  cancelOrder(): void {
    if (!this.orderId) return;

    const cancelUrl = `${baseUrl}api/orders/cashier/request-cancel`;
    const token = localStorage.getItem('authToken');
    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });
    const body = {
      order_id: this.orderId,
      type: "full",
      items: this.orderItems,
      reason: "fff",
    };

    this.http.post(cancelUrl, body, { headers }).subscribe({
      next: (response: any) => {
        console.log('Order cancelled successfully:', response);
        this.errorMessage = response.message;
        this.status_order = response.status;
        setTimeout(() => {
          this.errorMessage = '';
        }, 2000);
        this.fetchOrderDetailsFromAPI();
      },
      error: (error) => {
        console.error('Failed to cancel order:', error);
      },
    });
  }

  /** Whether to show the order actions card (unpaid, pending, not talabat). */
  canShowOrderActions(): boolean {
    const d = this.orderDetails;
    if (!d) return false;
    if (d.status === 'cancelled' || d.status === 'cancel') return false;
    const paymentStatus = d.payment_status ?? d.transactions?.[0]?.payment_status;
    if (paymentStatus !== 'unpaid') return false;
    if (d.order_type === 'talabat') return false;
    return true;
  }

  isDineIn(): boolean {
    return this.orderDetails?.order_type === 'dine-in';
  }

  /** Add Item: go to cart for this order so user can add more items. */
  goToAddItem(): void {
    if (!this.orderId) return;
    this.router.navigate(['/cart', this.orderId]);
  }

  /** Open Change Order Type modal on this page (بدون الانتقال لصفحة الطلبات). */
  openChangeOrderTypeModalFromDetails(): void {
    const paymentStatus = this.orderDetails?.payment_status ?? this.orderDetails?.transactions?.[0]?.payment_status;
    if (paymentStatus === 'paid') {
      const warningEl = document.getElementById('changeTypePaidWarningModalDetails');
      if (warningEl) {
        const modal = new bootstrap.Modal(warningEl);
        modal.show();
      }
      return;
    }
    const rawType = this.orderDetails?.order_type || '';
    this.selectedNewOrderType = rawType === 'reservation-table' ? 'dine-in' : (['Delivery', 'Takeaway', 'dine-in'].includes(rawType) ? rawType : '');
    this.selectedTableIdForTypeChange = this.orderDetails?.table_id ? String(this.orderDetails.table_id) : '';
    this.currentOrderForTypeChange = {
      order_details: {
        order_id: this.orderId,
        order_number: this.orderDetails?.order_summary?.order_number ?? this.orderDetails?.order_number ?? this.orderId,
        order_type: this.orderDetails?.order_type,
        payment_status: paymentStatus,
        table_id: this.orderDetails?.table_id,
        table_number: this.orderDetails?.table_number,
      },
    };
    this.fetchAvailableTablesForChangeType();
    const modalEl = document.getElementById('changeOrderTypeModalDetails');
    if (modalEl) {
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    }
  }

  fetchAvailableTablesForChangeType(): void {
    this.tablesService.getTables().subscribe({
      next: (response: any) => {
        if (response?.status && response?.data && Array.isArray(response.data)) {
          this.availableTables = response.data.map((table: any) => ({
            id: table.id,
            number: table.number ?? table.table_number ?? table.id,
            status: table.status ?? 1,
          }));
        }
      },
      error: () => { this.availableTables = []; },
    });
  }

  get availableTablesForTypeChange(): any[] {
    if (!this.availableTables?.length) return [];
    const currentTableId = this.currentOrderForTypeChange?.order_details?.table_id;
    return this.availableTables.filter(
      (t: any) => t.status === 1 || (currentTableId != null && Number(t.id) === Number(currentTableId))
    );
  }

  isSameOrderTypeSelected(): boolean {
    const current = this.currentOrderForTypeChange?.order_details?.order_type;
    if (!current || !this.selectedNewOrderType) return false;
    const normalizedCurrent = current === 'reservation-table' ? 'dine-in' : current;
    return normalizedCurrent === this.selectedNewOrderType;
  }

  openChangeTypeConfirmModalDetails(): void {
    const modalEl = document.getElementById('changeOrderTypeModalDetails');
    if (modalEl) {
      const inst = bootstrap.Modal.getInstance(modalEl);
      inst?.hide();
    }
    setTimeout(() => {
      const confirmEl = document.getElementById('confirmChangeOrderTypeModalDetails');
      if (confirmEl) {
        const modal = new bootstrap.Modal(confirmEl);
        modal.show();
      }
    }, 300);
  }

  onConfirmChangeOrderTypeClickDetails(): void {
    if (this.selectedNewOrderType === 'Delivery') {
      // Hide the confirm modal, then open the delivery details modal (same as OrdersComponent)
      const confirmEl = document.getElementById('confirmChangeOrderTypeModalDetails');
      if (confirmEl) {
        const inst = bootstrap.Modal.getInstance(confirmEl);
        inst?.hide();
      }
      setTimeout(() => this.openChangeTypeDeliveryDetailsModal(), 300);
    } else {
      this.submitChangeOrderTypeDetails();
    }
  }

  openChangeTypeDeliveryDetailsModal(): void {
    const o = this.currentOrderForTypeChange;
    this.changeTypeDeliveryPhoneMessage = '';
    this.changeTypeDeliveryPhoneTouched = false;
    this.changeTypeDeliveryDeliveryFormSubmitted = false;
    this.changeTypeDeliveryFoundAddresses = [];
    this.changeTypeDeliverySelectedAddressId = '';
    if (o?.order_details) {
      this.changeTypeDeliveryName = this.changeTypeDeliveryName || o.order_details.client_name || '';
      this.changeTypeDeliveryPhone = this.changeTypeDeliveryPhone || o.order_details.client_phone || '';
      const rawCode = (o.order_details.client_country_code || '').trim();
      this.changeTypeDeliveryCountryCode = this.changeTypeDeliveryCountryCode || rawCode || '+20';
    } else {
      this.changeTypeDeliveryCountryCode = this.changeTypeDeliveryCountryCode || '+20';
    }
    this.loadDeliveryAreas();
    this.loadChangeTypeHotels();
    this.loadChangeTypeDeliveryCountries();
    const modalEl = document.getElementById('changeTypeDeliveryDetailsModal');
    if (modalEl) {
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    }
  }

  submitChangeOrderTypeDetails(): void {
    if (!this.currentOrderForTypeChange) return;
    const newOrderType = this.selectedNewOrderType;
    if (!newOrderType || !['Delivery', 'Takeaway', 'dine-in'].includes(newOrderType)) return;
    if (newOrderType === 'dine-in' && !this.selectedTableIdForTypeChange) return;

    this.isChangeTypeSubmitting = true;
    const token = localStorage.getItem('authToken');
    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
      lang: 'ar',
    });
    const body: Record<string, unknown> = {
      order_id: this.currentOrderForTypeChange.order_details.order_id,
      new_order_type: newOrderType,
    };
    if (newOrderType === 'dine-in' && this.selectedTableIdForTypeChange) {
      body['table_id'] = parseInt(this.selectedTableIdForTypeChange, 10);
    }
    // Mirror OrdersComponent: include delivery payload when changing to Delivery
    if (newOrderType === 'Delivery') {
      const od = this.currentOrderForTypeChange?.order_details;
      const addressId = this.changeTypeDeliverySelectedAddressId || od?.client_address_id;
      if (addressId && !this.changeTypeDeliveryAreaId) {
        body['client_address'] = addressId;
      }
      body['client_name'] = this.changeTypeDeliveryName?.trim() || od?.client_name || '';
      body['client_phone'] = this.changeTypeDeliveryPhone?.trim() || od?.client_phone || '';
      body['client_country_code'] = (this.changeTypeDeliverySelectedCountry?.code || this.changeTypeDeliveryCountryCode || od?.client_country_code || '').trim();
      if (this.changeTypeDeliveryAreaId) {
        body['area_id'] = parseInt(this.changeTypeDeliveryAreaId, 10);
        body['delivery_address'] = this.changeTypeDeliveryAddress?.trim() || this.changeTypeDeliveryBuilding?.trim() || this.changeTypeDeliveryHotelName?.trim() || 'عنوان التوصيل';

        body['address_type'] = this.changeTypeDeliveryBuildingType || 'apartment';
        body['building'] = this.changeTypeDeliveryBuilding?.trim() || null;
        body['apartment_number'] = this.changeTypeDeliveryApartment?.trim() || null;
        body['floor_number'] = this.changeTypeDeliveryFloor?.trim() || null;
        body['address'] = body['delivery_address'];
        body['notes'] = this.changeTypeDeliveryNotes?.trim() || null;
        if (this.changeTypeDeliveryBuildingType === 'hotel' && this.changeTypeDeliveryHotelId) {
          body['hotel_id'] = parseInt(String(this.changeTypeDeliveryHotelId), 10);
        }
        if (this.changeTypeDeliveryWhatsappCode?.trim()) body['whatsapp_number_code'] = this.changeTypeDeliveryWhatsappCode.trim();
        if (this.changeTypeDeliveryWhatsapp?.trim()) body['whatsapp_number'] = this.changeTypeDeliveryWhatsapp.trim();
      }
    }

    this.http.post(`${baseUrl}api/orders/changeOrderType`, body, { headers }).subscribe({
      next: (res: any) => {
        this.isChangeTypeSubmitting = false;
        const confirmEl = document.getElementById('confirmChangeOrderTypeModalDetails');
        if (confirmEl) {
          const inst = bootstrap.Modal.getInstance(confirmEl);
          inst?.hide();
        }
        const deliveryEl = document.getElementById('changeTypeDeliveryDetailsModal');
        if (deliveryEl) {
          const inst2 = bootstrap.Modal.getInstance(deliveryEl);
          inst2?.hide();
        }
        if (res?.status) {
          this.errorMessage = res?.message || 'تم تغيير نوع الطلب بنجاح';
          this.status_order = true;
          // this.fetchOrderDetailsFromAPI();
          this.fetchOrderDetails();
          setTimeout(() => { this.errorMessage = ''; }, 4000);
          this.currentOrderForTypeChange = null;
          this.selectedNewOrderType = '';
          this.selectedTableIdForTypeChange = '';
          this.resetChangeTypeDeliveryForm();
        } else {
          this.errorMessage = (res?.errorData && typeof res.errorData === 'object' && Object.values(res.errorData).flat().filter(Boolean)[0]) || res?.message || 'حدث خطأ أثناء تغيير نوع الطلب';
          setTimeout(() => { this.errorMessage = ''; }, 4000);
        }
      },
      error: (err: any) => {
        this.isChangeTypeSubmitting = false;
        this.errorMessage = err?.error?.message || err?.error?.errorData || 'حدث خطأ أثناء تغيير نوع الطلب';
        setTimeout(() => { this.errorMessage = ''; }, 4000);
      },
    });
  }

  submitChangeOrderTypeFromDeliveryModal(): void {
    this.changeTypeDeliveryDeliveryFormSubmitted = true;
    this.changeTypeDeliveryPhoneTouched = true;
    const phoneError = this.getChangeTypeDeliveryPhoneError();
    if (phoneError) {
      this.changeTypeDeliveryPhoneMessage = phoneError;
      return;
    }
    this.submitChangeOrderTypeDetails();
  }

  loadDeliveryAreas(): void {
    if (this.deliveryAreas.length > 0) return;
    const branchId = localStorage.getItem('branch_id');
    if (!branchId) return;
    this.http.get<any>(`${baseUrl}api/areas/${branchId}`).subscribe({
      next: (res) => {
        if (res?.status && Array.isArray(res.data)) {
          this.deliveryAreas = res.data;
          this.dbService.saveData('areas', res.data).catch(() => {});
        }
      },
      error: () => {},
    });
  }

  loadChangeTypeHotels(): void {
    if (this.changeTypeDeliveryHotels.length > 0) return;
    const token = localStorage.getItem('authToken');
    this.http.get<any>(`${baseUrl}api/listHotels`, { headers: new HttpHeaders({ Authorization: `Bearer ${token}` }) }).subscribe({
      next: (res) => {
        const hotels = Array.isArray(res?.data) ? res.data : [];
        this.changeTypeDeliveryHotels = hotels;
        if (hotels.length) this.dbService.saveData('hotels', hotels).catch(() => {});
      },
      error: () => { this.changeTypeDeliveryHotels = []; },
    });
  }

  loadChangeTypeDeliveryCountries(): void {
    if (this.changeTypeDeliveryCountryList.length > 0) {
      this.filterChangeTypeDeliveryCountries();
      this.syncChangeTypeSelectedCountryFromCode();
      return;
    }
    this.http.get<any>(`${baseUrl}api/country`).subscribe({
      next: (response) => {
        if (response?.data && Array.isArray(response.data)) {
          const allowedCodes = ['+20', '+962', '+964', '+212', '+963', '+965', '+966'];
          this.changeTypeDeliveryCountryList = response.data
            .map((c: any) => ({
              code: (c.phone_code || '').trim(),
              flag: c.image || '',
              phoneLength: c.length || 10,
            }))
            .filter((c: any) => allowedCodes.includes(c.code.replace(/\s+/g, '')));
          const toSave = response.data.map((c: any, i: number) => ({
            ...c,
            code: (c.code || c.phone_code || '').trim() || `country_${i}`,
          }));
          if (toSave.length) this.dbService.saveData('countries', toSave).catch(() => {});
          this.filterChangeTypeDeliveryCountries();
          this.syncChangeTypeSelectedCountryFromCode();
        }
      },
      error: () => {},
    });
  }

  private syncChangeTypeSelectedCountryFromCode(): void {
    const code = (this.changeTypeDeliveryCountryCode || '').trim().replace(/\s+/g, '');
    if (!code) return;
    const match = this.changeTypeDeliveryCountryList.find(
      (c: any) => (c.code || '').replace(/\s+/g, '') === code
    );
    if (match) this.changeTypeDeliverySelectedCountry = match;
  }

  filterChangeTypeDeliveryCountries(): void {
    const term = (this.changeTypeDeliveryCountrySearchTerm || '').trim().toLowerCase();
    this.changeTypeDeliveryFilteredCountries = term
      ? this.changeTypeDeliveryCountryList.filter((c: any) => (c.code || '').toLowerCase().includes(term))
      : [...this.changeTypeDeliveryCountryList];
  }

  selectChangeTypeDeliveryCountry(country: any): void {
    this.changeTypeDeliverySelectedCountry = country;
    this.changeTypeDeliveryCountryCode = (country?.code || '').trim();
    this.changeTypeDeliveryPhoneTouched = false;
    this.changeTypeDeliveryPhoneMessage = '';
  }

  useSameWhatsappChangeType(value: boolean): void {
    this.changeTypeDeliveryUseSameWhatsapp = value;
    if (value) {
      this.changeTypeDeliveryWhatsapp = '';
      this.changeTypeDeliveryWhatsappCode = this.changeTypeDeliverySelectedCountry?.code || this.changeTypeDeliveryCountryCode || '';
    }
  }

  getChangeTypeDeliveryPhoneError(): string | null {
    const phone = (this.changeTypeDeliveryPhone || '').trim();
    const country = this.changeTypeDeliverySelectedCountry;
    const requiredLength = country?.phoneLength ?? 10;
    if (!phone) return 'رقم الهاتف مطلوب';
    if (!/^\d+$/.test(phone)) return 'رقم الهاتف يجب أن يحتوي على أرقام فقط';
    if (phone.length !== requiredLength) return `رقم الهاتف يجب أن يحتوي على ${requiredLength} رقم فقط`;
    return null;
  }

  searchChangeTypeByPhone(): void {
    this.changeTypeDeliveryPhoneTouched = true;
    const phone = (this.changeTypeDeliveryPhone || '').trim();
    const countryCode = this.changeTypeDeliverySelectedCountry?.code || this.changeTypeDeliveryCountryCode || '';
    const phoneError = this.getChangeTypeDeliveryPhoneError();
    this.changeTypeDeliveryPhoneMessage = '';
    this.changeTypeDeliveryFoundAddresses = [];
    this.changeTypeDeliverySelectedAddressId = '';
    if (phoneError) { this.changeTypeDeliveryPhoneMessage = phoneError; return; }
    if (!countryCode) { this.changeTypeDeliveryPhoneMessage = 'يرجى اختيار كود الدولة أولاً'; return; }
    this.changeTypeDeliverySearchPhoneIdle = false;
    const body = { country_code: countryCode.trim(), address_phone: phone };
    this.phoneCheckService.checkPhone(body).subscribe({
      next: (res: any) => {
        this.changeTypeDeliverySearchPhoneIdle = true;
        if (res?.status === true && res?.data != null) {
          const data = Array.isArray(res.data) ? res.data : [res.data];
          if (!data.length) { this.changeTypeDeliveryPhoneMessage = 'لا يوجد عميل مسجل بهذا الرقم'; return; }
          this.changeTypeDeliveryName = data[0].user_name || data[0].client_name || this.changeTypeDeliveryName || '';
          this.changeTypeDeliveryFoundAddresses = data;
          if (data.length === 1) {
            this.applyChangeTypeAddressFromItem(data[0]);
            this.changeTypeDeliverySelectedAddressId = String(data[0].id);
          } else {
            this.changeTypeDeliverySelectedAddressId = String(data[0].id);
            this.applyChangeTypeAddressFromItem(data[0]);
          }
          this.changeTypeDeliveryPhoneMessage = `تم العثور على عميل (${data.length} عنوان)`;
        } else {
          this.changeTypeDeliveryPhoneMessage = (res?.message && String(res.message).trim()) || 'لا يوجد عميل مسجل بهذا الرقم';
        }
      },
      error: (err: any) => {
        this.changeTypeDeliverySearchPhoneIdle = true;
        this.changeTypeDeliveryPhoneMessage = err?.error?.message || 'حدث خطأ أثناء البحث. تأكد من الرقم وكود الدولة.';
      }
    });
  }

  private applyChangeTypeAddressFromItem(addr: any): void {
    if (!addr) return;
    this.changeTypeDeliveryAreaId = addr.area_id != null ? String(addr.area_id) : (addr.area?.id != null ? String(addr.area.id) : this.changeTypeDeliveryAreaId || '');
    this.changeTypeDeliveryAddress = addr.address || '';
    this.changeTypeDeliveryBuilding = addr.building || '';
    this.changeTypeDeliveryApartment = addr.apartment_number || addr.apartment || '';
    this.changeTypeDeliveryFloor = addr.floor_number || addr.floor || '';
    this.changeTypeDeliveryNotes = addr.notes || '';
    const at = (addr.address_type || '').toLowerCase();
    if (['apartment', 'شقة'].includes(at)) this.changeTypeDeliveryBuildingType = 'apartment';
    else if (['villa', 'فيلا'].includes(at)) this.changeTypeDeliveryBuildingType = 'villa';
    else if (['office', 'مكتب'].includes(at)) this.changeTypeDeliveryBuildingType = 'office';
    else if (['hotel', 'فندق'].includes(at)) {
      this.changeTypeDeliveryBuildingType = 'hotel';
      if (addr.hotel_id != null) this.changeTypeDeliveryHotelId = addr.hotel_id;
    }
  }

  onChangeTypeDeliveryAddressSelect(): void {
    const addr = this.changeTypeDeliveryFoundAddresses.find((a: any) => String(a.id) === String(this.changeTypeDeliverySelectedAddressId));
    if (addr) this.applyChangeTypeAddressFromItem(addr);
  }

  onChangeTypeHotelSelect(hotelId: string | number): void {
    if (!hotelId) { this.changeTypeDeliveryHotelName = ''; return; }
    const hotel = this.changeTypeDeliveryHotels.find((h: any) => String(h.id) === String(hotelId));
    this.changeTypeDeliveryHotelName = hotel ? (hotel.name_ar || hotel.name_en || hotel.name || '') : '';
  }

  isDeliveryInfoComplete(): boolean {
    if (this.selectedNewOrderType !== 'Delivery') return true;
    const od = this.currentOrderForTypeChange?.order_details;
    // If phone and name and country code provided, that's enough (address can be existing)
    const hasPhone = !!(this.changeTypeDeliveryPhone?.trim() || od?.client_phone);
    const hasName = !!(this.changeTypeDeliveryName?.trim() || od?.client_name);
    const hasCode = !!(this.changeTypeDeliveryCountryCode?.trim() || this.changeTypeDeliverySelectedCountry?.code || od?.client_country_code);
    if (!hasPhone || !hasName || !hasCode) return false;
    // If we have a selected address from phone search, that's sufficient
    if (this.changeTypeDeliverySelectedAddressId || od?.client_address_id) return true;
    // Otherwise require area and at least one address field
    if (!this.changeTypeDeliveryAreaId) return false;
    if (this.changeTypeDeliveryBuildingType === 'hotel') return !!(this.changeTypeDeliveryHotelId || this.changeTypeDeliveryHotelName?.trim());
    return !!(this.changeTypeDeliveryAddress?.trim() || this.changeTypeDeliveryBuilding?.trim());
  }

  private resetChangeTypeDeliveryForm(): void {
    this.changeTypeDeliveryName = '';
    this.changeTypeDeliveryPhone = '';
    this.changeTypeDeliveryCountryCode = '+20';
    this.changeTypeDeliveryAddress = '';
    this.changeTypeDeliveryAreaId = '';
    this.changeTypeDeliveryBuilding = '';
    this.changeTypeDeliveryApartment = '';
    this.changeTypeDeliveryFloor = '';
    this.changeTypeDeliveryBuildingType = 'apartment';
    this.changeTypeDeliveryNotes = '';
    this.changeTypeDeliveryHotelName = '';
    this.changeTypeDeliveryHotelId = '';
    this.changeTypeDeliveryWhatsapp = '';
    this.changeTypeDeliveryWhatsappCode = '';
    this.changeTypeDeliveryUseSameWhatsapp = true;
    this.changeTypeDeliverySelectedCountry = null;
    this.changeTypeDeliveryCountrySearchTerm = '';
    this.changeTypeDeliveryFoundAddresses = [];
    this.changeTypeDeliverySelectedAddressId = '';
    this.changeTypeDeliveryPhoneMessage = '';
    this.changeTypeDeliveryPhoneTouched = false;
    this.changeTypeDeliveryDeliveryFormSubmitted = false;
  }

  getOrderTypeShortLabel(type: string): string {
    if (!type) return '';
    const short: Record<string, string> = {
      'dine-in': 'محلي',
      'Takeaway': 'استلام',
      'Delivery': 'توصيل',
    };
    return short[type] || type;
  }

  getOrderTypeIconClass(type: string): string {
    if (!type) return 'fa-solid fa-circle';
    const icons: Record<string, string> = {
      'dine-in': 'fa-solid fa-utensils',
      'Takeaway': 'fa-solid fa-bag-shopping',
      'Delivery': 'fa-solid fa-truck',
    };
    return icons[type] || 'fa-solid fa-circle';
  }

  /** Change Order Type: go to orders list (when user wants to complete Delivery form there). */
  goToChangeOrderType(): void {
    if (!this.orderId) return;
    this.router.navigate(['/orders'], { queryParams: { openOrder: this.orderId, action: 'changeType' } });
  }

  /** Whether to show Cancel/Modify item buttons for this line (unpaid, pending/inprogress item). Shown for all order types including talabat (طلبات). */
  canShowItemActions(item: any): boolean {
    const d = this.orderDetails;
    if (!d) return false;
    if (d.status === 'cancelled' || d.status === 'cancel') return false;
    const paymentStatus = d.payment_status ?? d.transactions?.[0]?.payment_status;
    if (paymentStatus !== 'unpaid') return false;
    const status = item?.dish_status;
    return status === 'pending' || status === 'inprogress';
  }

  /** Cancel a single item (partial cancel). Optional onDone called in finalize (e.g. to close delete modal). */
  cancelItem(item: any, onDone?: () => void): void {
    const detailId = item?.id ?? item?.order_detail_id;
    if (detailId == null || !this.orderId) return;

    this.removeItemLoading = detailId;
    this.deleteItemErrMsg = '';
    const url = `${baseUrl}api/orders/cashier/request-cancel`;
    const token = localStorage.getItem('authToken');
    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });
    const quantity = Number(item?.quantity) || 1;
    const body = {
      order_id: this.orderId,
      items: [{ item_id: detailId, quantity }],
      type: 'partial',
      reason: 'cashier reason',
      flag: 'cancel',
    };

    this.dbService.saveOrderToPrintkitchen(this.orderId, 'cancel').then(() => {}).catch(() => {});

    this.http.post(url, body, { headers }).pipe(
      finalize(() => {
        this.removeItemLoading = null;
        onDone?.();
      })
    ).subscribe({
      next: (res: any) => {
        this.errorMessage = res?.message || 'تم حذف الصنف بنجاح';
        this.status_order = res?.status;
        setTimeout(() => { this.errorMessage = ''; }, 2000);
        this.fetchOrderDetailsFromAPI();
      },
      error: (err) => {
        this.errorMessage = err?.error?.message || 'فشل حذف الصنف';
        this.deleteItemErrMsg = err?.error?.message || 'فشل حذف الصنف';
        setTimeout(() => { this.errorMessage = ''; }, 3000);
        this.fetchOrderDetailsFromAPI();
      },
    });
  }

  /** Open delete-item confirmation modal (same as orders list "حذف الطلب"). */
  openDeleteItemModal(item: any): void {
    this.itemToDelete = item;
    this.deleteItemErrMsg = '';
    const el = document.getElementById('deleteItemConfirmModalDetails');
    if (el) {
      const modalInstance = (bootstrap as any).Modal.getOrCreateInstance(el);
      modalInstance.show();
    }
  }

  /** Hide delete-item modal and clear selection. */
  hideDeleteItemModal(): void {
    const el = document.getElementById('deleteItemConfirmModalDetails');
    if (el) {
      const modalInstance = (bootstrap as any).Modal.getInstance(el);
      if (modalInstance) modalInstance.hide();
    }
    this.itemToDelete = null;
    this.deleteItemErrMsg = '';
  }

  /** Confirm delete from modal: mark item as cancelled so it turns yellow, then call cancelItem and close modal when done. */
  confirmDeleteItem(): void {
    if (!this.itemToDelete) return;
    this.itemToDelete.dish_status = 'cancel';
    this.cancelItem(this.itemToDelete, () => this.hideDeleteItemModal());
  }

  /** Open edit-item modal on the current page (same as orders list "تعديل الطلب"). */
  openEditModalFromDetails(item: any): void {
    const detailId = item?.order_detail_id ?? item?.id;
    if (detailId == null || !this.orderId) return;

    const hasExtraData = item.size || (item.addons && item.addons.length > 0);
    const modalSize = hasExtraData ? 'lg' : 'md';

    const editModal = this.ngbModal.open(EditOrderModalComponent, {
      size: modalSize,
      centered: true,
    });
    editModal.componentInstance.itemId = detailId;

    this.dbService.saveOrderToPrintkitchen(this.orderId, 'edit').then(() => {}).catch(() => {});

    editModal.result.then(
      (result) => {
        if (result) {
          this.errorMessage = 'تم تحديث الطلب بنجاح';
          this.status_order = true;
          setTimeout(() => { this.errorMessage = ''; }, 2000);
          this.fetchOrderDetailsFromAPI();
        }
      },
      () => {}
    );
  }
}
