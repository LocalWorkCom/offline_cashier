import {
  Component,
  OnInit,
  OnDestroy,
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  NgZone,
  ViewChild,
  TrackByFunction
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { Subject, BehaviorSubject, combineLatest, from, firstValueFrom } from 'rxjs';
import { takeUntil, debounceTime, distinctUntilChanged, switchMap, finalize } from 'rxjs/operators';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { EditOrderModalComponent } from '../edit-order-modal/edit-order-modal.component';
import { baseUrl } from '../environment';
import { HttpClient, HttpHeaders } from '@angular/common/http';
declare var bootstrap: any;
// Import necessary services
import { OrderListService } from '../services/order-list.service';
import { IndexeddbService } from '../services/indexeddb.service';
import { ProductsService } from '../services/products.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { AccordionModule } from 'primeng/accordion';
@Component({
  selector: 'app-new-orders',
  standalone: true,
  templateUrl: './new-orders.component.html',
  styleUrls: ['./new-orders.component.css'],
  imports: [CommonModule, FormsModule, RouterLink,AccordionModule],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class NewOrdersComponent implements OnInit, OnDestroy {
  // State Management
  private destroy$ = new Subject<void>();
  private search$ = new BehaviorSubject<string>('');
  private status$ = new BehaviorSubject<string>('all');
  private orderType$ = new BehaviorSubject<string>('All');

  // Data Streams
  orders$ = new BehaviorSubject<any[]>([]);
  filteredOrders$ = new BehaviorSubject<any[]>([]);
  staticOrders$ = new BehaviorSubject<any[]>([]);
  successMessageModal: any;
  successMessage!: string;
  errMsg: any;
  removeLoading: boolean = false;
  apiUrl = `${baseUrl}`;
  message: string = '';
  messageType: 'success' | 'error' = 'success';
  usingOfflineData: boolean = false;
  cartItems: any;
  storedValueLocalStorage: any;
  filteredCartItems: any;
  // UI State
  loading = false;
  selectedStatus = 'all';
  selectedOrderTypeStatus = 'All';
  searchOrderNumber = '';
  cancelReason: string = '';
  cancelErrorMessage: string = '';
  cancelSuccessMessage: string = '';
  cancelMessage: any;
  selectedItem: any = null;
  selectedOrder: any = null;
  isSubmitting = false;
  page = 1;
  perPage = 30; // smaller first page for faster initial render
  hasMore = true;
  syncing = false;

  // Cached counts to avoid recalculation each CD cycle
  private dynamicOrderTypeCounts: Record<string, number> = {};
  private dynamicStatusTypeCounts: Record<string, Record<string, number>> = {};
  private staticOrderTypeCounts: Record<string, number> = {};
  private staticStatusTypeCounts: Record<string, Record<string, number>> = {};

  // Constants for performance
  readonly ORDER_TYPES = ['All', 'dine-in', 'Takeaway', 'Delivery', 'talabat'];
  readonly STATUSES = ['all', 'pending', 'in_progress', 'readyForPickup', 'completed', 'cancelled', 'static'];

  // Incremental rendering: show first N immediately, then increase gradually
  visibleCountStatic = 12;
  visibleCountDynamic = 12;

  // TrackBy Functions
  trackByOrderId: TrackByFunction<any> = (index, order) =>
    order.order_details?.order_id || order.orderId || index;

  trackByItemId: TrackByFunction<any> = (index, item) =>
    item.order_detail_id || item.dish_id || index;

  trackByOrderType: TrackByFunction<string> = (index, type) => type;
  trackByStatus: TrackByFunction<string> = (index, status) => status;

  constructor(
    private router: Router,
    private cdr: ChangeDetectorRef,
    private ngZone: NgZone,
    private ordersListService: OrderListService,
    private dbService: IndexeddbService,
    private productsService: ProductsService,
    private sanitizer: DomSanitizer,
    private NgbModal: NgbModal,
    private http: HttpClient,
  ) { }

  // ngOnInit() {
  //   this.setupReactiveFilters();
  //   this.loadInitialData();
  //   this.loadStaticOrders();
  // }
async ngOnInit() {
  this.loading = true;

  // 0️⃣ التأكد من أن القيم الافتراضية هي "الكل" -> "الكل"
  // this.selectedStatus = 'all';
  // this.selectedOrderTypeStatus = 'All';
  // this.status$.next('all');
  // this.orderType$.next('All');
  // this.search$.next('');

  // 1️⃣ عرض البيانات من IndexedDB أولًا
  await this.loadOrdersFromIndexedDB();

  // 2️⃣ تحميل الطلبات المعلقة من localStorage
  this.loadStaticOrders();

  // 3️⃣ حساب العدادات بعد تحميل البيانات
  this.recomputeCounts(this.orders$.getValue(), this.staticOrders$.getValue());

  // 4️⃣ إعداد الفلاتر التفاعلية
  this.setupReactiveFilters();

  // 5️⃣ تطبيق الفلترة الأولية لتأكيد عرض الطلبات الديناميكية (الكل -> الكل)
  // this.ngZone.run(() => {
  //   const filtered = this.applyFilters(
  //     this.orders$.getValue(),
  //     this.staticOrders$.getValue(),
  //     'all', // Always start with 'all' status
  //     'All', // Always start with 'All' order type
  //     '' // Empty search
  //   );
  //   this.filteredOrders$.next(filtered);
  //   this.cdr.markForCheck();
  // });

  this.loading = false;

  // 6️⃣ بعد العرض، انتظر 0.3 ثانية ثم ابدأ المزامنة
  if (navigator.onLine) {
    setTimeout(() => {
      console.log('⏳ Waiting 0.3 seconds... then syncing');
      this.syncAllorders();
    }, 300);
  }

  // Schedule incremental reveal in idle time without breaking offline/online/sync
  this.scheduleIncrementalReveal();
}

// 🟢 تحميل الطلبات من IndexedDB
private async loadOrdersFromIndexedDB(): Promise<void> {
  try {
    await this.dbService.init();
    const cachedOrders = await this.dbService.getOrders();


    if (cachedOrders?.length > 0) {
      this.orders$.next(this.processOrders(cachedOrders));
      this.filteredOrders$.next(this.processOrders(cachedOrders));
      //  this.orders$ = this.filteredOrders$;
      console.log('📦 Loaded from IndexedDB:', cachedOrders.length);
    } else {
      console.log('⚠️ No cached orders found');
    }

  } catch (error) {
    console.error('❌ Error loading from IndexedDB:', error);
  }
}

// 🟣 المزامنة الصامتة عند كل refresh
// private syncOrdersInBackgroundold(): void {
//   from(this.ordersListService.getOrdersListE(1, 50))
//     .pipe(takeUntil(this.destroy$))
//     .subscribe({
//       next: async (response) => {
//         if (response?.data?.orders?.length) {
//           console.log('🔄 Sync complete. Updating cache...');

//           // تحديث البيانات في IndexedDB
//           await this.batchSaveOrders(response.data.orders);

//           // تحديث البيانات المعروضة في BehaviorSubject
//           const updatedOrders = await this.dbService.getOrders();
//           this.orders$.next(this.processOrders(updatedOrders));
//           this.filteredOrders$.next(this.processOrders(updatedOrders));
//           // this.orders$ = this.filteredOrders$;

//           console.log('✅ Orders synced silently');
//         }
//       },
//       error: (error) => console.error('❌ Sync error:', error),
//     });
// }

async syncOrdersInBackground(sync: boolean = false): Promise<void> {
  if (this.loading || (!this.hasMore && !sync)) return;

  this.loading = true;
  // فقط نشغل syncing إذا كانت عملية مزامنة كاملة (sync = true)
  if (sync && !this.syncing) {
    this.syncing = true;
    this.cdr.markForCheck();
  }
  console.log(`📥 Fetching page ${this.page}...`);

  try {
    const response: any = await firstValueFrom(
      this.ordersListService.getOrdersListE(this.page, this.perPage)
    );

    if (response?.status && response.data?.orders?.length) {
      const pagination = response.data.pagination;
      console.log('🔄 Sync complete. Updating cache...');

      await this.ngZone.runOutsideAngular(async () => {
        await this.batchSaveOrders(response.data.orders);
        const updatedOrders = await this.dbService.getOrders();

        this.ngZone.run(() => {
          const processed = this.processOrders(updatedOrders);
          this.orders$.next(processed);
          this.filteredOrders$.next(processed);
          // Recompute cached counts when the base data changes
          this.recomputeCounts(processed, this.staticOrders$.getValue());
          this.cdr.markForCheck();
        });
      });

      this.hasMore = pagination.current_page < pagination.last_page;
      this.page++;
      console.log(`✅ Synced & displayed page ${pagination.current_page} / ${pagination.last_page}`);
    }
  } catch (err) {
    console.error('⚠️ Server fetch failed, fallback to offline data:', err);
    this.usingOfflineData = true;
    if (sync) await this.loadOrdersFromIndexedDB();
  } finally {
    this.loading = false;
    // لا نعطل syncing هنا - نترك syncAllorders تتحكم فيه
    // this.syncing = false; // تم نقله إلى syncAllorders
  }
}

async syncAllorders(): Promise<void> {
  console.log('🔁 Starting full sync from server...');
  this.page = 1;
  this.hasMore = true;

  // تفعيل حالة المزامنة لعرض spinner
  this.syncing = true;
  this.cdr.markForCheck();

  try {
    while (this.hasMore) {
      await this.syncOrdersInBackground(true);
      await new Promise(resolve => setTimeout(resolve, 100));
    }

    console.log('✅ Full sync completed. IndexedDB is up to date.');
  } finally {
    // إيقاف حالة المزامنة بعد الانتهاء
    this.syncing = false;
    this.cdr.markForCheck();
  }
}




  // Setup reactive filters with debouncing
  private setupReactiveFilters() {
    // Track previous orders and staticOrders to only recompute counts when they change
    let previousOrdersLength = 0;
    let previousStaticOrdersLength = 0;

    combineLatest([
      this.orders$,
      this.staticOrders$,
      this.status$.pipe(distinctUntilChanged()),
      this.orderType$.pipe(distinctUntilChanged()),
      this.search$.pipe(
        debounceTime(150),
        distinctUntilChanged()
      )
    ]).pipe(takeUntil(this.destroy$))
      .subscribe(([orders, staticOrders, status, orderType, search]) => {
        this.ngZone.runOutsideAngular(() => {
          const filtered = this.applyFilters(orders, staticOrders, status, orderType, search);
          this.filteredOrders$.next(filtered);

          // Only recompute counts when orders or staticOrders actually change
          // This ensures counts remain stable when switching between filters
          const ordersChanged = orders.length !== previousOrdersLength;
          const staticOrdersChanged = staticOrders.length !== previousStaticOrdersLength;

          if (ordersChanged || staticOrdersChanged) {
            this.recomputeCounts(orders, staticOrders);
            previousOrdersLength = orders.length;
            previousStaticOrdersLength = staticOrders.length;
          }

          this.cdr.markForCheck();
        });
      });

    // Listen to orders$ changes to recompute counts when orders change
    this.orders$.pipe(
      distinctUntilChanged((prev, curr) => prev.length === curr.length && JSON.stringify(prev) === JSON.stringify(curr)),
      takeUntil(this.destroy$)
    ).subscribe((orders) => {
      this.recomputeCounts(orders, this.staticOrders$.getValue());
      this.cdr.markForCheck();
    });

    // Also listen to staticOrders$ changes to recompute counts when saved orders are updated
    this.staticOrders$.pipe(
      distinctUntilChanged((prev, curr) => prev.length === curr.length && JSON.stringify(prev) === JSON.stringify(curr)),
      takeUntil(this.destroy$)
    ).subscribe((staticOrders) => {
      this.recomputeCounts(this.orders$.getValue(), staticOrders);

      // Trigger filter recalculation when static orders change
      this.ngZone.run(() => {
        const filtered = this.applyFilters(
          this.orders$.getValue(),
          staticOrders,
          this.status$.getValue(),
          this.orderType$.getValue(),
          this.search$.getValue()
        );
        this.filteredOrders$.next(filtered);
        this.cdr.markForCheck();
      });
    });
  }

  // Load initial data
  // private async loadInitialData() {
  //   this.loading = true;

  //   try {
  //     await this.dbService.init();

  //     // Load cached data first
  //     const cachedOrders = await this.dbService.getOrders();
  //     if (cachedOrders?.length > 0) {
  //       this.orders$.next(this.processOrders(cachedOrders));
  //     }

  //     // Sync with server in background
  //     if (navigator.onLine) {
  //       this.syncOrdersInBackground();
  //     }

  //   } catch (error) {
  //     console.error('Initialization error:', error);
  //   } finally {
  //     this.loading = false;
  //     this.cdr.markForCheck();
  //   }
  // }

  // Load static orders from localStorage
  private loadStaticOrders(): void {
    try {
      const stored = localStorage.getItem('savedOrders');
      const staticOrders = stored ? JSON.parse(stored) : [];
      if (!Array.isArray(staticOrders)) {
        console.warn('⚠️ savedOrders is not an array, setting to empty array');
        this.staticOrders$.next([]);
        this.cdr.markForCheck();
        return;
      }

      console.log(`📦 Loading ${staticOrders.length} static orders from localStorage`);
      this.staticOrders$.next(staticOrders);

      // Note: recomputeCounts will be called in ngOnInit after loadStaticOrders
      // and in setupReactiveFilters when staticOrders$ changes
      this.cdr.markForCheck();
    } catch (error) {
      console.error('❌ Error loading static orders:', error);
      this.staticOrders$.next([]);
      this.cdr.markForCheck();
    }
  }

  // Apply filters efficiently
  private applyFilters(orders: any[], staticOrders: any[], status: string, orderType: string, search: string): any[] {
    const startTime = performance.now();

    let result: any[] = [];

    if (status === 'static') {
      result = this.filterStaticOrders(staticOrders, orderType, search);

      // ===== TESTING: Verify filtering =====
      // this.testHoldOrderFilter(status, orderType, search, staticOrders.length, result.length);
    } else {
      result = this.filterDynamicOrders(orders, status, orderType, search);
    }

    console.log(`Filtering took: ${performance.now() - startTime}ms`);
    return result;
  }

  // Normalize order type to handle variations
  private normalizeOrderType(type: string): string {
    const typeLower = (type || '').toString().trim().toLowerCase();

    // Map all possible variations to standard values
    const typeMappings: { [key: string]: string } = {
      'takeaway': 'Takeaway',
      'استلام': 'Takeaway',
      'إستلام': 'Takeaway',
      'خارج المطعم': 'Takeaway',
      'delivery': 'Delivery',
      'توصيل': 'Delivery',
      'dine-in': 'dine-in',
      'dinein': 'dine-in',
      'dine_in': 'dine-in',
      'في المطعم': 'dine-in',
      'فى المطعم': 'dine-in',
      'talabat': 'talabat',
      'طلبات': 'talabat'
    };

    return typeMappings[typeLower] || type;
  }

  // Filter static orders
  private filterStaticOrders(orders: any[], orderType: string, search: string): any[] {
    let filtered = orders;

    // Filter by order type if not 'All'
    if (orderType !== 'All') {
      const normalizedSelectedType = this.normalizeOrderType(orderType);
      console.log(`🔍 Filtering by orderType: selected="${orderType}" normalized="${normalizedSelectedType}"`);

      filtered = filtered.filter(order => {
        const normalizedOrderType = this.normalizeOrderType(order.type || '');
        const matches = normalizedOrderType === normalizedSelectedType;

        if (!matches) {
          console.log(`❌ Order ${order.orderId} type mismatch: stored="${order.type}" normalized="${normalizedOrderType}" vs selected="${orderType}" normalized="${normalizedSelectedType}"`);
        }

        // Compare normalized types
        return matches;
      });

      console.log(`✅ After orderType filter: ${filtered.length} out of ${orders.length} orders match`);
    }

    // Filter by search text
    if (search) {
      const searchLower = search.toLowerCase();
      filtered = filtered.filter(order => {
        const orderId = (order.orderId || '').toString();
        return orderId.toLowerCase().includes(searchLower);
      });
    }

    return filtered;
  }

  // Filter dynamic orders
  private filterDynamicOrders(orders: any[], status: string, orderType: string, search: string): any[] {
    let filtered = orders;

    if (orderType !== 'All') {
      filtered = filtered.filter(order =>
        order.order_details?.order_type === orderType
      );
    }

    if (status !== 'all') {
      if (status === 'completed') {
        filtered = filtered.filter(order =>
          ['completed', 'delivered'].includes(order.order_details?.status)
        );
      } else {
        filtered = filtered.filter(order =>
          order.order_details?.status === status
        );
      }
    }

    if (search) {
      const searchLower = search.toLowerCase();
      filtered = filtered.filter(order =>
        order.order_details?.order_number?.toString().toLowerCase().includes(searchLower)
      );
    }

    return filtered;
  }

  // Process orders
  private processOrders(orders: any[]): any[] {
    return orders
      .filter(order => order.order_details?.order_type && order.order_details?.status)
      .map(order => ({
        ...order,
        currency_symbol: order.currency_symbol || 'SAR'
      }));
  }

  private scheduleIncrementalReveal(): void {
    const step = 12;
    const bump = () => {
      // increase visible counts gradually; capped by available items
      const dynLen = this.orders$.getValue().length;
      const stLen = this.staticOrders$.getValue().length;
      if (this.visibleCountDynamic < dynLen) {
        this.visibleCountDynamic = Math.min(this.visibleCountDynamic + step, dynLen);
      }
      if (this.visibleCountStatic < stLen) {
        this.visibleCountStatic = Math.min(this.visibleCountStatic + step, stLen);
      }
      this.cdr.markForCheck();
    };

    const schedule = (cb: () => void) => {
      if (typeof (window as any).requestIdleCallback === 'function') {
        (window as any).requestIdleCallback(cb, { timeout: 500 });
      } else {
        setTimeout(cb, 150);
      }
    };

    // Run a few bumps after first render
    schedule(() => bump());
    schedule(() => bump());
    schedule(() => bump());
  }

  // Recompute memoized counts for dynamic and static orders
  private recomputeCounts(dynamicOrders: any[], staticOrders: any[]): void {
    const dynTypeCounts: Record<string, number> = { ['All']: dynamicOrders.length };
    const dynStatusTypeCounts: Record<string, Record<string, number>> = {};

    for (const ord of dynamicOrders) {
      const type = ord.order_details?.order_type ?? 'Unknown';
      const status = ord.order_details?.status ?? 'unknown';
      dynTypeCounts[type] = (dynTypeCounts[type] ?? 0) + 1;

      if (!dynStatusTypeCounts[status]) dynStatusTypeCounts[status] = { ['All']: 0 };
      dynStatusTypeCounts[status]['All'] = (dynStatusTypeCounts[status]['All'] ?? 0) + 1;
      dynStatusTypeCounts[status][type] = (dynStatusTypeCounts[status][type] ?? 0) + 1;
    }

    const stTypeCounts: Record<string, number> = { ['All']: staticOrders.length };
    const stStatusTypeCounts: Record<string, Record<string, number>> = {};
    for (const ord of staticOrders) {
      const type = ord.type ?? 'Unknown';
      stTypeCounts[type] = (stTypeCounts[type] ?? 0) + 1;
      // Static orders currently treated as one status bucket "static"
      const status = 'static';
      if (!stStatusTypeCounts[status]) stStatusTypeCounts[status] = { ['All']: 0 };
      stStatusTypeCounts[status]['All'] = (stStatusTypeCounts[status]['All'] ?? 0) + 1;
      stStatusTypeCounts[status][type] = (stStatusTypeCounts[status][type] ?? 0) + 1;
    }

    this.dynamicOrderTypeCounts = dynTypeCounts;
    this.dynamicStatusTypeCounts = dynStatusTypeCounts;
    this.staticOrderTypeCounts = stTypeCounts;
    this.staticStatusTypeCounts = stStatusTypeCounts;
  }

  // Event Handlers
  selectOrderType(orderType: string): void {
    this.orderType$.next(orderType);
    this.selectedOrderTypeStatus = orderType;

    // If selecting "All" and currently on "static" status, switch to "all" status automatically
    // This ensures that when user clicks "All" in order type filter, they see dynamic orders
    if (orderType === 'All' && this.selectedStatus === 'static') {
      // Switch to "all" status to show dynamic orders
      this.selectStatus('all');
      return; // selectStatus will handle the filter update
    }

    // Force filter update when switching order types
    // The reactive filter will automatically update via combineLatest

    // If currently showing static orders, reload them to apply new filter
    if (this.selectedStatus === 'static') {
      this.loadStaticOrders();

      // Force filter recalculation when switching order type while on static status
      this.ngZone.run(() => {
        const filtered = this.applyFilters(
          this.orders$.getValue(),
          this.staticOrders$.getValue(),
          this.selectedStatus,
          orderType,
          this.search$.getValue()
        );
        this.filteredOrders$.next(filtered);
        this.cdr.markForCheck();
      });
    } else {
      // When switching to non-static status, ensure dynamic orders are properly filtered
      this.ngZone.run(() => {
        const filtered = this.applyFilters(
          this.orders$.getValue(),
          this.staticOrders$.getValue(),
          this.selectedStatus,
          orderType,
          this.search$.getValue()
        );
        this.filteredOrders$.next(filtered);
        this.cdr.markForCheck();
      });
    }
  }

  selectStatus(status: string): void {
    this.status$.next(status);
    this.selectedStatus = status;

    // Force filter update when switching status
    // The reactive filter will automatically update via combineLatest

    // If switching to static, ensure static orders are loaded first
    if (status === 'static') {
      this.loadStaticOrders();

      // Wait for staticOrders$ to be updated, then apply filters
      // Use setTimeout to ensure staticOrders$ is updated before filtering
      setTimeout(() => {
        this.ngZone.run(() => {
          const staticOrders = this.staticOrders$.getValue();
          const filtered = this.applyFilters(
            this.orders$.getValue(),
            staticOrders,
            status,
            this.selectedOrderTypeStatus,
            this.search$.getValue()
          );
          this.filteredOrders$.next(filtered);
          this.cdr.markForCheck();
        });
      }, 0);
    } else {
      // For non-static status, apply filters immediately
      this.ngZone.run(() => {
        const filtered = this.applyFilters(
          this.orders$.getValue(),
          this.staticOrders$.getValue(),
          status,
          this.selectedOrderTypeStatus,
          this.search$.getValue()
        );
        this.filteredOrders$.next(filtered);
        this.cdr.markForCheck();
      });
    }
  }

  onSearchChange(search: string): void {
    this.search$.next(search);
    this.searchOrderNumber = search;
  }

  // Background sync
  // private syncOrdersInBackground(): void {
  //   from(this.dbService.getOrdersLastSync())
  //     .pipe(
  //       switchMap(lastSync => {
  //         const fiveMinutesAgo = Date.now() - (5 * 60 * 1000);
  //         if (lastSync < fiveMinutesAgo) {
  //           return this.ordersListService.getOrdersListE(1, 50);
  //         }
  //         return [];
  //       }),
  //       takeUntil(this.destroy$)
  //     )
  //     .subscribe({
  //       next: async (response) => {
  //         if (response?.data?.orders) {
  //           await this.batchSaveOrders(response.data.orders);
  //           const updatedOrders = await this.dbService.getOrders();
  //           this.orders$.next(this.processOrders(updatedOrders));
  //         }
  //       },
  //       error: (error) => console.error('Sync error:', error)
  //     });
  // }

  // Batch save orders
  private async batchSaveOrders(orders: any[]): Promise<void> {
    const batchSize = 20;
    for (let i = 0; i < orders.length; i += batchSize) {
      const batch = orders.slice(i, i + batchSize);
      await Promise.all(batch.map(order => this.dbService.saveOrder(order)));

      // Give main thread a chance
      if (i % (batchSize * 2) === 0) {
        await new Promise(resolve => setTimeout(resolve, 0));
      }
    }
    await this.dbService.setOrdersLastSync(Date.now());
  }

  // Template Helper Methods
  getOrderTypeLabel(type: string): string {
    const labels: { [key: string]: string } = {
      'All': 'الكل',
      'Takeaway': 'إستلام',
      'Delivery': 'توصيل',
      'dine-in': 'فى المطعم',
      'talabat': 'طلبات',
    };
    return labels[type] || type;
  }

  getOrderTypeImage(type: string): string {
    const images: { [key: string]: string } = {
      'Takeaway': 'assets/images/out.png',
      'talabat': 'assets/images/out.png',
      'Delivery': 'assets/images/delivery.png',
      'dine-in': 'assets/images/in.png'
    };
    return images[type] || 'assets/images/default.png';
  }

  getOrderTypeCount(type: string): number {
    // Always return counts from dynamic orders for the order type filter tabs
    // These tabs should show the total count of dynamic orders regardless of selected status
    return this.dynamicOrderTypeCounts[type] ?? (type === 'All' ? this.orders$.getValue().length : 0);
  }

  getStatusLabel(status: string): string {
    const labels: { [key: string]: string } = {
      'all': 'الكل',
      'pending': 'بانتظار التحضير',
      'in_progress': 'قيد التحضير',
      'readyForPickup': 'جاهزة للاستلام',
      'completed': 'مكتملة',
      'cancelled': 'ملغية',
      'static': 'معلقة',
      'on_way': 'في الطريق',
      'delivered': 'تم الاستلام'
    };
    return labels[status] || status;
  }

  getStatusCount(status: string, orderType: string): number {
    // For static status, return counts from static orders
    if (status === 'static') {
      if (orderType === 'All') {
        return this.staticOrderTypeCounts['All'] ?? this.staticOrders$.getValue().length;
      }
      return this.staticStatusTypeCounts['static']?.[orderType] ?? 0;
    }

    // For all other statuses (including 'all'), return counts from dynamic orders only
    // This ensures status counts are always based on dynamic orders, not static orders
    if (status === 'all') {
      if (orderType === 'All') {
        return this.dynamicOrderTypeCounts['All'] ?? this.orders$.getValue().length;
      }
      return this.dynamicOrderTypeCounts[orderType] ?? 0;
    }

    // For specific statuses, return counts from dynamic orders
    const byType = this.dynamicStatusTypeCounts[status];
    if (!byType) return 0;
    return (orderType === 'All' ? byType['All'] : byType[orderType]) ?? 0;
  }

  translateType(type: string): string {
    const translations: { [key: string]: string } = {
      'dine-in': 'فى المطعم',
      'Takeaway': 'إستلام',
      'talabat': 'طلبات',
      'Delivery': 'توصيل'
    };
    return translations[type] || type;
  }

  getStatusText(status: string): string {
    const statusTexts: { [key: string]: string } = {
      'pending': 'بانتظار التحضير',
      'completed': 'مكتملة',
      'readyForPickup': 'جاهزة للاستلام',
      'on_way': 'في الطريق',
      'in_progress': 'قيد التحضير',
      'delivered': 'تم الاستلام',
      'cancelled': 'ملغية'
    };
    return statusTexts[status] || status;
  }

  getStatusBadgeClass(status: string): string {
    const badgeClasses: { [key: string]: string } = {
      'completed': 'btn-static-secondary',
      'pending': 'btn-static-warning',
      'on_way': 'btn-static-primary',
      'delivered': 'btn-static-purple',
      'cancelled': 'btn-static-danger',
      'readyForPickup': 'btn-static-success',
      'in_progress': 'btn-static-progress'
    };
    return badgeClasses[status] || 'btn-static-secondary';
  }

  getStatusIcon(status: string): string {
    const icons: { [key: string]: string } = {
      'completed': 'fa fa-check-circle text-success',
      'pending': 'fa fa-clock text-warning',
      'cancelled': 'fa fa-times-circle text-danger',
      'readyForPickup': 'fa-bell-concierge text-success',
      'in_progress': 'fa-spinner text-progress',
      'on_way': 'fa-truck text-primary',
      'delivered': 'fa-check text-purple'
    };
    return icons[status] || 'fa fa-clock text-warning';
  }


  ngDoCheck() {
    const newStoredValue = localStorage.getItem('savedOrders');

    if (newStoredValue) {
      try {
        const parsedValue = JSON.parse(newStoredValue);

        // Update cartItems for compatibility
        if (JSON.stringify(parsedValue) !== JSON.stringify(this.cartItems)) {
          this.cartItems = parsedValue;
        }

        // Update staticOrders$ if different
        const currentStatic = this.staticOrders$.getValue();
        if (JSON.stringify(parsedValue) !== JSON.stringify(currentStatic)) {
          const staticOrders = Array.isArray(parsedValue) ? parsedValue : [];
          this.staticOrders$.next(staticOrders);
          this.recomputeCounts(this.orders$.getValue(), staticOrders);
          this.cdr.markForCheck();
        }
      } catch (error) {
        console.error('Error parsing savedOrders in ngDoCheck:', error);
      }
    }
  }

  fetchOrderDetails(): void {
    this.storedValueLocalStorage = localStorage.getItem('savedOrder');
    if (this.storedValueLocalStorage) {
      this.storedValueLocalStorage = JSON.parse(this.storedValueLocalStorage);
      this.cartItems = this.storedValueLocalStorage.cartItems;
    } else {
      this.storedValueLocalStorage = [];
    }
  }
  filterCartItems(): void {
    const stored = localStorage.getItem('savedOrders');
    const parsed = stored ? JSON.parse(stored) : [];

    if (this.selectedOrderTypeStatus !== 'all') {
      this.filteredCartItems = parsed.filter(
        (item: { type: string }) => item.type === this.selectedOrderTypeStatus
      );
    } else {
      this.filteredCartItems = parsed;
    }
  }
  // loadOrderToCart(orderId: number) {
  //   localStorage.setItem('holdCart', JSON.stringify([]));

  //   const savedOrders = JSON.parse(localStorage.getItem('savedOrders') || '[]');
  //   const selectedOrder = savedOrders.find((o: any) => o.orderId === orderId);

  //   if (!selectedOrder) {
  //     console.error('Order not found');
  //     return;
  //   }

  //   const existingCart: any[] = JSON.parse(
  //     localStorage.getItem('holdCart') || '[]'
  //   );
  //   const orderItems: any[] = selectedOrder.items || [];

  //   // Normalize function to compare uniqueness
  //   const normalize = (item: any) => ({
  //     dishId: item.dish?.id || item.dish_id,
  //     sizeId: item.selectedSize?.id || item.sizeId,
  //     addons: (item.selectedAddons || item.addon_categories || [])
  //       .flatMap((cat: any) => cat.addons || [])
  //       .map((addon: any) => addon.id)
  //       .sort()
  //       .join(','),
  //     note: item.note?.trim() || '',
  //   });

  //   const existingKeys = new Set(
  //     existingCart.map((item) => {
  //       const norm = normalize(item);
  //       return `${norm.dishId}-${norm.sizeId}-${norm.addons}-${norm.note}`;
  //     })
  //   );

  //   // Re-map new items into the expected cart structure
  //   const newItems = orderItems
  //     .filter((item) => {
  //       const norm = normalize(item);
  //       const key = `${norm.dishId}-${norm.sizeId}-${norm.addons}-${norm.note}`;
  //       return !existingKeys.has(key);
  //     })
  //     .map((item: any) => {
  //       const dishId = item.dish?.id || item.dish_id;
  //       const dishName = item.dish?.name || item.dish_name;
  //       const dishImage = item.dish?.image || item.dish_image;
  //       const dishDesc = item.dish?.description || item.dish_desc;
  //       const dishPrice = item.dish?.price || item.dish_price;
  //       const sizeName = item.selectedSize?.name || item.size_name;
  //       const sizeId = item.selectedSize?.id || item.sizeId;
  //       const sizePrice = item.selectedSize?.price || item.size_price;
  //       const finalPrice = item.finalPrice || item.final_price;
  //       const quantity = item.quantity || 1;
  //       const note = item.note || '';

  //       const addon_categories = item.addon_categories || [];

  //       const selectedAddons = addon_categories.flatMap(
  //         (cat: any) => cat.addons || []
  //       );

  //       return {
  //         dish: {
  //           id: dishId,
  //           name: dishName,
  //           image: dishImage,
  //           description: dishDesc,
  //           price: dishPrice,
  //         },
  //         dish_order: item.dish_order?.toString() || '-1',
  //         finalPrice: finalPrice,
  //         quantity: quantity,
  //         selectedSize: {
  //           id: sizeId,
  //           name: sizeName,
  //           price: sizePrice,
  //         },
  //         note: note,
  //         selectedAddons: selectedAddons,
  //         addon_categories: addon_categories,
  //       };
  //     });

  //   const updatedCart = [...existingCart, ...newItems];
  //   localStorage.setItem('holdCart', JSON.stringify(updatedCart));

  //   // Set order data
  //   if (selectedOrder.FormDataDetails) {
  //     localStorage.setItem(
  //       'FormDataDetails',
  //       JSON.stringify(selectedOrder.FormDataDetails)
  //     );
  //   }
  //   if (selectedOrder.type) {
  //     localStorage.setItem('selectedOrderType', selectedOrder.type);
  //   }
  //   localStorage.setItem('finalOrderId', orderId.toString());

  //   this.router.navigate(['/home']);
  // }
  loadCartItems(): void {
    const storedCart = localStorage.getItem('savedOrders');
    if (storedCart) {
      this.cartItems = JSON.parse(storedCart);
      // console.log('Loaded cart items:', this.cartItems);
    } else {
      this.cartItems = [];
    }
  }



  // startFiltering(): void {
  //   let filtered = this.orders;

  //   // filter by order type (skip if "All")
  //   if (this.selectedOrderTypeStatus !== 'All') {
  //     filtered = filtered.filter(
  //       (order) =>
  //         order.order_details?.order_type === this.selectedOrderTypeStatus
  //     );
  //   }

  //   // filter by status
  //   if (this.selectedStatus === 'completed') {
  //     // completed includes delivered
  //     filtered = filtered.filter((order) =>
  //       ['completed', 'delivered'].includes(order.order_details?.status)
  //     );
  //   } else if (this.selectedStatus === 'static') {
  //     const stored = localStorage.getItem('savedOrders');
  //     const parsed = stored ? JSON.parse(stored) : [];

  //     if (this.selectedOrderTypeStatus === 'All') {
  //       filtered = parsed; //  take all saved orders
  //     } else {
  //       ``;
  //       filtered = parsed.filter(
  //         (item: any) => item.type === this.selectedOrderTypeStatus
  //       );
  //     }
  //   } else if (this.selectedStatus !== 'all') {
  //     filtered = filtered.filter(
  //       (order) => order.order_details?.status === this.selectedStatus
  //     );
  //   }

  //   // filter by search text
  //   if (this.searchText) {
  //     const search = this.searchText.toLowerCase();
  //     filtered = filtered.filter((order) =>
  //       order.order_details?.order_number
  //         ?.toString()
  //         .toLowerCase()
  //         .includes(search)
  //     );
  //   }

  //   this.filteredOrders = filtered;
  // }


  getOrderLink(order: any): string {
    const type = order.order_details.order_type;
    const status = order.order_details.status;
    const orderId = order.order_details.order_id;

    if (
      type === 'Delivery' &&
      (status === 'pending' || status === 'in_progress' || status === 'readyForPickup')
    ) {
      return '/cart/' + orderId;
    }

    return '/order-details/' + orderId;
  }

  // Static orders management
  selectedOrderIdToDelete: string | null = null;

  openDeleteModal(orderId: string): void {
    this.selectedOrderIdToDelete = orderId;

    // Show modal (Bootstrap)
    const modalElement = document.getElementById('deleteOrderModal');
    if (modalElement) {
      const modal = new bootstrap.Modal(modalElement);
      modal.show();
    }
  }

  confirmDelete(): void {
    if (!this.selectedOrderIdToDelete) return;

    try {
      const saved = localStorage.getItem('savedOrders');
      if (!saved) return;

      const orders = JSON.parse(saved);
      if (!Array.isArray(orders)) return;

      const orderToDelete = orders.find((o: any) => o.orderId === this.selectedOrderIdToDelete);
      const initialCount = orders.length;

      const updatedOrders = orders.filter(
        (order: any) => order.orderId !== this.selectedOrderIdToDelete
      );

      localStorage.setItem('savedOrders', JSON.stringify(updatedOrders));

      // ===== TESTING: Verify delete operation =====
      // this.testHoldOrderDelete(this.selectedOrderIdToDelete, orderToDelete, initialCount, updatedOrders.length);

      // Update staticOrders$ and recompute counts
      this.staticOrders$.next(updatedOrders);
      this.recomputeCounts(this.orders$.getValue(), updatedOrders);

      // Reload cart items
      this.loadCartItems();

      // Refresh filtered orders if showing static status
      if (this.selectedStatus === 'static') {
        this.selectStatus('static'); // This will trigger filtering
      }

      // Close modal
      const modalEl = document.getElementById('deleteOrderModal');
      if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        modalInstance?.hide();
      }

      this.selectedOrderIdToDelete = null;
      this.cdr.markForCheck();
    } catch (error) {
      console.error('Error deleting static order:', error);
    }
  }

  async loadOrderToCart(orderId: number) {
    // مسح السلة السابقة (في localStorage و IndexedDB)
    localStorage.setItem('holdCart', JSON.stringify([]));
    try {
      await this.dbService.clearCart();
    } catch (error) {
      console.warn('⚠️ Error clearing cart from IndexedDB:', error);
    }

    const savedOrders = JSON.parse(localStorage.getItem('savedOrders') || '[]');
    const selectedOrder = savedOrders.find((o: any) => o.orderId === orderId);

    if (!selectedOrder) {
      console.error('Order not found');
      return;
    }

    const existingCart: any[] = JSON.parse(
      localStorage.getItem('holdCart') || '[]'
    );
    const orderItems: any[] = selectedOrder.items || [];

    // Normalize function to compare uniqueness
    const normalize = (item: any) => ({
      dishId: item.dish?.id || item.dish_id,
      sizeId: item.selectedSize?.id || item.sizeId,
      addons: (item.selectedAddons || item.addon_categories || [])
        .flatMap((cat: any) => cat.addons || [])
        .map((addon: any) => addon.id)
        .sort()
        .join(','),
      note: item.note?.trim() || '',
    });

    const existingKeys = new Set(
      existingCart.map((item) => {
        const norm = normalize(item);
        return `${norm.dishId}-${norm.sizeId}-${norm.addons}-${norm.note}`;
      })
    );

    // Re-map new items into the expected cart structure
    const newItems = orderItems
      .filter((item) => {
        const norm = normalize(item);
        const key = `${norm.dishId}-${norm.sizeId}-${norm.addons}-${norm.note}`;
        return !existingKeys.has(key);
      })
      .map((item: any) => {
        const dishId = item.dish?.id || item.dish_id;
        const dishName = item.dish?.name || item.dish_name;
        const dishImage = item.dish?.image || item.dish_image;
        const dishDesc = item.dish?.description || item.dish_desc;
        const dishPrice = item.dish?.price || item.dish_price;
        const sizeName = item.selectedSize?.name || item.size_name;
        const sizeId = item.selectedSize?.id || item.sizeId;
        const sizePrice = item.selectedSize?.price || item.size_price;
        const finalPrice = item.finalPrice || item.final_price;
        const quantity = item.quantity || 1;
        const note = item.note || '';

        const addon_categories = item.addon_categories || [];

        const selectedAddons = addon_categories.flatMap(
          (cat: any) => cat.addons || []
        );

        return {
          dish: {
            id: dishId,
            name: dishName,
            image: dishImage,
            description: dishDesc,
            price: dishPrice,
          },
          dish_order: item.dish_order?.toString() || '-1',
          finalPrice: finalPrice,
          quantity: quantity,
          selectedSize: {
            id: sizeId,
            name: sizeName,
            price: sizePrice,
          },
          note: note,
          selectedAddons: selectedAddons,
          addon_categories: addon_categories,
        };
      });

    const updatedCart = [...existingCart, ...newItems];

    // حفظ في localStorage (holdCart)
    localStorage.setItem('holdCart', JSON.stringify(updatedCart));

    // حفظ في localStorage (cart) أيضاً للتوافق
    localStorage.setItem('cart', JSON.stringify(updatedCart));

    // حفظ في IndexedDB
    try {
      await this.dbService.init();
      // مسح السلة أولاً
      await this.dbService.clearCart();
      // إضافة كل عنصر في IndexedDB
      for (const item of updatedCart) {
        await this.dbService.addToCart(item);
      }
      console.log('✅ Cart items saved to IndexedDB:', updatedCart.length);
    } catch (error) {
      console.error('❌ Error saving cart to IndexedDB:', error);
    }

    // Set order data
    if (selectedOrder.FormDataDetails) {
      localStorage.setItem(
        'FormDataDetails',
        JSON.stringify(selectedOrder.FormDataDetails)
      );
      // حفظ FormDataDetails في IndexedDB أيضاً
      try {
        await this.dbService.saveFormData(selectedOrder.FormDataDetails);
      } catch (error) {
        console.warn('⚠️ Error saving FormDataDetails to IndexedDB:', error);
      }
    }
    if (selectedOrder.type) {
      localStorage.setItem('selectedOrderType', selectedOrder.type);
    }
    localStorage.setItem('finalOrderId', orderId.toString());

    // حفظ currentOrderId للتوافق مع side-details
    localStorage.setItem('currentOrderId', orderId.toString());

    // أبلغ الـ ProductsService لتحديث الـ cart$ فوراً بدون ريفرش
    try {
      this.productsService.loadCart();
    } catch (_) {}

    this.router.navigate(['/home']);
  }



  ngOnDestroy() {
    this.destroy$.next();
    this.destroy$.complete();
  }

  continueOrder(order: any): void {
    console.log('continueOrder', order);
    localStorage.setItem('selectedOrderType', order.order_details.order_type);
    this.productsService.destroyCart();
    localStorage.removeItem('cart');
    localStorage.setItem('currentOrderId', order.order_details.order_id);
    localStorage.setItem('currentOrderData', JSON.stringify(order));
    // Ensure side panel logic picks correct order type immediately
    // localStorage.setItem('selectedOrderType', order.order_details?.order_type || '');

    const type = order.order_details?.order_type;
    const status = order.order_details?.status;
    const orderId = order.order_details?.order_id;

    const navigateAndRefresh = (commands: any[]) => {
      this.router.navigate(commands).then(() => {
        setTimeout(() => window.location.reload(), 0);
      });
    };

    if (
      type === 'Delivery' &&
      (status === 'pending' || status === 'in_progress' || status === 'readyForPickup')
    ) {
      navigateAndRefresh(['/cart', orderId]);
    } else {
      navigateAndRefresh(['/home']);
    }
  }
  highlightMatch(text: string, search: string): SafeHtml {
    if (!search) return text;
    const regex = new RegExp(`(${search})`, 'gi');
    const result = text.replace(regex, `<mark class="test">$1</mark>`);
    return this.sanitizer.bypassSecurityTrustHtml(result);
  }
  openEditModal(item: any) {
    const hasExtraData = item?.size || item?.dish_addons?.[0];

    const modalSize = hasExtraData ? 'lg' : 'md';

    const editModal = this.NgbModal.open(EditOrderModalComponent, {
      size: modalSize,
      centered: true,
    });
    editModal.componentInstance.itemId = item.order_detail_id;
    // editModal.componentInstance.selectedItem = item;

    console.log(item, modalSize);

    editModal.result.then(
      (result) => {
        if (result) {
          this.successMessage = 'تم تحديث الطلب بنجاح';
          this.successMessageModal.show();
          setTimeout(() => {
            this.successMessageModal.dismiss();
            document
              .querySelectorAll('.modal-backdrop')
              .forEach((el) => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
          }, 1000);
          console.log('✅ Modal closed with data:', result);
        }
      },
      (reason) => {
        console.log('❌ Modal dismissed:', reason);
      }
    );
  }

   removeDish(orderDetailId: number, order: any): void {
     this.removeLoading = true;
     const url = `${this.apiUrl}api/orders/cashier/request-cancel`;
     console.log(orderDetailId, "id to delete");

     // 1️⃣ Find dish inside this order by order_detail_id
     const dish = order.order_items.find(
       (d: any) => d.order_detail_id === orderDetailId
     );
     if (!dish) {
       console.warn('❌ Dish not found in this order');
       this.removeLoading = false;
       return;
     }

     // 📴 Offline handling
     if (!navigator.onLine) {
       try {
         // Remove item from local order
         order.order_items = order.order_items.filter(
           (d: any) => d.order_detail_id !== orderDetailId
         );
         order.order_details.order_items_count = order.order_items.length;

         // Queue offline action
         order.pendingActions = order.pendingActions || [];
         order.pendingActions.push({
           type: 'delete_order_item',
           payload: {
             order_id: order.order_details.order_id,
             item_id: orderDetailId,
             reason: 'cashier reason'
           },
           createdAt: new Date().toISOString()
         });

         // Recompute totals and save
         this.recomputeOrderTotals(order);

         if (!order.order_details || !order.order_details.order_id) {
           console.error('Missing order_details.order_id - cannot save to IndexedDB');
           this.errMsg = 'خطأ في بيانات الطلب';
           this.removeLoading = false;
           return;
         }

         this.dbService.saveOrder(order).then(() => {
           console.log('✅ Order saved offline');
         }).catch(err => {
           console.error('❌ Failed to save offline:', err);
           this.errMsg = 'تعذر حفظ البيانات محليًا';
         });

         // Close modal and show success
         const modalElement = document.getElementById('deleteConfirmModal');
         if (modalElement) {
           const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
           modalInstance.hide();
         }

         this.showMessageModal('تم حذف الطلب (وضع عدم الاتصال)', 'success');
         this.removeLoading = false;

         setTimeout(() => window.location.reload(), 500);
         return;
       } catch (e) {
         this.removeLoading = false;
         this.errMsg = 'تعذر حذف الطلب في وضع عدم الاتصال';
         setTimeout(() => (this.errMsg = null), 3000);
         return;
       }
     }

     // 2️⃣ Build request body
     const body = {
       order_id: order.order_details.order_id,
       items: [
         {
           item_id: orderDetailId, // API expects this
           quantity: dish.quantity ?? 1, // cancel this qty
         },
       ],
       type: 'partial',
       reason: 'cashier reason',
     };

     // 3️⃣ Call API
     this.http
       .post(url, body)
       .pipe(
         finalize(() => {
           this.removeLoading = false; // ✅ يشتغل بعد الـ next أو error
         })
       )
       .subscribe({
         next: (res: any) => {
           if (res.status) {
             order.items = order.order_items.filter(
               (d: any) => d.order_detail_id !== orderDetailId
             );
             const modalElement = document.getElementById('deleteConfirmModal');
             if (modalElement) {
               const modalInstance =
                 bootstrap.Modal.getInstance(modalElement) ||
                 new bootstrap.Modal(modalElement);
               modalInstance.hide();
             }
             this.showMessageModal(
               res.message || 'تم حذف الطلب بنجاح',
               'success'
             );
           } else {
             // ✅ ناخد كل الرسائل من errorData (بغض النظر عن المفتاح)
             const errors = res.errorData
               ? Object.values(res.errorData)
                 .flat()
                 .map((e: any) => String(e))
               : [];
             this.errMsg = errors.length
               ? errors.join(' \n ')
               : res.message || 'تعذر حذف الطلب';
             /*           this.showMessageModal(errMsg, 'error');
              */

             setTimeout(() => {
               this.errMsg = null;
             }, 2000);
           }
         },
         error: (err) => {
           const errors = err.error?.errorData
             ? Object.values(err.error.errorData)
               .flat()
               .map((e: any) => String(e))
             : [];
           this.errMsg = errors.length
             ? errors.join(' \n ')
             : err.error?.message || 'خطأ في الاتصال بالخادم';
           /*         this.showMessageModal(errMsg, 'error');
            */
           setTimeout(() => {
             this.errMsg = null;
           }, 2000);
         },
       });
   }


  @ViewChild('messageModal') messageModal: any;
  showMessageModal(msg: string, type: 'success' | 'error') {
    this.message = msg;
    this.messageType = type;

    const modalRef = this.NgbModal.open(this.messageModal, {
      centered: true,
      size: 'sm',
      keyboard: false,
    });

    setTimeout(() => {
      modalRef.close();
      const backdrops = document.querySelectorAll('.modal-backdrop');
      backdrops.forEach((backdrop) => backdrop.remove());
    }, 1500);
  }

  // في الـ component
  getButtonClass(order: any): string {
    const status = order.order_details?.status;

    const statusClasses: any = {
      'completed': 'btn-static-secondary',
      'pending': 'btn-static-warning',
      'cancelled': 'btn-static-danger',
      'on_way': 'btn-static-primary',
      'delivered': 'btn-static-purple',
      'readyForPickup': 'btn-static-success',
      'in_progress': 'btn-static-progress'
    };

    return statusClasses[status] || 'bg-lightyellow';
  }

  setSelectedItem(item: any, order: any) {
    this.selectedItem = item;
    this.selectedOrder = order;
  }

  confirmRemove() {
    console.log(this.selectedOrder);
    console.log(this.selectedItem);
    if (!this.selectedItem || !this.selectedOrder) return;
    this.removeDish(this.selectedItem.order_detail_id, this.selectedOrder);
  }

//   selectedOrder: any = null;
// cancelReason: string = '';


openOrderModal(order: any): void {
  this.selectedOrder = order;
  this.cancelReason = '';

  // Initialize modal item states for a clean UX
  if (this.selectedOrder?.order_items?.length) {
    for (const item of this.selectedOrder.order_items) {
      // Preserve original price for display and avoid accidental mutation during selection
      if (item.originalTotalDishPrice === undefined) {
        item.originalTotalDishPrice = item.total_dish_price;
      }
      // Default selected quantity equals original quantity (returned starts at 0)
      if (item.selectedQuantity === undefined || item.selectedQuantity === null) {
        item.selectedQuantity = item.quantity;
      }
      // Pre-calc unit price for later computations; do NOT change total_dish_price during selection
      if (!item.unitPrice && item.quantity) {
        item.unitPrice = item.total_dish_price / item.quantity;
      }
      // Uncheck by default when opening
      item.isChecked = false;
    }
  }

  const modalElement = document.getElementById('orderModal');
  if (modalElement) {
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
  }
}
  submitCancelRequest(order: any): void {
    if (this.isSubmitting) return; // منع تكرار الضغط لو لسه الطلب شغال
    this.isSubmitting = true; // ⏳ بداية الطلب

    const isDeleteAction =
      order?.order_details?.payment_status == 'unpaid' &&
      order?.order_details?.status === 'pending';

    // Require reason for both actions (delete or return invoice)
    if (!this.cancelReason || !this.cancelReason.trim()) {
      this.isSubmitting = false;
      this.cancelErrorMessage = isDeleteAction ? 'السبب مطلوب لحذف الطلب' : 'السبب مطلوب لطلب المرتجع';
      this.cancelSuccessMessage = '';
      setTimeout(() => {
        this.cancelErrorMessage = '';
      }, 3000);
      return;
    }

    const selectedItems = order.order_items
      .filter((item: any) => item.isChecked)
      .map((item: any) => {
        const originalQuantity = item.quantity;
        const selectedQuantity = item.selectedQuantity ?? item.quantity;
        const returnedQuantity = originalQuantity - selectedQuantity;

        return {
          item_name: item.dish_name,
          item_id: item.order_detail_id,
          quantity: returnedQuantity,
          isFullyReturned: selectedQuantity === 0,
        };
      })
      .filter((item: any) => item.quantity > 0);

    if (selectedItems.length === 0) {
      this.cancelErrorMessage = 'يرجى اختيار صنف واحد على الأقل بكمية مرتجعة';
      this.cancelSuccessMessage = '';
      this.isSubmitting = false; // رجّع الزرار لأنه مفيش إرسال فعلي
      setTimeout(() => {
        this.cancelErrorMessage = '';
      }, 4000);
      return;
    }

    // 📴 Offline handling: apply changes locally, persist, close modal, refresh
    if (!navigator.onLine) {
      try {
        for (const sel of selectedItems) {
          const target = order.order_items.find((it: any) => it.order_detail_id === sel.item_id);
          if (!target) continue;
          const newSelectedQty = Math.max(0, (target.selectedQuantity ?? target.quantity));
          target.quantity = newSelectedQty;
          if (!target.unitPrice && target.quantity) {
            target.unitPrice = target.total_dish_price / target.quantity;
          }
          if (newSelectedQty === 0) {
            target.dish_status = 'cancel';
          }
          if (target.unitPrice !== undefined) {
            target.total_dish_price = target.unitPrice * newSelectedQty;
          }
        }

        // Remove items with zero quantity from list
        order.order_items = order.order_items.filter((it: any) => (it.quantity ?? 0) > 0);
        order.order_details.order_items_count = order.order_items.length;

        // Queue pending action for later sync
        order.pendingActions = order.pendingActions || [];
        order.pendingActions.push({
          type: isDeleteAction ? 'delete_order_items' : 'return_invoice_items',
          payload: {
            order_id: order.order_details.order_id,
            items: selectedItems,
            reason: this.cancelReason || ''
          },
          createdAt: new Date().toISOString()
        });

        this.recomputeOrderTotals(order);

        // Ensure order_details.order_id exists for IndexedDB keyPath
        if (!order.order_details || !order.order_details.order_id) {
          console.error('Missing order_details.order_id - cannot save to IndexedDB');
          this.isSubmitting = false;
          this.cancelErrorMessage = 'خطأ في بيانات الطلب';
          return;
        }

        this.dbService.saveOrder(order).then(() => {
          console.log('✅ Order saved to IndexedDB successfully');
        }).catch(err => {
          console.error('❌ Failed to save order to IndexedDB:', err);
          this.cancelErrorMessage = 'تعذر حفظ البيانات محليًا';
        });

        this.isSubmitting = false;
        this.cancelSuccessMessage = isDeleteAction ? 'تم حذف العناصر محليًا (أوفلاين)' : 'تم تطبيق المرتجع محليًا (أوفلاين)';
        this.cancelErrorMessage = '';

        const orderModal = document.getElementById('orderModal');
        if (orderModal) {
          const instance = bootstrap.Modal.getInstance(orderModal) || new bootstrap.Modal(orderModal);
          instance.hide();
        }
        setTimeout(() => window.location.reload(), 300);
      } catch (e) {
        this.isSubmitting = false;
        this.cancelErrorMessage = 'تعذر تطبيق العملية في وضع عدم الاتصال';
        setTimeout(() => (this.cancelErrorMessage = ''), 3000);
      }
      return;
    }

    if (!isDeleteAction && order.order_details.status !== 'cancelled') {
      this.cancelMessage = `تم ارسال المرتجع الي مدير الفرع
وبانتظار الموافقة`;
    } else if (isDeleteAction) {
      this.cancelMessage = 'تم بنجاح';
    }

    const isFullReturn =
      selectedItems.length === order.order_items.length &&
      selectedItems.every((item: any) => item.isFullyReturned);

    const body = {
      order_id: order.order_details.order_id,
      items: selectedItems.map((item: any) => ({
        item_id: item.item_id,
        quantity: item.quantity,
        item_name: item.item_name,
      })),
      type: isFullReturn ? 'full' : 'partial',
      reason: this.cancelReason || '',
    };

    console.log('Sending:', body, selectedItems, order);

    this.http
      .post(`${baseUrl}api/orders/cashier/request-cancel`, body)
      .subscribe({
        next: (res: any) => {
          this.isSubmitting = false; // ✅ رجّع الزرار بعد الرد

          this.cancelSuccessMessage = 'تم إرسال طلب المرتجع بنجاح';
          this.cancelErrorMessage = '';
          setTimeout(() => {
            this.cancelSuccessMessage = '';
          }, 2000);

          if (!res?.status) {
            let errorText = 'حدث خطأ أثناء الإرسال';
            const reasonErrors = res?.errorData?.reason;
            if (Array.isArray(reasonErrors) && reasonErrors.length > 0) {
              errorText = reasonErrors[0];
            }
            const statusErrors = res?.errorData?.status;
            if (Array.isArray(statusErrors) && statusErrors.length > 0) {
              errorText = statusErrors[0];
            }
            const Err = res?.errorData?.error;
            if (Array.isArray(Err) && Err.length > 0) {
              errorText = Err[0];
            }
            const errorString = res?.errorData?.error;
            if (typeof errorString === 'string' && errorString.trim() !== '') {
              errorText = errorString;
            }
            console.log(res);
            this.cancelErrorMessage = errorText;
            this.cancelSuccessMessage = '';

            setTimeout(() => {
              this.cancelErrorMessage = '';
            }, 2000);
          }

          if (res?.status === true) {
            this.cancelSuccessMessage = 'تم إرسال طلب المرتجع بنجاح';
            this.cancelErrorMessage = '';
            this.cancelReason = '';
            // Hide the unified modal and refresh page after a brief delay
            const orderModal = document.getElementById('orderModal');
            if (orderModal) {
              const instance = bootstrap.Modal.getInstance(orderModal) || new bootstrap.Modal(orderModal);
              instance.hide();
            }

            // Cleanup checked state
            order.order_items.forEach((item: any) => {
              item.isChecked = false;
              item.selectedQuantity = item.quantity;
            });

            // Optional small success modal, then full refresh
            const successModalEl = document.getElementById('successSmallModal');
            if (successModalEl) {
              const successModal = new bootstrap.Modal(successModalEl, { backdrop: 'static' });
              successModal.show();
              setTimeout(() => {
                successModal.hide();
                document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                window.location.reload();
              }, 900);
            } else {
              setTimeout(() => window.location.reload(), 300);
            }
          }
        },

        error: (err) => {
          this.isSubmitting = false; // ✅ رجّع الزرار بعد الفشل
          console.error('Error:', err);

          let errorText;
          const reasonErrors = err?.error?.errorData?.error?.reason;
          if (Array.isArray(reasonErrors) && reasonErrors.length > 0) {
            errorText = reasonErrors[0];
          }

          const statusErrors = err?.error?.errorData?.error?.status;
          if (Array.isArray(statusErrors) && statusErrors.length > 0) {
            errorText = statusErrors[0];
          }
          const Err = err?.error?.errorData?.error?.error;
          if (Array.isArray(Err) && Err.length > 0) {
            errorText = Err[0];
          }
          const errorString = err?.error?.errorData?.error;
          if (typeof errorString === 'string' && errorString.trim() !== '') {
            errorText = errorString;
          }
          this.cancelErrorMessage = errorText;
          this.cancelSuccessMessage = '';

          setTimeout(() => {
            this.cancelErrorMessage = '';
          }, 4000);
        },
      });
  }
   increaseItem(item: any, index: number): void {
    if (item.selectedQuantity === undefined) {
      item.selectedQuantity = item.quantity;
    }

    // Calculate unit price once
    if (!item.unitPrice) {
      item.unitPrice = item.total_dish_price / item.quantity;
    }

    if (item.selectedQuantity < item.quantity) {
      item.selectedQuantity += 1;
      // Keep displayed price unchanged during selection
    }
  }

  decreaseItem(item: any, index: number): void {
    if (item.selectedQuantity === undefined) {
      item.selectedQuantity = item.quantity;
    }

    // Calculate unit price once
    if (!item.unitPrice) {
      item.unitPrice = item.total_dish_price / item.quantity;
    }

    if (item.selectedQuantity > 0) {
      item.selectedQuantity -= 1;
      // Keep displayed price unchanged during selection
    }
  }

  onItemCheckToggle(item: any, checked: boolean): void {
    item.isChecked = checked;
    if (item.selectedQuantity === undefined || item.selectedQuantity === null) {
      item.selectedQuantity = item.quantity;
    }
    if (!item.unitPrice && item.quantity) {
      item.unitPrice = item.total_dish_price / item.quantity;
    }

    // When checking, start with returned = 0 -> selectedQuantity = full quantity
    // When unchecking, also reset to original
    item.selectedQuantity = item.quantity;

    // Do not mutate item.total_dish_price during selection; keep original price visible
  }

  private recomputeOrderTotals(order: any): void {
    try {
      const subtotal = (order.order_items || []).reduce((sum: number, it: any) => sum + (Number(it.total_dish_price) || 0), 0);
      if (order.invoiceSummary) {
        order.invoiceSummary.subtotal_price = subtotal;
        const coupon = Number(order.invoiceSummary.coupon_value || 0);
        const service = Number(order.invoiceSummary.service_fee || 0);
        const tax = Number(order.invoiceSummary.tax_value || 0);
        const delivery = Number(order.invoiceSummary.delivery_fees || 0);
        const total = Math.max(0, subtotal - coupon + service + tax + delivery);
        order.invoiceSummary.total_price = total;
      }
      if (typeof order.total_price !== 'undefined') {
        const coupon = Number(order.coupon_value || 0);
        order.total_price = Math.max(0, subtotal - coupon);
      }
    } catch (e) {
      console.warn('Failed to recompute order totals offline', e);
    }
  }

}
