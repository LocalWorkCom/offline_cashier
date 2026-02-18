import { Component, OnInit, OnDestroy, Input } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { OrderListDetailsService } from '../services/order-list-details.service';
import { CommonModule, Location } from '@angular/common';
import { Subject } from 'rxjs';
import { finalize, takeUntil } from 'rxjs/operators';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { baseUrl } from '../environment';
import { IndexeddbService } from '../services/indexeddb.service';


@Component({
  selector: 'app-order-details',
  templateUrl: './order-details.component.html',
  styleUrls: ['./order-details.component.css'],
  imports: [CommonModule, ShowLoaderUntilPageLoadedDirective, RouterLink],
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

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private orderListById: OrderListDetailsService,
    private http: HttpClient,
    private location: Location,
    private dbService: IndexeddbService
  ) { }
  ngOnInit(): void {
    this.route.paramMap.subscribe({
      next: (params) => {
        // console.log(params,'params order details')
        this.orderId = params.get('id');
        if (this.orderId) {
          const forceRefresh = this.route.snapshot.queryParamMap.get('refresh') === 'true';
          this.clearCouponAfterSplit = this.route.snapshot.queryParamMap.get('clearCoupon') === '1';
          if (forceRefresh && navigator.onLine) {
            // After merge (or similar): force fetch from API so merged items are shown, then update IndexedDB
            console.log("🔄 Refresh requested - fetching order from API");
            this.fetchOrderDetailsFromAPI();
            return;
          }
          if (navigator.onLine) {
            // 🌐 Online → استخدم الـ id الحقيقي من السيرفر
            console.log("✅ Online mode - using actual orderId from route");
            this.searchOrderInIndexedDB();
            // أو كمان API call: this.fetchOrderDetailsFromAPI(this.orderId);

          } else {
            // 📴 Offline → الـ orderId اللي في الـ params مش هو الحقيقي
            // نجيب التفاصيل من الـ IndexedDB
            console.log("📴 Offline mode - fetching order by runId/tempId");
            this.searchOrderInIndexedDB();
          }
        }
      },
      error: (err) => {
        this.error = 'Error retrieving order ID from route.';
        // console.error(this.error, err);
      },
    });
  }


  // start dalia
  // Search for order in IndexedDB by ID
  async searchOrderInIndexedDB(): Promise<void> {
    this.loading = true;
    this.error = '';
    // Convert orderId to number
    const numericOrderId = parseInt(this.orderId, 10);
    if (isNaN(numericOrderId)) {
      this.error = 'Invalid order ID';
      this.loading = false;
      return;
    }
    this.dbService.getOrderById(numericOrderId).then(order => {
      if (order) {
        console.log('Order found in IndexedDB:', order);
        this.displayOrderDetails(order);
      } else {
        console.log('Order not found in IndexedDB, fetching from API');
        // this.fetchOrderDetailsFromAPI();
        this.fetchOrderDetails();
      }
    }).catch(err => {
      console.error('Error searching order in IndexedDB:', err);
      this.fetchOrderDetailsFromAPI();
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

  // Display order details from IndexedDB
  private displayOrderDetails(order: any): void {


    try {
      // Extract order details
      this.currencySymbol = order.details_order.currency_symbol || 'ج.م';

      this.paymenMethod = order.details_order.transactions[0].payment_method;
      this.deliveryData = order.details_order?.delivery_data || "";
      const rawDeliveryFees = order.details_order.order_summary?.delivery_fees ||
        order.details_order.order_summary?.delivery_fees || 0;
      const summaryFromOrder = order.details_order?.order_summary || {
        total_dish_price: order.order_details?.total_dish_price || 0,
        total: order.total_price || 0,
        delivery_fees: order.delivery_fees_amount || 0,
        coupon_value: order.coupon_value || 0,
        service_percentage: order.service_percentage || 0
      };
      const orderType = order.details_order?.order_type || '';
      const applied = this.normalizeSummaryByOrderType(orderType, summaryFromOrder, Number(rawDeliveryFees));

      this.deliveryFees = applied.deliveryFees;
      // Set the main order details
      this.orderDetails = order.details_order;
      this.orderItems = this.filterMovedOrderItems(order.details_order?.order_details || []);
      this.orderSummary = this.recalculateSummaryFromDisplayedItems(applied.orderSummary, this.orderItems);



      console.log("orderitems", this.orderItems);

      // Fix delivery name if empty
      if (this.deliveryData?.delivery_name === ' ' || !this.deliveryData?.delivery_name) {
        this.deliveryData.delivery_name = "test";
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
            this.processOrderData(order);

            // Save to IndexedDB for future access
            this.saveOrderToIndexedDB(order);
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

  /** Exclude moved (split) items: only show items with quantity > 0 so original order shows remaining items only. */
  private filterMovedOrderItems(items: any[]): any[] {
    if (!items || !Array.isArray(items)) return [];
    return items.filter((item: any) => (Number(item.quantity) || 0) > 0);
  }

  /**
   * Recalculate order summary from displayed items when backend summary is stale (e.g. after split).
   * Ensures "view original order" and invoice show the correct amount for the remaining items only.
   */
  private recalculateSummaryFromDisplayedItems(summary: any, items: any[]): any {
    if (!summary || !items || items.length === 0) return summary;
    const itemsSubtotal = items.reduce((sum: number, item: any) => sum + this.safeNum(item.total_dish_price), 0);
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
    this.deliveryData = order.order_type === 'Delivery' ? order.delivery_data : null;
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
            this.paymenMethod = order.transactions[0].payment_method;
            this.deliveryData = order.order_type === 'Delivery' ? response.data.orderDetails[0].delivery_data : null;
            const summary = order.order_summary || {};
            const rawFee = summary.delivery_fees ?? 0;
            const orderType = order.order_type || '';
            const applied = this.normalizeSummaryByOrderType(orderType, summary, Number(rawFee));
            this.deliveryFees = applied.deliveryFees;

            console.log(response.data, 'test');
            this.orderDetails = order;
            this.orderItems = this.filterMovedOrderItems(order.order_details || []);
            this.orderSummary = this.recalculateSummaryFromDisplayedItems(applied.orderSummary, this.orderItems);
            if (this.deliveryData?.delivery_name == ' ') {
              this.deliveryData.delivery_name = 'لا يوجد';
            }
            console.log(' ordtterSummary :', this.orderDetails);

            console.log(' orderSummary :', this.orderSummary);
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

  /** Change Order Type: go to orders list with this order so user can use "تغيير النوع" there. */
  goToChangeOrderType(): void {
    if (!this.orderId) return;
    this.router.navigate(['/orders'], { queryParams: { openOrder: this.orderId, action: 'changeType' } });
  }

  /** Whether to show Cancel/Modify item buttons for this line (unpaid, pending/inprogress item). */
  canShowItemActions(item: any): boolean {
    if (!this.canShowOrderActions()) return false;
    const status = item?.dish_status;
    return status === 'pending' || status === 'inprogress';
  }

  /** Cancel a single item (partial cancel). */
  cancelItem(item: any): void {
    const detailId = item?.id ?? item?.order_detail_id;
    if (detailId == null || !this.orderId) return;

    this.removeItemLoading = detailId;
    const url = `${baseUrl}api/orders/cashier/request-cancel`;
    const token = localStorage.getItem('authToken');
    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });
    const quantity = Number(item?.quantity) || 1;
    const body = {
      order_id: this.orderId,
      items: [{ order_detail_id: detailId, quantity }],
      type: 'partial',
      reason: 'cashier reason',
      flag: 'cancel',
    };

    this.dbService.saveOrderToPrintkitchen(this.orderId, 'cancel').then(() => {}).catch(() => {});

    this.http.post(url, body, { headers }).pipe(
      finalize(() => { this.removeItemLoading = null; })
    ).subscribe({
      next: (res: any) => {
        this.errorMessage = res?.message || 'تم حذف الصنف بنجاح';
        this.status_order = res?.status;
        setTimeout(() => { this.errorMessage = ''; }, 2000);
        this.fetchOrderDetailsFromAPI();
      },
      error: (err) => {
        this.errorMessage = err?.error?.message || 'فشل حذف الصنف';
        setTimeout(() => { this.errorMessage = ''; }, 3000);
      },
    });
  }

  /** Modify Item: go to orders list so user can open this order and use "تعديل الطلب" on the item. */
  goToModifyItem(_item: any): void {
    if (!this.orderId) return;
    this.router.navigate(['/orders'], { queryParams: { openOrder: this.orderId, action: 'modifyItem' } });
  }
}
