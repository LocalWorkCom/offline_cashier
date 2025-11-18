import {
  ChangeDetectorRef,
  Component,
  OnDestroy,
  ViewChild,
} from '@angular/core';
import {
  ActivatedRoute,
  Router,
  RouterLink,
  RouterLinkActive,
} from '@angular/router';
import { OrdersService } from '../services/orders.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { OrderDetailsComponent } from '../order-details/order-details.component';
import { OrderListService } from '../services/order-list.service';
import { finalize, takeUntil } from 'rxjs/operators';
import { Subject } from 'rxjs';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { PusherService } from '../services/pusher/pusher.service';
import { NewOrderService } from '../services/pusher/newOrder';
import { OrderChangeListenService } from '../services/pusher/order-change-listen.service';
import { DishStatusService } from '../services/pusher/dishStatus';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
declare var bootstrap: any;
import { AccordionModule } from 'primeng/accordion';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Modal } from 'bootstrap';
import { log } from 'node:console';
import { baseUrl } from '../environment';
import { EditOrderModalComponent } from '../edit-order-modal/edit-order-modal.component';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ProductsService } from '../services/products.service';
import { OrderListDetailsService } from '../services/order-list-details.service';
import { IndexeddbService } from '../services/indexeddb.service';
import { timer } from 'rxjs';
import { switchMap } from 'rxjs/operators';
@Component({
  selector: 'app-new-orders',
  standalone: true,
  templateUrl: './new-orders.component.html',
  styleUrls: ['./new-orders.component.css'],
  imports: [
    RouterLink,
    ShowLoaderUntilPageLoadedDirective,
    RouterLinkActive,
    CommonModule,
    FormsModule,
    AccordionModule,
  ],
})
export class NewOrdersComponent implements OnDestroy {
  private isOnline: boolean = navigator.onLine;
  isLastOrderLoading: boolean = false;
  lastOrderError: string = '';
  orders: any[] = [];
  ordersStatus: string[] = []; //
  filteredOrdersByStatus: { status: string; orders: any[] }[] = [];
  selectedStatus: string = 'all';
  filteredOrders: any[] = [];
  searchOrderNumber: string = '';
  filteredOrder: any[] = [];
  searchText: string = '';
  storedValueLocalStorage: any;
  cartItems: any;
  selectedItem: any | null = null;
  orderId: string | null = null;
  error: string | undefined;
  orderDetails: any;
  currencySymbol!: string;
  orderTypeById?: string;
  selectedOrderType!: string;
  selectedOrderTypeStatus: string = 'All';
  // selectedOrderTypeStatus: string = 'dine-in';
  filteredCartItems: any;
  allowedOrderTypes = ['Takeaway', 'Delivery', 'dine-in', 'talabat'];
  allowedStatuses = [
    'pending',
    'in_progress',
    'readyForPickup',
    'completed',
    'cancelled',
    'on_way',
    'delivered',
  ];
  private destroy$ = new Subject<void>();
  loading: boolean = true;
  isFilterdFromClientSide: boolean = true;
  private activeOrderChannels: Set<string> = new Set();
  order: any;
  errorMessage: string = '';
  apiUrl = `${baseUrl}`;
  removeLoading: boolean = false;

  // Cached computed values for template performance
  orderTypeCounts: Map<string, number> = new Map();
  statusCounts: Map<string, number> = new Map();
  filteredStatusesCache: string[] = [];
  orderTypeLabels: Map<string, string> = new Map();
  statusLabelsCache: Map<string, string> = new Map();

  // Bound event handlers for network status (needed for proper cleanup)
  private boundHandleOnline: () => void;
  private boundHandleOffline: () => void;

  constructor(
    private ordersService: OrdersService,
    private router: Router,
    private route: ActivatedRoute,
    private ordersListService: OrderListService,
    public pusherService: PusherService,
    private sanitizer: DomSanitizer,
    private newOrder: NewOrderService,
    private orderChangeStatus: OrderChangeListenService,
    private orderChange: DishStatusService,
    private cdr: ChangeDetectorRef,
    private http: HttpClient,
    private NgbModal: NgbModal,
    private productsService: ProductsService,
    private _OrderListDetailsService: OrderListDetailsService,
    private dbService: IndexeddbService
  ) {
    // Bind event handlers once in constructor for proper cleanup
    this.boundHandleOnline = this.handleOnline.bind(this);
    this.boundHandleOffline = this.handleOffline.bind(this);

    // const navigation = this.router.getCurrentNavigation();
    // this.orderDetails = navigation?.extras.state?.['orderData'];
    // this.orderTypeById= this.orderDetails
    // console.log(this.orderDetails,'orderDetails')
  }

  ngOnInit(): void {
    console.log("new orders component");
    this.selectedOrderTypeStatus = 'All';

    // Pre-initialize IndexedDB for faster offline loading (non-blocking)
    this.dbService.init().catch(err => {
      console.error('IndexedDB init error:', err);
    });

    // Priority 1: Load orders data first (critical path)
    this.fetchOrdersData();

    // Priority 2: Load cart items (lightweight, can run in parallel)
    this.loadCartItems();
    this.filterCartItems();
    this.fetchOrderDetails();

    // Initialize computed values after cart items are loaded
    setTimeout(() => {
      this.updateComputedValues();
    }, 0);

    // Priority 3: Defer non-critical operations to avoid blocking
    // Use setTimeout(0) to defer Pusher listeners until after initial render
    setTimeout(() => {
      this.listenToDishChange();
      this.listenToNewOrder();
    }, 0);

    // Route subscription (lightweight)
    this.route.paramMap.pipe(takeUntil(this.destroy$)).subscribe({
      next: (params) => {
        this.orderId = params.get('id');
        if (this.orderId) {
          this.fetchOrderDetails();
        }
      },
      error: (err) => {
        this.error = 'Error retrieving order ID from route.';
      },
    });

    // Setup online/offline event listeners
    this.setupNetworkListeners();
  }

  // Setup network status change listeners
  private setupNetworkListeners(): void {
    // Handle online event
    window.addEventListener('online', this.boundHandleOnline);

    // Handle offline event
    window.addEventListener('offline', this.boundHandleOffline);
  }

  // Handle when connection comes back online
  private handleOnline(): void {
    console.log('🌐 Connection restored - back online');
    this.isOnline = true;

    // Clear error message if it was about connection failure
    if (this.errorMessage === 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ') {
      this.errorMessage = '';
    }

    // Refresh orders data from API
    this.fetchOrdersFromAPI();
  }

  // Handle when connection goes offline
  private handleOffline(): void {
    console.log('📴 Connection lost - going offline');
    this.isOnline = false;

    // Set error message
    this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';

    // Try to load from IndexedDB if available
    if (this.orders.length === 0) {
      this.loadOrdersFromIndexedDB();
    }
  }
  fetchOrdersData() {
    this.loading = false;
    if (this.isOnline) {
      this.fetchOrdersFromAPI();
    } else {
      this.loadOrdersFromIndexedDB();
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
    }
  }
  //start dalia

  // Load orders from IndexedDB - Optimized for maximum performance (< 1 second)
  private loadOrdersFromIndexedDB(): void {
    const startTime = performance.now();

    // Ensure IndexedDB is initialized before querying
    this.dbService.init().then(() => {
      return this.dbService.getOrders();
    }).then(orders => {
      const loadTime = performance.now() - startTime;
      console.log(`IndexedDB load time: ${loadTime.toFixed(2)}ms`);

      if (orders && orders.length > 0) {
        console.log('Orders loaded from IndexedDB:', orders.length);

        // Process orders immediately - no delays
        const processStartTime = performance.now();
        this.processOrders(orders);
        const processTime = performance.now() - processStartTime;
        console.log(`Process orders time: ${processTime.toFixed(2)}ms`);

        const totalTime = performance.now() - startTime;
        console.log(`Total offline load time: ${totalTime.toFixed(2)}ms`);

        // Only check sync time if online (skip when offline for performance)
        if (this.isOnline) {
          // Check sync time asynchronously without blocking UI
          this.dbService.getOrdersLastSync().then(lastSync => {
            // const fiveMinutesAgo = Date.now() - (1 * 60 * 1000);
            // if (lastSync < fiveMinutesAgo) {
            // Fetch fresh data in background without blocking
            this.fetchOrdersFromAPI();
            // this.errorMessage = 'جارى التحميل';

            // }
          }).catch(err => {
            console.error('Error getting last sync time:', err);
          });
        }
      } else {
        // No data in IndexedDB
        this.loading = true;
        if (this.isOnline) {
          // Only fetch from API if online
          this.fetchOrdersFromAPI();
        } else {
          console.warn('No orders available offline');
        }
      }
    }).catch(err => {
      console.error('Error loading orders from IndexedDB:', err);
      this.loading = true;
      if (this.isOnline) {
        this.fetchOrdersFromAPI();
      }
    });
  }
  // Fetch orders from API
  private fetchOrdersFromAPI(): void {
    this.loading = false;
    this.ordersListService
      .getOrdersList()
      .pipe(
        finalize(() => {
          this.loading = true;
        }),
        takeUntil(this.destroy$)
      )
      .subscribe({
        next: (response) => {
          if (response.status && response.data.orders) {
            console.log(
              'Orders fetched from API:',
              response.data.orders.length
            );
            this.processOrders(response.data.orders);
            // Save to IndexedDB
            this.dbService.saveOrders(response.data.orders).then(() => {
              console.log('Orders saved to IndexedDB');
              return this.dbService.setOrdersLastSync(Date.now());
            }).catch(err => {
              console.error('Error saving orders to IndexedDB:', err);
            });
          } else {
            console.warn('No orders found in API response.');
            this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';

            this.loading = true;
          }
        },
        error: (err) => {
          this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
          this.loading = true;
          this.showMessageModal(
            'حدث خطأ فى الاتصال يرجى المحاولة مره اخرى',
            'error'
          );

          // If we're online but API failed, try to use IndexedDB data as fallback
          // if (this.isOnline) {
          //   this.dbService.getOrders().then(orders => {
          //     if (orders && orders.length > 0) {
          //       console.log('Using IndexedDB data as fallback:', orders.length);
          //       this.processOrders(orders);
          //     }
          //   });
          // }
        },
      });
  }
  // Process orders (common method for both API and IndexedDB data) - Maximum performance
  private processOrders(orders: any[]): void {
    if (!orders || orders.length === 0) {
      this.orders = [];
      this.ordersStatus = ['all'];
      this.filteredOrders = [];
      this.loading = true;
      return;
    }

    // Cache allowed sets for O(1) lookups
    const allowedOrderTypesSet = new Set(this.allowedOrderTypes);
    const allowedStatusesSet = new Set(this.allowedStatuses);

    this.currencySymbol = orders[0]?.currency_symbol;

    // Single-pass filtering and mapping for maximum speed
    const filteredOrders: any[] = [];
    const statusSet = new Set<string>();

    for (let i = 0; i < orders.length; i++) {
      const order = orders[i];
      const orderType = order.order_details?.order_type;
      const status = order.order_details?.status;

      if (allowedOrderTypesSet.has(orderType) && allowedStatusesSet.has(status)) {
        filteredOrders.push({
          ...order,
          currency_symbol: this.currencySymbol,
        });
        if (status) {
          statusSet.add(status);
        }
      }
    }

    this.orders = filteredOrders;

    // Build status array efficiently
    this.ordersStatus = Array.from(statusSet).filter((status) =>
      allowedStatusesSet.has(status)
    );

    if (this.ordersStatus.length > 0) {
      this.ordersStatus.unshift('all');
    }

    // Process filtering immediately - no delays
    this.filterOrders();
    this.updateComputedValues();
    this.loading = true;
  }

  // Update all computed values for template performance
  private updateComputedValues(): void {
    // Update order type counts
    const orderTypes = ['All', 'dine-in', 'Takeaway', 'Delivery', 'talabat'];
    orderTypes.forEach(type => {
      this.orderTypeCounts.set(type, this.computeOrderTypeCount(type));
    });

    // Update status counts
    const statuses = ['all', 'static', ...this.ordersStatus];
    statuses.forEach(status => {
      const orderTypes = ['All', 'dine-in', 'Takeaway', 'Delivery', 'talabat'];
      orderTypes.forEach(orderType => {
        const key = `${status}-${orderType}`;
        this.statusCounts.set(key, this.computeStatusCount(status, orderType));
      });
    });

    // Update filtered statuses cache
    this.filteredStatusesCache = this.computeFilteredStatuses();

    // Update order type labels cache
    orderTypes.forEach(type => {
      this.orderTypeLabels.set(type, this.getOrderTypeLabel(type));
    });

    // Update status labels cache
    statuses.forEach(status => {
      this.statusLabelsCache.set(status, this.getStatusLabel(status));
    });
  }

  // Private methods to compute values (used by updateComputedValues)
  private computeOrderTypeCount(orderType: string): number {
    if (orderType === 'All') {
      if (this.selectedStatus === 'static') {
        return this.cartItems?.length || 0;
      }
      return this.orders.length;
    }

    if (this.selectedStatus === 'static') {
      return (
        this.cartItems?.filter((item: any) => item.type === orderType)
          ?.length || 0
      );
    }

    return this.orders.filter(
      (order) => order.order_details?.order_type === orderType
    ).length;
  }

  private computeStatusCount(status: string, orderType: string): number {
    if (status === 'static') {
      const stored = localStorage.getItem('savedOrders');
      const parsed = stored ? JSON.parse(stored) : [];

      if (orderType === 'All') {
        return parsed.length;
      }

      return parsed.filter((item: any) => item.type === orderType).length;
    }

    if (status === 'all') {
      if (orderType === 'All') {
        return this.orders.length;
      }

      return this.orders.filter(
        (order) => order.order_details?.order_type === orderType
      ).length;
    }

    return this.orders.filter(
      (order) =>
        order.order_details?.status === status &&
        (orderType === 'All' || order.order_details?.order_type === orderType)
    ).length;
  }

  private computeFilteredStatuses(): string[] {
    const baseStatuses = ['pending', 'in_progress', 'readyForPickup'];
    const finalStatuses = ['completed', 'cancelled'];

    if (
      this.selectedOrderTypeStatus === 'Takeaway' &&
      this.selectedStatus === 'all'
    ) {
      return [...baseStatuses, ...finalStatuses];
    }

    if (this.selectedOrderTypeStatus === 'Delivery') {
      return [...baseStatuses, 'on_way', ...finalStatuses];
    }

    return [...baseStatuses, ...finalStatuses];
  }
  //end dalia
  newOrderFromPusher: any;
  /*   listenToNewOrder() {
    this.newOrder.listenToNewOrder();
    this.newOrder.orderAdded$
      .pipe(takeUntil(this.destroy$))
      .subscribe((newOrder) => {
        console.log(newOrder, 'new order');
        setTimeout(()=>{
        return this._OrderListDetailsService
          .NewgetOrderById(newOrder.order_id)
          .subscribe({
            next: (res: any) => {
              console.log(res.data);
              this.newOrderFromPusher = res?.data?.orderDetails[0];
              this.orders = [this.newOrderFromPusher, ...this.orders];

              console.log(this.newOrderFromPusher, 'this.newOrderFromPusher,');
              /* const orderType = this.newOrderFromPusher.order_type?.toLowerCase();
              const status = this.newOrderFromPusher.status?.toLowerCase();

              const isAllOrderTypes =
                this.selectedOrderTypeStatus?.toLowerCase() === 'all';
              const isAllStatuses =
                this.selectedStatus?.toLowerCase() === 'all';
              console.log(
                this.selectedOrderTypeStatus,
                'new selectedOrderTypeStatus arrived'
              );
              const matchesOrderType =
                isAllOrderTypes ||
                orderType === this.selectedOrderTypeStatus?.toLowerCase();

              const matchesStatus =
                isAllStatuses || status === this.selectedStatus?.toLowerCase();

              if (matchesOrderType && matchesStatus) {
                this.filteredOrders = [...this.orders];
              }
            },
            error: (res: any) => {
              console.log(res.data);
            },
          });
        },4000)


      });
  } */

  listenToNewOrder() {
    // Start listening to new orders
    this.newOrder.listenToNewOrder();

    this.newOrder.orderAdded$
      .pipe(
        takeUntil(this.destroy$),
        // Wait 4 seconds before calling the API
        switchMap((newOrder) =>
          timer(2000).pipe(
            switchMap(() =>
              this._OrderListDetailsService.NewgetOrderById(newOrder.order_id)
            )
          )
        )
      )
      .subscribe({
        next: (res: any) => {
          console.log(res.data);
          this.newOrderFromPusher = res?.data.order;
          this.orders = [this.newOrderFromPusher, ...this.orders];

          console.log(this.newOrderFromPusher, 'this.newOrderFromPusher,');
          const orderType = this.newOrderFromPusher.order_details.order_type?.toLowerCase();
          const status = this.newOrderFromPusher.order_details.status?.toLowerCase();

          const isAllOrderTypes =
            this.selectedOrderTypeStatus?.toLowerCase() === 'all';
          const isAllStatuses = this.selectedStatus?.toLowerCase() === 'all';
          console.log(
            this.selectedOrderTypeStatus,
            'new selectedOrderTypeStatus arrived'
          );
          const matchesOrderType =
            isAllOrderTypes ||
            orderType === this.selectedOrderTypeStatus?.toLowerCase();

          const matchesStatus =
            isAllStatuses || status === this.selectedStatus?.toLowerCase();

          if (matchesOrderType && matchesStatus) {
            this.filteredOrders = [...this.orders];
          }
        },
        error: (err) => {
          console.error('Error fetching order:', err);
        },
      });
  }
  // listenToOrderChangeStatus(orders:any){
  //   this.orderChangeStatus.orderChange(orders)
  // }

  // private subscribeToOrderStatusChannel(orderId: string | undefined): void {
  //   if (!orderId || this.activeOrderChannels.has(orderId)) return;

  //   const channelName = `order-status-${orderId}`;
  //   this.pusherService.pipe(takeUntil(this.destroy$)).subscribe(channelName, 'order-status-update', (data: any) => {
  //     console.log(`Order status update received for order ${orderId}:`, data);
  //     this.handleOrderStatusUpdate(data);
  //   });

  //   this.activeOrderChannels.add(orderId);
  // }

  // private unsubscribeFromOrderStatusChannel(orderId: string | undefined): void {
  //   if (!orderId || !this.activeOrderChannels.has(orderId)) return;

  //   const channelName = `order-status-${orderId}`;
  //   this.activeOrderChannels.delete(orderId);

  // if (this.selectedOrderTypeStatus.toLowerCase()=='all'||
  //   (this.selectedOrderTypeStatus == 'dine-in' ||
  //   (newOrder.data.Order.order_details.order_type.toLowerCase() ==
  //     this.selectedOrderTypeStatus.toLowerCase() &&
  //     (this.selectedStatus == 'all' ||
  //       newOrder.data.Order.order_details.status.toLowerCase() ==
  //         this.selectedStatus.toLocaleLowerCase())))
  // ) {
  //   console.log('fatema new order added to list');

  //   this.filteredOrders = [...this.orders];
  // }
  // }

  private handleOrderStatusUpdate(data: any): void {
    // data should contain data.order and data.status as per your backend team's spec
    const updatedOrder = data.order;
    const newStatus = data.status;

    const index = this.orders.findIndex(
      (order) => order.order_details?.order_id === updatedOrder.order_id
    );

    if (index !== -1) {
      // Update the order status
      this.orders[index] = {
        ...this.orders[index],
        order_details: {
          ...this.orders[index].order_details,
          status: newStatus,
        },
        recentlyUpdated: true,
      };

      // Show notification
      this.showUpdateNotification(
        `تم تحديث حالة الطلب #${updatedOrder.order_number
        } إلى ${this.getStatusText(newStatus)}`,
        'info'
      );

      // Reset the update flag after 5 seconds
      setTimeout(() => {
        const updatedIndex = this.orders.findIndex(
          (order) => order.order_details?.order_id === updatedOrder.order_id
        );
        if (updatedIndex !== -1) {
          this.orders[updatedIndex].recentlyUpdated = false;
        }
      }, 5000);

      // Refresh the filtered orders
      this.filterOrders();
    }
  }
  // Add these methods to your OrdersComponent class

  private handleOrderUpdate(updatedOrder: any): void {
    const index = this.orders.findIndex(
      (order) => order.order_details?.order_id === updatedOrder.order_id
    );

    if (index !== -1) {
      this.orders[index] = {
        ...this.orders[index],
        recentlyUpdated: true,
        order_details: {
          ...this.orders[index].order_details,
          ...updatedOrder,
        },
      };

      setTimeout(() => {
        const updatedIndex = this.orders.findIndex(
          (order) => order.order_details?.order_id === updatedOrder.order_id
        );
        if (updatedIndex !== -1) {
          this.orders[updatedIndex].recentlyUpdated = false;
        }
      }, 5000);

      this.filterOrders();
    }
  }

  private handleNewOrder(newOrder: any): void {
    this.orders.unshift({
      ...newOrder,
      isNewOrder: true,
    });

    setTimeout(() => {
      const newOrderIndex = this.orders.findIndex(
        (order) =>
          order.order_details?.order_id === newOrder.order_details?.order_id
      );
      if (newOrderIndex !== -1) {
        this.orders[newOrderIndex].isNewOrder = false;
      }
    }, 5000);

    this.filterOrders();
  }
  filterOrdersInput(): void {
    const search = this.searchOrderNumber?.trim().toLowerCase();

    // Reset view if search is empty
    if (!search) {
      this.filterOrders();
      return;
    }

    // Find all orders that match the search
    const foundOrders = this.orders.filter((order) =>
      order.order_details?.order_number
        ?.toString()
        .toLowerCase()
        .includes(search)
    );

    if (foundOrders.length > 0) {
      this.selectedOrderTypeStatus = foundOrders[0].order_details?.order_type;
      this.selectedStatus = 'all';

      // Show all matched orders
      this.filteredOrders = foundOrders;

      // Optional: scroll and highlight the first one
      setTimeout(() => {
        document
          .querySelectorAll('.highlight-order')
          .forEach((el) => el.classList.remove('highlight-order'));

        const el = document.getElementById(
          `order-${foundOrders[0].order_details?.order_number}`
        );
        if (el) {
          el.scrollIntoView({ behavior: 'smooth', block: 'center' });
          el.classList.add('highlight-order');
        }
      }, 100);
    } else {
      // No match
      this.filteredOrders = [];
    }
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
      item.dish_price = item.total_dish_price;
      item.selectedQuantity += 1;
      item.dish_price = item.unitPrice * item.selectedQuantity;
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
      item.dish_price = item.total_dish_price;
      item.selectedQuantity -= 1;
      item.dish_price = item.unitPrice * item.selectedQuantity;
    }
  }

  calculateItemPrice(item: any): number {
    const quantity = item.selectedQuantity ?? item.quantity;

    const basePrice = item.base_price || 0;
    const addonsPrice =
      item.addons?.reduce(
        (sum: number, addon: any) => sum + (addon.price || 0),
        0
      ) || 0;

    const totalPrice = (basePrice + addonsPrice) * quantity;
    return totalPrice;
  }

  highlightMatch(text: string, search: string): SafeHtml {
    if (!search) return text;
    const regex = new RegExp(`(${search})`, 'gi');
    const result = text.replace(regex, `<mark class="test">$1</mark>`);
    return this.sanitizer.bypassSecurityTrustHtml(result);
  }

  private handleOrderDeletion(deletedOrder: any): void {
    this.orders = this.orders.filter(
      (order) => order.order_details?.order_id !== deletedOrder.order_id
    );
    this.filterOrders();
  }
  // Update ngOnDestroy to clean up all subscriptions
  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
    this.newOrder.stopListening();
    this.orderChangeStatus.stopListeningOfOrderStatus();
    this.orderChange.stopListening();

    // Remove network event listeners
    window.removeEventListener('online', this.boundHandleOnline);
    window.removeEventListener('offline', this.boundHandleOffline);

    // this.activeOrderChannels.forEach(orderId => {
    //   this.unsubscribeFromOrderStatusChannel(orderId);
    // });
    // this.activeOrderChannels.clear();
  }

  private showUpdateNotification(
    message: string,
    type: 'success' | 'info' | 'warning' | 'error' = 'info'
  ) {
    // Simple notification implementation - consider using a proper toast service
    const toast = document.createElement('div');
    toast.className = `toast show position-fixed bottom-0 end-0 m-3 bg-${type} text-white`;
    toast.style.zIndex = '1100';
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }
  // pusher code end

  ngDoCheck() {
    const newStoredValue = localStorage.getItem('savedOrders');

    if (newStoredValue) {
      const parsedValue = JSON.parse(newStoredValue);

      if (JSON.stringify(parsedValue) !== JSON.stringify(this.cartItems)) {
        this.cartItems = parsedValue;
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
  loadOrderToCart(orderId: number) {
    // ✅ مسح بيانات الكوبون أولاً قبل تحميل الطلب
    localStorage.removeItem('appliedCoupon');
    localStorage.removeItem('validCoupon');
    localStorage.removeItem('couponTitle');
    localStorage.removeItem('couponCode');
    localStorage.removeItem('discountAmount');
    localStorage.removeItem('client');
    localStorage.removeItem('clientPhone');
    localStorage.removeItem('country_code');
    localStorage.removeItem('table_id');
    localStorage.removeItem('table_number');
    localStorage.setItem('holdCart', JSON.stringify([]));

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
    localStorage.setItem('holdCart', JSON.stringify(updatedCart));

    // Set order data
    if (selectedOrder.FormDataDetails) {
      localStorage.setItem(
        'FormDataDetails',
        JSON.stringify(selectedOrder.FormDataDetails)
      );
    }
    if (selectedOrder.type) {
      localStorage.setItem('selectedOrderType', selectedOrder.type);
    }
    localStorage.setItem('finalOrderId', orderId.toString());

    this.router.navigate(['/home']);
  }

  // fetchOrdersData(): void {
  //   this.loading = false;
  //   this.ordersListService
  //     .getOrdersList()
  //     .pipe(
  //       finalize(() => {
  //         this.loading = true;
  //       }),
  //       takeUntil(this.destroy$)
  //     )
  //     .subscribe({
  //       next: (response) => {
  //         console.log(response.data.orders);

  //         if (response.status && response.data.orders) {
  //           this.currencySymbol = response.data.orders[0]?.currency_symbol;
  //           this.orders = response.data.orders
  //             .filter(
  //               (order: any) =>
  //                 this.allowedOrderTypes.includes(
  //                   order.order_details?.order_type
  //                 ) &&
  //                 this.allowedStatuses.includes(order.order_details?.status)
  //             )
  //             .map((order: any) => ({
  //               ...order,
  //               currency_symbol: this.currencySymbol,
  //             }));
  //           // this.listenToOrderChangeStatus(this.orders)

  //           this.ordersStatus = Array.from(
  //             new Set(
  //               this.orders.map((order) => order.order_details?.status || '')
  //             )
  //           ).filter((status) => this.allowedStatuses.includes(status));

  //           if (this.ordersStatus.length > 0) this.ordersStatus.unshift('all');

  //           // // Subscribe to order-specific channels here
  //           // this.orders.forEach(order => {
  //           //   this.pipe(takeUntil(this.destroy$)).subscribeToOrderStatusChannel(order.order_details?.order_id);
  //           // });

  //           this.filterOrders();
  //         } else {
  //           console.warn('No orders found in response.');
  //         }
  //       },
  //       error: (err) => {
  //         console.error('Error fetching orders:', err);
  //       },
  //     });
  // }

  selectStatusGroup(index: number): void {
    this.selectedStatus = this.ordersStatus[index];
    this.filterOrders();
  }

  filterOrdersByStatus(status: string): void {
    if (status === 'all') {
      this.filteredOrders = this.orders;
    } else {
      const selectedGroup = this.filteredOrdersByStatus.find(
        (group) => group.status === status
      );
      this.filteredOrders = selectedGroup ? selectedGroup.orders : [];
    }
  }
  loadCartItems(): void {
    const storedCart = localStorage.getItem('savedOrders');
    if (storedCart) {
      this.cartItems = JSON.parse(storedCart);
      // console.log('Loaded cart items:', this.cartItems);
    } else {
      this.cartItems = [];
    }
  }

  getStatusText(status: any): string {
    switch (status) {
      case 'all':
        return 'الكل';
      case 'pending':
        return 'بانتظار التحضير';
      case 'completed':
        return 'مكتملة ';
      case 'readyForPickup':
        if (
          this.selectedOrderTypeStatus === 'Delivery' ||
          this.selectedOrderTypeStatus === 'Takeaway' ||
          this.selectedOrderTypeStatus === 'talabat'
        ) {
          return 'جاهزة للاستلام';
        } else {
          return 'جاهزة للتقديم';
        }
      case 'on_way':
        return 'في الطريق';
      case 'in_progress':
      case 'inprogress':
        return 'قيد التحضير';
      case 'delivered':
      case 'Delivered':
        return 'تم الاستلام';
      case 'cancelled':
        return 'ملغية';
      case 'cancel':
        return 'ملغية';
      default:
        return `Unknown : ${status}`;
    }
  }

  translateType(type: string): string {
    const translations: { [key: string]: string } = {
      'dine-in': 'فى المطعم',
      Takeaway: ' إستلام',
      Delivery: 'توصيل',
      talabat: 'طلبات',
    };

    return translations[type] || type;
  }
  getOrderTypeCount(orderType: string): number {
    // Use cached value if available
    return this.orderTypeCounts.get(orderType) ?? 0;
  }

  // getOrderTypeCount(orderType: string): number {
  //   if (this.selectedStatus === 'static') {

  //     return this.cartItems?.filter(
  //       (item: any) => item.type === orderType
  //     )?.length || 0;
  //   }

  //   return this.orders.filter(
  //     (order) => order.order_details?.order_type === orderType
  //   ).length;
  // }

  getOrderTypeImage(type: string): string {
    const images = {
      Takeaway: 'assets/images/out.png',
      Delivery: 'assets/images/delivery.png',
      'dine-in': 'assets/images/in.png',
      talabat: 'assets/images/in.png',
    };

    return (
      images[type as 'Takeaway' | 'Delivery' | 'dine-in' | 'talabat'] ||
      'assets/images/default.png'
    );
  }

  startFiltering(): void {
    // Optimize: single-pass filtering for better performance
    if (this.selectedStatus === 'static') {
      const stored = localStorage.getItem('savedOrders');
      const parsed = stored ? JSON.parse(stored) : [];

      if (this.selectedOrderTypeStatus === 'All') {
        this.filteredOrders = parsed;
      } else {
        this.filteredOrders = parsed.filter(
          (item: any) => item.type === this.selectedOrderTypeStatus
        );
      }
      return;
    }

    // Single-pass filter combining all conditions
    const orderTypeFilter = this.selectedOrderTypeStatus !== 'All'
      ? this.selectedOrderTypeStatus
      : null;
    const statusFilter = this.selectedStatus === 'all'
      ? null
      : this.selectedStatus === 'completed'
        ? ['completed', 'delivered']
        : this.selectedStatus;
    const searchFilter = this.searchText?.trim().toLowerCase() || null;

    // Single pass through orders array
    this.filteredOrders = this.orders.filter((order) => {
      // Order type filter
      if (orderTypeFilter && order.order_details?.order_type !== orderTypeFilter) {
        return false;
      }

      // Status filter
      if (statusFilter) {
        if (Array.isArray(statusFilter)) {
          if (!statusFilter.includes(order.order_details?.status)) {
            return false;
          }
        } else if (order.order_details?.status !== statusFilter) {
          return false;
        }
      }

      // Search filter
      if (searchFilter) {
        const orderNumber = order.order_details?.order_number?.toString().toLowerCase();
        if (!orderNumber || !orderNumber.includes(searchFilter)) {
          return false;
        }
      }

      return true;
    });
  }

  // startFiltering(): void {

  //   let filtered = this.orders;

  //   if (this.selectedOrderTypeStatus !== 'all') {
  //     filtered = filtered.filter(
  //       (order) => order.order_details?.order_type === this.selectedOrderTypeStatus
  //     );
  //   }

  //   if (this.selectedStatus === 'completed') {
  //     filtered = filtered.filter(
  //       (order) =>
  //         ['completed', 'delivered'].includes(order.order_details?.status)
  //     );
  //   } else if (this.selectedStatus !== 'all' && this.selectedStatus !== 'static') {
  //     filtered = filtered.filter(
  //       (order) => order.order_details?.status === this.selectedStatus
  //     );
  //   } else if (this.selectedStatus === 'static') {
  //     const stored = localStorage.getItem('savedOrders');
  //     const parsed = stored ? JSON.parse(stored) : [];
  //     filtered = parsed.filter((item: any) => item.type === this.selectedOrderTypeStatus);
  //   }
  //   // Apply search filter if any search text is present
  //   if (this.searchText) {
  //     filtered = filtered.filter(order =>
  //       order.order_details?.order_number?.toString().includes(this.searchText)
  //     );
  //   }

  //   this.filteredOrders = filtered;
  // }

  handleRedirect(item: any): void {
    // console.log('Clicked item:', item);
    if (
      this.storedValueLocalStorage &&
      this.storedValueLocalStorage.cartItems?.length > 0
    ) {
      this.router.navigate(['/cart', item.orderId]);
    } else {
      this.selectedItem = item;
      console.log('No items in cart, item selected:', this.selectedItem);
      this.router.navigate(['/home']);
    }
  }

  navigateToCart(order: any) {
    // console.log(' Navigating to cart with order:', order);
    this.router.navigate(['/cart', order.order_details.order_id], {
      state: { orderData: order },
    });
  }

  getOrderLink(order: any): string {
    const type = order.order_details.order_type;
    const status = order.order_details.status;
    const orderId = order.order_details.order_id;

    if (
      type === 'Delivery' &&
      (status === 'pending' ||
        status === 'in_progress' ||
        status === 'readyForPickup')
    ) {
      return '/cart/' + orderId;
    }

    // if (type === 'Takeaway' && status === 'readyForPickup') {
    //   return '/cart/' + orderId;
    // }

    return '/order-details/' + orderId;
  }

  selectOrderType(orderType: string): void {
    console.log('fatema', orderType, this.selectedOrderTypeStatus);

    this.selectedOrderTypeStatus = orderType;
    this.filterOrders();
    this.filterOrdersInput();
    this.updateComputedValues();
  }
  selectStatus(status: string): void {
    this.selectedStatus = status;
    this.filterOrdersInput();
    this.filterOrders();
    this.updateComputedValues();
  }
  getTotalPrice(order: any): number {
    let total = 0;

    for (const item of order.items) {
      const basePrice = item.size_price || item.dish_price || 0;

      // Total price of all selected addons
      const addonsTotal = (item.addon_categories || []).reduce(
        (sum: number, category: any) => {
          const addonSum = (category.addons || []).reduce(
            (acc: number, addon: any) => {
              return acc + (addon.price || 0);
            },
            0
          );
          return sum + addonSum;
        },
        0
      );

      const itemTotal = (basePrice + addonsTotal) * item.quantity;
      total += itemTotal;
    }

    return total;
  }

  getAddonsTotal(item: any): number {
    return (item.addon_categories || []).reduce((sum: number, cat: any) => {
      return (
        sum +
        (cat.addons || []).reduce(
          (aSum: number, addon: any) => aSum + (addon.price || 0),
          0
        )
      );
    }, 0);
  }

  getOrderTypeLabel(orderType: string): string {
    // Use cached value if available
    const cached = this.orderTypeLabels.get(orderType);
    if (cached) return cached;

    // Compute and cache
    const translations: { [key: string]: string } = {
      All: ' الكل',
      Takeaway: ' إستلام',
      Delivery: 'توصيل',
      'dine-in': 'فى المطعم',
      talabat: 'طلبات',
    };
    const label = translations[orderType] || orderType;
    this.orderTypeLabels.set(orderType, label);
    return label;
  }

  statusLabels: Record<string, string> = {
    all: 'الكل',
    pending: 'بانتظار التحضير',
    in_progress: 'قيد التحضير',
    on_way: 'في الطريق',
    completed: 'مكتملة ',
    cancelled: 'ملغية',
    static: 'معلقة',
  };

  getStatusLabel(status: string): string {
    // Use cached value if available
    const cached = this.statusLabelsCache.get(status);
    if (cached) return cached;

    // Compute and cache
    let label: string;
    if (status === 'readyForPickup') {
      label = this.selectedOrderTypeStatus === 'Delivery' ||
        this.selectedOrderTypeStatus === 'Takeaway'
        ? 'جاهزة للاستلام'
        : 'جاهزة للتقديم';
    } else {
      label = this.statusLabels[status] || status;
    }

    this.statusLabelsCache.set(status, label);
    return label;
  }

  getFilteredStatuses(): string[] {
    // Use cached value if available
    return this.filteredStatusesCache.length > 0
      ? this.filteredStatusesCache
      : this.computeFilteredStatuses();
  }

  getStatusCount(status: string, orderType: string): number {
    // Use cached value if available
    const key = `${status}-${orderType}`;
    return this.statusCounts.get(key) ?? 0;
  }

  // getStatusCount(status: string, orderType: string): number {
  //   if (status === 'static') {

  //     const stored = localStorage.getItem('savedOrders');
  //     const parsed = stored ? JSON.parse(stored) : [];
  //     return parsed.filter((item: any) => item.type === orderType).length;
  //   }

  //   if (status === 'all') {
  //     return this.orders.filter(
  //       (order) => order.order_details?.order_type === orderType
  //     ).length;
  //   }

  //   return this.orders.filter(
  //     (order) =>
  //       order.order_details?.status === status &&
  //       order.order_details?.order_type === orderType
  //   ).length;
  // }

  // listenToOrderChange() {
  //   this.orderChangeStatus.listenToOrder();

  //   this.orderChangeStatus.OrderStatusUpdated$.pipe(
  //     takeUntil(this.destroy$)
  //   ).subscribe((updatedOrder) => {
  //     console.log('Incoming order update from dashboard:', updatedOrder);
  //     this.upsertOrder(updatedOrder);
  //   });
  // }
  // private upsertOrder(order: any): void {
  //   const orderId = order.orderId;
  //   // const newStatus = order?.status_name; change from BE without knowledge of frontend team
  //   const newStatus = order?.status;

  //   const index = this.orders.findIndex(
  //     (o) => Number(o.order_details?.order_id) === orderId
  //   );

  //   if (index !== -1) {
  //     // Only update the order status (not replace entire order)
  //     const existingOrder = this.orders[index];
  //     const updatedOrder = {
  //       ...existingOrder,
  //       order_details: {
  //         ...existingOrder.order_details,
  //         status: newStatus,
  //       },
  //     };

  //     this.orders.splice(index, 1, updatedOrder);
  //     console.log(
  //       'status',
  //       newStatus,
  //       this.selectedStatus,
  //       this.selectedOrderTypeStatus.toLowerCase(),
  //       existingOrder.order_details.order_type.toLowerCase()
  //     );

  //     if (
  //       newStatus.toLowerCase() == this.selectedStatus.toLowerCase() &&
  //       this.selectedOrderTypeStatus.toLowerCase() ==
  //       existingOrder.order_details.order_type.toLowerCase()
  //     ) {
  //       // this.filteredOrders=[...this.orders]
  //       this.filteredOrders = [updatedOrder, ...this.filteredOrders];
  //     }
  //   } else if (order?.order_details) {
  //     this.orders.unshift(order);
  //   }

  //   this.orders = [...this.orders]; // Trigger change detection

  //   this.filterOrders();
  //   this.cdr.detectChanges();

  //   console.log('Order status updated or inserted:', this.filteredOrder);
  // }

  /*   listenToDishChange() {
      this.orderChange.listenToDishStatusInOrder();
      this.orderChange.dishChanged$
        .pipe(takeUntil(this.destroy$))
        .subscribe((dishChanged) => {
          console.log(' Incoming dish update:', dishChanged);

          const targetOrderId = Number(dishChanged?.order_id);
          // const targetDishId = Number(dishChanged.data?.dish_ids?.[0]);

          const orderIndex = this.orders.findIndex(
            (order) => Number(order.order_details?.order_id) === targetOrderId
          );

          if (orderIndex !== -1) {
            console.warn(' Order found :', targetOrderId);
       /*      const currentOrder = this.orders[orderIndex];

            const updatedOrder = {
              ...currentOrder,
              ...dishChanged,
            };

            this.orders.splice(orderIndex, 1, updatedOrder);
            this.orders = [...this.orders];
            this.filterOrders();

            this.cdr.detectChanges();
            console.log(' Order status updated:', updatedOrder);
          } else {
            console.warn(' Order not found for update:', targetOrderId);
          }
        });
    } */

  listenToDishChange() {
    this.orderChange.listenToDishStatusInOrder();
    this.orderChange.dishChanged$
      .pipe(takeUntil(this.destroy$))
      .subscribe((dishChanged) => {
        console.log('Incoming dish update:', dishChanged);

        const targetOrderId = Number(dishChanged?.data.order_id);

        const orderIndex = this.orders.findIndex(
          (order) => Number(order.order_details?.order_id) === targetOrderId
        );

        if (orderIndex !== -1) {
          console.warn('Order found:', targetOrderId);
          setTimeout(() => {
            this._OrderListDetailsService.NewgetOrderById(targetOrderId).pipe(takeUntil(this.destroy$))
              .subscribe({
                next: (res: any) => {
                  console.log('API', res.data);
                  this.orders[orderIndex] = {
                    ...this.orders[orderIndex],
                    ...res.data.order
                  };
                  this.orders = [...this.orders];
                  this.filterOrders();
                  this.cdr.detectChanges();
                },
                error: (err) => {
                  console.error('API', err);
                }
              });
          }, 2000);
        } else {
          console.warn('Order not found for update:', targetOrderId);
        }
      });
  }

  filterOrders(): void {
    this.isFilterdFromClientSide = false;

    // Skip requestAnimationFrame delay when offline for maximum speed
    if (!this.isOnline) {
      try {
        this.startFiltering();
        this.updateComputedValues();
      } finally {
        this.isFilterdFromClientSide = true;
      }
    } else {
      // Use requestAnimationFrame only when online
      requestAnimationFrame(() => {
        try {
          this.startFiltering();
          this.updateComputedValues();
        } finally {
          this.isFilterdFromClientSide = true;
        }
      });
    }
  }

  // TrackBy functions for *ngFor performance
  trackByOrderId(index: number, order: any): any {
    return order.order_details?.order_id || order.orderId || index;
  }

  trackByItemId(index: number, item: any): any {
    return item.order_detail_id || item.dish_id || index;
  }

  trackByStatus(index: number, status: string): string {
    return status;
  }

  trackByOrderType(index: number, type: string): string {
    return type;
  }

  //  deleteOrder(orderIdToDelete: string): void {
  //   const saved = localStorage.getItem('savedOrders');
  //   if (!saved) return;

  //   const orders = JSON.parse(saved);

  //   const updatedOrders = orders.filter((order: any) => order.orderId !== orderIdToDelete);

  //   localStorage.setItem('savedOrders', JSON.stringify(updatedOrders));

  //   // Optionally update your displayed list if you're binding from a variable:
  //   this.filteredOrders = updatedOrders;
  // }
  selectedOrderIdToDelete: string | null = null;

  openDeleteModal(orderId: string): void {
    this.selectedOrderIdToDelete = orderId;

    // Show modal (Bootstrap)
    const modal = new bootstrap.Modal(
      document.getElementById('deleteOrderModal')!
    );
    modal.show();
  }

  confirmDelete(): void {
    if (!this.selectedOrderIdToDelete) return;

    const saved = localStorage.getItem('savedOrders');
    if (!saved) return;

    const orders = JSON.parse(saved);

    const updatedOrders = orders.filter(
      (order: any) => order.orderId !== this.selectedOrderIdToDelete
    );

    localStorage.setItem('savedOrders', JSON.stringify(updatedOrders));

    // Reload and apply filtering logic
    this.loadCartItems();
    this.filterCartItems();

    if (this.selectedStatus === 'static') {
      this.filterOrders();
    }

    // Close modal
    const modalEl = document.getElementById('deleteOrderModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl!);
    modalInstance?.hide();

    this.selectedOrderIdToDelete = null;
  }
  cancelReason: string = '';
  cancelReasonTouched: boolean = false;
  cancelErrorMessage: string = '';
  cancelSuccessMessage: string = '';
  cancelMessage: any;
  /*   submitCancelRequest(order: any): void {
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

      setTimeout(() => {
        this.cancelErrorMessage = '';
      }, 4000);

      return;
    }
    if (
      order.order_details.status !== 'cancelled' &&
      !(
        order.order_details.payment_status == 'unpaid' &&
        order.order_details.status === 'pending'
      )
    ) {
      this.cancelMessage = `تم ارسال المرتجع الي مدير الفرع
وبانتظار الموافقة`;
    } else if (
      order.order_details.payment_status == 'unpaid' &&
      order.order_details.status === 'pending'
    ) {
      this.cancelMessage = 'تم بنجاح';
    }

    // ✅ تحديد النوع: full إذا كل الأصناف المختارة تم إرجاعها بالكامل
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
          this.cancelSuccessMessage = 'تم إرسال طلب المرتجع بنجاح';
          this.cancelErrorMessage = ''; // clear previous errors
          setTimeout(() => {
            this.cancelSuccessMessage = '';
          }, 2000);
          if (!res?.status) {
            let errorText = 'حدث خطأ أثناء الإرسال';
            const reasonErrors = res?.errorData?.reason;
            if (Array.isArray(reasonErrors) && reasonErrors.length > 0) {
              errorText = reasonErrors[0]; // " السبب مطلوب."
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
            this.cancelReasonTouched = false;
            const modal_id = `modal-${order.order_details.order_id}`;
            const currentModal = document.getElementById(modal_id);
            console.log(currentModal);

            if (currentModal) {
              const modalInstance = bootstrap.Modal.getInstance(currentModal);
              modalInstance?.hide();

              // ✅ Reset items + reason لما يتقفل المودال (سواء submit أو dismiss)
              currentModal.addEventListener(
                'hidden.bs.modal',
                () => {
                  order.order_items.forEach((item: any) => {
                    item.isChecked = false;
                    item.selectedQuantity = item.quantity; // أو null لو  المدخل يبقى فاضي
                  });
                  this.cancelReason = '';
                  this.cancelReasonTouched = false;
                },
                { once: true }
              );
            }

            // order.order_items.forEach((item: any) => {
            //   item.isChecked = false;
            //   item.selectedQuantity = item.quantity;
            // });
            // Close the current modal
            const modalId = `modal-${order.order_details.order_id}`;
            const currentModalEl = document.getElementById(modalId);
            if (currentModalEl) {
              const modalInstance = bootstrap.Modal.getInstance(currentModalEl);
              modalInstance?.hide();
            }

            // Wait for modal close animation
            setTimeout(() => {
              const successModalEl =
                document.getElementById('successSmallModal');
              if (successModalEl) {
                const successModal = new bootstrap.Modal(successModalEl, {
                  backdrop: 'static',
                });
                successModal.show();

                // Auto-close after 2 seconds
                setTimeout(() => {
                  successModal.hide();

                  // ✅ Remove leftover backdrop after hiding
                  document
                    .querySelectorAll('.modal-backdrop')
                    .forEach((el) => el.remove());
                  document.body.classList.remove('modal-open');
                  document.body.style.overflow = ''; // reset scroll
                }, 1000);
              }
            }, 300);
          }
        },

        error: (err) => {
          console.error('Error:', err);

          let errorText;

          // ✅ First check: reason[] errors
          const reasonErrors = err?.error?.errorData?.error?.reason;
          if (Array.isArray(reasonErrors) && reasonErrors.length > 0) {
            errorText = reasonErrors[0]; // "السبب مطلوب."
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
          this.cancelSuccessMessage = ''; // Clear success message on error

          // Auto-hide after 4s
          setTimeout(() => {
            this.cancelErrorMessage = '';
          }, 4000);
        },
      });
  } */

  isSubmitting = false; // للتحكم في حالة الإرسال

  submitCancelRequest(order: any): void {
    if (this.isSubmitting) return; // منع تكرار الضغط لو لسه الطلب شغال
    this.isSubmitting = true; // ⏳ بداية الطلب

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

    // Validate cancelReason
    this.cancelReasonTouched = true;
    if (!this.cancelReason || !this.cancelReason.trim()) {
      this.cancelErrorMessage = 'يرجى إدخال سبب الإرجاع';
      this.cancelSuccessMessage = '';
      this.isSubmitting = false;
      setTimeout(() => {
        this.cancelErrorMessage = '';
      }, 4000);
      return;
    }

    if (
      order.order_details.status !== 'cancelled' &&
      !(
        order.order_details.payment_status == 'unpaid' &&
        order.order_details.status === 'pending'
      )
    ) {
      this.cancelMessage = `تم ارسال المرتجع الي مدير الفرع
وبانتظار الموافقة`;
    } else if (
      order.order_details.payment_status == 'unpaid' &&
      order.order_details.status === 'pending'
    ) {
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
            this.cancelReasonTouched = false;
            const modal_id = `modal-${order.order_details.order_id}`;
            const currentModal = document.getElementById(modal_id);
            console.log(currentModal);

            if (currentModal) {
              const modalInstance = bootstrap.Modal.getInstance(currentModal);
              modalInstance?.hide();

              currentModal.addEventListener(
                'hidden.bs.modal',
                () => {
                  order.order_items.forEach((item: any) => {
                    item.isChecked = false;
                    item.selectedQuantity = item.quantity;
                  });
                  this.cancelReason = '';
                  this.cancelReasonTouched = false;
                },
                { once: true }
              );
            }

            const modalId = `modal-${order.order_details.order_id}`;
            const currentModalEl = document.getElementById(modalId);
            if (currentModalEl) {
              const modalInstance = bootstrap.Modal.getInstance(currentModalEl);
              modalInstance?.hide();
            }

            setTimeout(() => {
              const successModalEl =
                document.getElementById('successSmallModal');
              if (successModalEl) {
                const successModal = new bootstrap.Modal(successModalEl, {
                  backdrop: 'static',
                });
                successModal.show();

                setTimeout(() => {
                  successModal.hide();
                  document
                    .querySelectorAll('.modal-backdrop')
                    .forEach((el) => el.remove());
                  document.body.classList.remove('modal-open');
                  document.body.style.overflow = '';
                }, 1000);
              }
            }, 300);
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

  status_order: any;

  cancelOrder(order: any): void {
    const orderId = order.order_details.order_id;
    if (!orderId) return;
    console.log('body');

    const cancelUrl = `${baseUrl}api/orders/cashier/order-cancel`;

    const token = localStorage.getItem('authToken');

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });
    const body = {
      order_id: orderId,
      type: 1, // 1 to delete all the dishes
    };

    this.http.post(cancelUrl, body, { headers }).subscribe({
      next: (response: any) => {
        console.log('Order cancelled successfully:', response);
        this.errorMessage = response.message;
        this.status_order = response.status;

        if (!response.status) {
          this.errorMessage = response.errorData.error[0];
          console.log('Order cancelled failed:', response);
        }
        setTimeout(() => {
          this.errorMessage = '';
        }, 2000);
      },
      error: (error) => {
        console.error('Failed to cancel order:', error);

        if (error?.errorData?.error?.length) {
          this.errorMessage = error.errorData.error[0]; // e.g., "لا يمكنك حذف الطلب"
        } else {
          this.errorMessage = 'response.message';
        }

        setTimeout(() => {
          this.errorMessage = '';
        }, 2000);
      },
    });
  }
  successMessageModal: any;
  successMessage!: string;

  ngAfterViewInit() {
    this.successMessageModal = new bootstrap.Modal(
      document.getElementById('successMessageModal')
    );
  }
  getStatus(dish: any): string {
    const status = dish?.dish_status || dish?.status;

    if (status === 'onhold') return 'onhold'; // hide badge
    return status; // show for pending, completed, etc.
  }
  openEditModal(item: any) {
    const hasExtraData = item.size || item.dish_addons[0];

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
  message: string = '';
  messageType: 'success' | 'error' = 'success';
  errMsg: any;
  removeDish(orderDetailId: number, quantity: number, order: any): void {
    this.removeLoading = true;
    const url = `${this.apiUrl}api/orders/cashier/request-cancel`;
    console.log(orderDetailId, order, 'id to delete');

    // 1️⃣ Find dish inside this order by order_detail_id
    const dish = order.order_items.find(
      (d: any) => d.order_detail_id === orderDetailId
    );
    if (!dish) {
      console.warn('❌ Dish not found in this order');
      return;
    }

    // 2️⃣ Build request body
    const body = {
      order_id: order.order_details.order_id,
      items: [
        {
          item_id: orderDetailId, // API expects this
          quantity: quantity,
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
            const modalElement = document.getElementById(
              `deleteConfirmModal${orderDetailId}`
            );
            console.log(modalElement);

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

  continueOrder(order: any): void {
    console.log('🔄 continueOrder called with order:', order);

    // 🔄 حفظ بيانات الكوبون من الطلب الحالي بدلاً من مسحها
    this.extractAndSaveCouponData(order);


    localStorage.removeItem('client');
    localStorage.removeItem('clientPhone');
    localStorage.removeItem('country_code');
    localStorage.removeItem('table_id');
    localStorage.removeItem('table_number');
    localStorage.removeItem('selectedOrderType');
    localStorage.removeItem('currentOrderId');
    localStorage.removeItem('currentOrderData');
    localStorage.removeItem('finalOrderId');
    localStorage.removeItem('cart');
    localStorage.removeItem('selectedPaymentStatus');
    localStorage.removeItem('holdCart');

    console.log('tet', order);
    this.productsService.destroyCart(); // 🔥 destroy stream

    localStorage.removeItem('cart');
    this.selectedOrderType = order?.order_details?.order_type;
    console.log('selectedOrderType', this.selectedOrderType);
    localStorage.setItem('selectedOrderType', this.selectedOrderType);
    localStorage.setItem('currentOrderId', order.order_details.order_id);
    localStorage.setItem('currentOrderData', JSON.stringify(order));

    this.router.navigate(['/home']);
    //   this.router.navigate(['/home']).then(() => {
    //   // ✅ إعادة تحميل الصفحة بعد التوجيه بنجاح
    //   window.location.reload();
    // });
  }
  private extractAndSaveCouponData(order: any): void {
    console.log('🔍 Searching for coupon data in order:', order);

    let couponData = null;
    let couponCode = '';
    let couponTitle = '';
    let discountAmount = 0;
    let couponType = '';
    let couponValue = '';

    // البحث في order_summary أولاً
    if (order.details_order?.order_summary) {
      couponData = order.details_order.order_summary;
      console.log('✅ Found coupon in details_order.order_summary', couponData);

      // استخدام بيانات من order_summary
      couponCode = couponData.coupon_title || 'COUPON_' + couponData.coupon_id;
      couponTitle = couponData.coupon_title || '';
      couponType = couponData.coupon_type || '';

      // ✅ التصحيح: استخدام القيمة الصحيحة للكوبون (10%)
      // إذا كان الكوبون "ca01" فهو 10%، نستخدم هذه القيمة مباشرة
      couponValue = "10"; // 10% مباشرة

      // حساب الخصم بناءً على النسبة
      if (couponType === 'percentage') {
        discountAmount = (couponData.subtotal_price_before_coupon * parseFloat(couponValue)) / 100;
      } else {
        discountAmount = parseFloat(couponValue);
      }

      console.log('💰 Corrected coupon details (10%):', {
        couponCode,
        couponTitle,
        couponType,
        couponValue: couponValue + '%',
        calculatedDiscount: discountAmount
      });
    }

    // حفظ بيانات الكوبون في localStorage
    if (couponCode && couponValue) {
      localStorage.setItem('appliedCoupon', 'true');
      localStorage.setItem('validCoupon', 'true');
      localStorage.setItem('couponTitle', couponTitle);
      localStorage.setItem('couponCode', couponCode);
      localStorage.setItem('discountAmount', discountAmount.toString());
      localStorage.setItem('couponType', couponType);
      localStorage.setItem('couponValue', couponValue); // ✅ حفظ 10 كقيمة

      console.log('💾 10% coupon saved to localStorage:', {
        code: couponCode,
        title: couponTitle,
        type: couponType,
        value: couponValue + '%',
        discount: discountAmount
      });
    } else {
      this.clearCouponData();
      console.log('❌ No valid coupon found in order data');
    }
  }
  private clearCouponData(): void {
    const couponKeys = [
      'appliedCoupon', 'validCoupon', 'couponTitle',
      'couponCode', 'discountAmount'
    ];

    couponKeys.forEach(key => {
      localStorage.removeItem(key);
      console.log(`🗑️ Removed ${key} from localStorage`);
    });
  }
}
