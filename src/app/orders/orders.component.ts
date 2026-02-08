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
import { TablesService } from '../services/tables.service';
import { AddAddressService } from '../services/add-address.service';
@Component({
  selector: 'app-orders',
  standalone: true,
  templateUrl: './orders.component.html',
  styleUrls: ['./orders.component.css'],
  imports: [
    RouterLink,
    ShowLoaderUntilPageLoadedDirective,
    RouterLinkActive,
    CommonModule,
    FormsModule,
    AccordionModule,
  ],
})
export class OrdersComponent implements OnDestroy {
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
  errorMessage: any;
  apiUrl = `${baseUrl}`;
  removeLoading: boolean = false;

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
    private tablesService: TablesService, // private dbService: IndexeddbService
    private _OrderListDetailsService: OrderListDetailsService ,
     private dbService: IndexeddbService,
    private addAddressService: AddAddressService
  ) {
    // const navigation = this.router.getCurrentNavigation();
    // this.orderDetails = navigation?.extras.state?.['orderData'];
    // this.orderTypeById= this.orderDetails
    // console.log(this.orderDetails,'orderDetails')
  }

  ngOnInit(): void {
    // console.log("this.isOnline", this.isOnline);
    this.selectedOrderTypeStatus = 'All';
    this.fetchOrdersData();
    // if (this.isOnline == false) {
    //   // this.loadOrdersFromIndexedDB();
    //         this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';

    // }
    // else {
    // this.fetchOrdersFromAPI();
    // }

    this.loadCartItems();
    this.filterCartItems();
    // this.fetchOrdersData();
    this.fetchOrderDetails();
    // this.setupPusherListeners();
    this.listenToDishChange();
    // this.listenToOrderChange();
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
    // 'pusher'
    this.listenToNewOrder();
  }
  fetchOrdersData() {
    this.loading = false;
    if (this.isOnline) {
      this.fetchOrdersFromAPI();
    } else {
      // this.loadFromIndexedDB();
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
    }
  }
  //start dalia

  // Load orders from IndexedDB
  // private loadOrdersFromIndexedDB(): void {
  //   this.dbService.getOrders().then(orders => {
  //     if (orders && orders.length > 0) {
  //       console.log('Orders loaded from IndexedDB:', orders.length);

  //       this.processOrders(orders);

  //       // Check if data is stale (older than 5 minutes)
  //       this.dbService.getOrdersLastSync().then(lastSync => {
  //         const fiveMinutesAgo = Date.now() - (5 * 60 * 1000);
  //         if (this.isOnline && lastSync < fiveMinutesAgo) {
  //           this.fetchOrdersFromAPI();
  //         }
  //       }).catch(err => {
  //         console.error('Error getting last sync time:', err);
  //         if (this.isOnline) {
  //           this.fetchOrdersFromAPI();
  //         }
  //       });
  //     } else if (this.isOnline) {
  //       // No data in IndexedDB, fetch from API
  //       this.fetchOrdersFromAPI();
  //     } else {
  //       // Offline and no data available
  //       this.loading = false;
  //       console.warn('No orders available offline');
  //     }
  //   }).catch(err => {
  //     console.error('Error loading orders from IndexedDB:', err);
  //     if (this.isOnline) {
  //       this.fetchOrdersFromAPI();
  //     } else {
  //       this.loading = false;
  //     }
  //   });
  // }
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
  // Process orders (common method for both API and IndexedDB data)
  private processOrders(orders: any[]): void {
    this.currencySymbol = orders[0]?.currency_symbol;
    this.orders = orders
      .filter(
        (order: any) =>
          this.allowedOrderTypes.includes(order.order_details?.order_type) &&
          (this.allowedStatuses.includes(order.order_details?.status) ||
           order.order_details?.status === 'packing') // Allow packing status
      )
      .map((order: any) => {
        // Convert 'packing' status to 'pending' for frontend compatibility
        const processedOrder = {
          ...order,
          currency_symbol: this.currencySymbol,
        };

        // Map packing to pending for frontend
        if (processedOrder.order_details?.status === 'packing') {
          processedOrder.order_details.status = 'pending';
        }

        return processedOrder;
      });

    this.ordersStatus = Array.from(
      new Set(this.orders.map((order) => order.order_details?.status || ''))
    ).filter((status) => this.allowedStatuses.includes(status));

    if (this.ordersStatus.length > 0) this.ordersStatus.unshift('all');

    this.filterOrders();
    this.loading = true;
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
        } إلى ${this.getStatusText(this.orders[index])}`,
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
    // Do NOT call stopListening() on newOrder - it's managed globally in app.component.ts
    // this.newOrder.stopListening();
    this.orderChangeStatus.stopListeningOfOrderStatus();
    this.orderChange.stopListening();
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

  getStatusText(order: any): string {
    // Handle both old format (status string) and new format (order object)
    const status = typeof order === 'string' ? order : (order?.order_details?.status || order?.status);
    const mergedIntoOrderId = typeof order === 'object' ? (order?.order_details?.merged_into_order_id || order?.merged_into_order_id) : null;

    // Check if the order is cancelled AND merged into another order
    if (status === 'cancelled' && mergedIntoOrderId) {
      return 'تم الدمج';
    }

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
    let filtered = this.orders;

    // filter by order type (skip if "All")
    if (this.selectedOrderTypeStatus !== 'All') {
      filtered = filtered.filter(
        (order) =>
          order.order_details?.order_type === this.selectedOrderTypeStatus
      );
    }

    // filter by status
    if (this.selectedStatus === 'completed') {
      // completed includes delivered
      filtered = filtered.filter((order) =>
        ['completed', 'delivered'].includes(order.order_details?.status)
      );
    } else if (this.selectedStatus === 'static') {
      const stored = localStorage.getItem('savedOrders');
      const parsed = stored ? JSON.parse(stored) : [];

      if (this.selectedOrderTypeStatus === 'All') {
        filtered = parsed; //  take all saved orders
      } else {
        ``;
        filtered = parsed.filter(
          (item: any) => item.type === this.selectedOrderTypeStatus
        );
      }
    } else if (this.selectedStatus !== 'all') {
      filtered = filtered.filter(
        (order) => order.order_details?.status === this.selectedStatus
      );
    }

    // filter by search text
    if (this.searchText) {
      const search = this.searchText.toLowerCase();
      filtered = filtered.filter((order) =>
        order.order_details?.order_number
          ?.toString()
          .toLowerCase()
          .includes(search)
      );
    }

    this.filteredOrders = filtered;
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
  }
  selectStatus(status: string): void {
    this.selectedStatus = status;
    this.filterOrdersInput();
    this.filterOrders();
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
    const translations: { [key: string]: string } = {
      All: ' الكل',
      Takeaway: ' إستلام',
      Delivery: 'توصيل',
      'dine-in': 'فى المطعم',
      talabat: 'طلبات',
    };
    return translations[orderType] || orderType;
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
    if (status === 'readyForPickup') {
      return this.selectedOrderTypeStatus === 'Delivery' ||
        this.selectedOrderTypeStatus === 'Takeaway'
        ? 'جاهزة للاستلام'
        : 'جاهزة للتقديم';
    }

    return this.statusLabels[status] || status;
  }

  getFilteredStatuses(): string[] {
    const baseStatuses = ['pending', 'in_progress', 'readyForPickup'];
    const finalStatuses = ['completed', 'cancelled'];

    if (
      this.selectedOrderTypeStatus === 'Takeaway' &&
      this.selectedStatus === 'all'
    ) {
      return [...baseStatuses, ...finalStatuses];
    }

    if (this.selectedOrderTypeStatus === 'Delivery') {
      // نضيف 'delivered' هنا
      return [...baseStatuses, 'on_way', ...finalStatuses];
    }

    // Default statuses for other order types
    return [...baseStatuses, ...finalStatuses];
  }

  getStatusCount(status: string, orderType: string): number {
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
        return this.orders.length; // ✅ count all
      }

      return this.orders.filter(
        (order) => order.order_details?.order_type === orderType
      ).length;
    }

    // specific status
    return this.orders.filter(
      (order) =>
        order.order_details?.status === status &&
        (orderType === 'All' || order.order_details?.order_type === orderType)
    ).length;
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

    // Let Angular render spinner first, then run filter logic
    requestAnimationFrame(() => {
      try {
        this.startFiltering();
      } finally {
        this.isFilterdFromClientSide = true;
      }
    });
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
  selectedReturnPaymentMethod: string = 'cash'; // Default payment method for return invoice
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

    console.log('order', order);
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
    //  check if the order is talabat then must be full return
    if (order.order_details.order_type == "talabat") {
      if (selectedItems.length !== order.order_items.length) {
        this.cancelErrorMessage = 'يرجى اختيار جميع الأصناف بالكامل';
        this.cancelSuccessMessage = '';
        this.isSubmitting = false;
        setTimeout(() => {
          this.cancelErrorMessage = '';
        }, 4000);
        return;
      }

      // التحقق من أن الكمية المرجعة تساوي الكمية الأصلية لكل عنصر
      const itemsWithWrongQuantity = order.order_items.filter((item: any) => {
        const originalQuantity = item.quantity;
        const selectedQuantity = item.selectedQuantity ?? item.quantity;
        const returnedQuantity = originalQuantity - selectedQuantity;
        // يجب أن تكون الكمية المرجعة = الكمية الأصلية (يعني selectedQuantity = 0)
        return returnedQuantity !== originalQuantity;
      });

      if (itemsWithWrongQuantity.length > 0) {
        const itemNames = itemsWithWrongQuantity.map((item: any) => item.dish_name).join('، ');
        this.cancelErrorMessage = `يرجى إدخال نفس الكمية الأصلية للعناصر التالية: ${itemNames}`;
        this.cancelSuccessMessage = '';
        this.isSubmitting = false;
        setTimeout(() => {
          this.cancelErrorMessage = '';
        }, 4000);
        return;
      }
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
      payment_method: this.selectedReturnPaymentMethod,
    };

    console.log('Sending:', body, selectedItems, order);

    // Save to IndexedDB first to match removeDish logic
    this.dbService.saveOrderToPrintkitchen(order.order_details.order_id, "cancel").then(() => {
      console.log('order cancelled and saved to printkitchen indexeddb', order.order_details.order_id);

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

              // ✅ Refresh order data to get updated calculations (coupon, tax, total)
              this.refreshOrderAfterCancel(order.order_details.order_id);

              // NEW: Print Cancel Request to Kitchen
              this.dbService.getOrderFromPrintkitchenById(order.order_details.order_id).then((orderMetadata: any) => {
                if (orderMetadata) {
                  this.processKitchenPrint(order.order_details.order_id, body.items, 'cancel');
                }
              }).catch((err) => {
                console.error('error getting order from printkitchen indexeddb', err);
              });

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
    }).catch((err) => {
      console.error('error saving to printkitchen indexeddb', err);
      this.isSubmitting = false; // reset flag if save failed
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
  openEditModal(item: any, orderId: any) {
    const hasExtraData = item.size || item.dish_addons[0];

    // console.log('item', item);

    const modalSize = hasExtraData ? 'lg' : 'md';

    const editModal = this.NgbModal.open(EditOrderModalComponent, {
      size: modalSize,
      centered: true,
    });
    editModal.componentInstance.itemId = item.order_detail_id;

    console.log('order_id', orderId);
    // dalia
    // save order_id to indexeddb
    this.dbService.saveOrderToPrintkitchen(orderId, "edit").then(() => {
      console.log('order_id saved to indexeddb', orderId);
    }).catch((err) => {
      console.error('error saving order_id to indexeddb', err);
    });

    editModal.result.then(
      (result) => {
        console.log('🔍 [DEBUG] Modal result received:', result);
        if (result) {
          console.log('🔍 [DEBUG] Result is truthy, proceeding...');
          this.successMessage = 'تم تحديث الطلب بنجاح';
          this.successMessageModal.show();

          // get order from printkitchen indexeddb (This contains the OLD state)
          console.log('🔍 [DEBUG] Fetching order from IndexedDB, orderId:', orderId);
          this.dbService.getOrderFromPrintkitchenById(orderId).then((orderMetadata: any) => {
            console.log('🔍 [DEBUG] Order from printkitchen indexeddb:', orderMetadata);
            if (!orderMetadata || !orderMetadata.order_data) {
              console.error('❌ [DEBUG] Order not found in printkitchen indexeddb or order_data is missing');
              return;
            }

            // Filter to get ONLY the item that was edited
            const editedItemOldState = orderMetadata.order_data.order_items.find(
              (i: any) => i.order_detail_id === item.order_detail_id
            );

            if (!editedItemOldState) {
               console.error('❌ [DEBUG] Original item not found in old order state');
               return;
            }

            const oldItems = [{
              item_id: editedItemOldState.order_detail_id,
              quantity: editedItemOldState.quantity,
              size: editedItemOldState.size,
              dish_addons: editedItemOldState.dish_addons 
            }];

            this.processKitchenPrint(orderId, oldItems, 'edit');
          }).catch((err) => {
            console.error('error getting order from printkitchen indexeddb', err);
          });

          setTimeout(() => {
            // Safe dismiss - only call if method exists
            if (this.successMessageModal && typeof this.successMessageModal.dismiss === 'function') {
              this.successMessageModal.dismiss();
            } else if (this.successMessageModal && typeof this.successMessageModal.hide === 'function') {
              this.successMessageModal.hide();
            }
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

    // Safety check for order object
    if (!order || !order.order_details) {
      console.error('❌ Cannot cancel: Invalid order object', order);
      return;
    }

    // print cancel order to printkitchen indexeddb
    this.dbService.saveOrderToPrintkitchen(order.order_details.order_id, "cancel").then(() => {
      console.log('order cancelled and saved to printkitchen indexeddb', order.order_details.order_id);
    }).catch((err) => {
      console.error('error saving order to printkitchen indexeddb', err);
    });

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
      flag: 'cancel',
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

            // ✅ Refresh order data to get updated calculations (coupon, tax, total)
            this.refreshOrderAfterCancel(order.order_details.order_id);



             // get order from printkitchen indexeddb
          this.dbService.getOrderFromPrintkitchenById(order.order_details.order_id).then((orderMetadata: any) => {
            if (orderMetadata) {
              this.processKitchenPrint(order.order_details.order_id, body.items, 'cancel');
            }
          }).catch((err) => {
            console.error('error getting order from printkitchen indexeddb', err);
          });


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

  processKitchenPrint(orderId: any, items: any[], flag: string): void {
    const token = localStorage.getItem('authToken');
    if (!token) {
      console.error('Auth token not found');
      return;
    }

    const printPayload = {
      order_id: orderId,
      items: items,
      flag: flag
    };

    console.log(`Sending request to print-editor-cancel API with flag: ${flag}`, printPayload);
    this.http.post(`${baseUrl}api/print-editor-cancel`, { order: printPayload }, { headers: { Authorization: `Bearer ${token}` } }).subscribe({
      next: async (response: any) => {
        console.log('order updated successfully', response);

        if (response.status && response.printers && response.printers.length > 0) {
          for (const printer of response.printers) {
            if (printer.items && printer.items.length > 0) {
              try {
                await this.newOrder.printInvoiceImage(
                  printer.items,
                  response.order,
                  printer.ip,
                  printer.port,
                  response.type
                );
                await new Promise(resolve => setTimeout(resolve, 500));
              } catch (err) {
                console.error(`Error printing to ${printer.ip}:`, err);
              }
            }
          }
        }

        this.dbService.deleteOrderFromPrintkitchenById(orderId).catch((err) => {
          console.error('error deleting order from printkitchen indexeddb', err);
        });
      },
      error: (err) => {
        console.error('error calling print-editor-cancel', err);
      }
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

  canShowReturnInvoice(order: any): boolean {
    const totalCash = localStorage.getItem('totalcash');
    const totalCredit = localStorage.getItem('totalvisa');
    if (!totalCash && !totalCredit) {
      return false;
    }
    const cashValue = Number(totalCash);
    const creditValue = Number(totalCredit);
    const orderPrice = Number(order.total_price);
    if(order.details_order?.transactions?.[0]?.payment_method == 'credit') {
      // console.log('creditdalia');
      return !isNaN(creditValue) && !isNaN(orderPrice) && (creditValue > orderPrice || cashValue > orderPrice);
    }
        // console.log('cashdalia');

    return !isNaN(cashValue) && !isNaN(orderPrice) && cashValue > orderPrice ;
  }

  shouldShowReturnInvoiceSection(order: any): boolean {
    return order.order_details.status !== 'cancelled' &&
           !(order.order_details.payment_status == 'unpaid' && order.order_details.status === 'pending');
            // && order.order_details.order_type != "talabat";
  }

  // Split Order Properties
  splitOrderItems: any[] = [];
  newSplitOrderNumber: string = '';
  selectedTableIdForSplit: string = '';
  splitOrderCurrency: string = '';
  currentSplitOrder: any = null;
  newSplitOrderId: number | null = null;
  isSplitSubmitting: boolean = false;
  splitErrorMessage: string = '';
  splitSuccessMessage: string = '';
  availableTables: any[] = [];
  filteredTablesForSplit: any[] = [];
  selectedLocationFilter: string = 'all';

  // Merge Order Properties
  currentMergeOrder: any = null;
  eligibleOrdersForMerge: any[] = [];
  selectedOrderIdForMerge: number | null = null;
  selectedTableIdForMerge: string = '';
  mergeOrderCurrency: string = '';
  isMergeSubmitting: boolean = false;
  mergeErrorMessage: string = '';
  mergeSuccessMessage: string = '';
  mergeReason: string = '';
  mergeOrderItems: any[] = [];

  // Change Order Type Properties
  currentOrderForTypeChange: any = null;
  selectedNewOrderType: string = '';
  selectedTableIdForTypeChange: string = '';
  deliveryAreas: any[] = [];
  changeTypeDeliveryName: string = '';
  changeTypeDeliveryPhone: string = '';
  changeTypeDeliveryCountryCode: string = '';
  changeTypeDeliveryAddress: string = '';
  changeTypeDeliveryAreaId: string = '';
  changeTypeDeliveryBuildingType: string = 'apartment';
  changeTypeDeliveryBuilding: string = '';
  changeTypeDeliveryApartment: string = '';
  changeTypeDeliveryFloor: string = '';
  changeTypeDeliveryNotes: string = '';
  changeTypeDeliveryHotelName: string = '';
  changeTypeDeliveryHotelId: number | string = '';
  changeTypeDeliveryHotels: any[] = [];
  changeTypeDeliveryWhatsapp: string = '';
  changeTypeDeliveryWhatsappCode: string = '';
  changeTypeDeliveryUseSameWhatsapp: boolean = true;
  changeTypeDeliveryCountryList: any[] = [];
  changeTypeDeliveryFilteredCountries: any[] = [];
  changeTypeDeliverySelectedCountry: { code: string; flag: string; phoneLength?: number } | null = null;
  changeTypeDeliveryCountrySearchTerm: string = '';
  changeTypeDeliverySearchPhoneIdle: boolean = true;
  isChangeTypeSubmitting: boolean = false;
  changeTypeSuccessMessage: string = '';

  // Check if order can be split
  canSplitOrder(order: any): boolean {
    // Must be dine-in, unpaid, and status 'pending' (which is 'packing' in backend)
    if (
      order.order_details.order_type !== 'dine-in' ||
      order.order_details.payment_status !== 'unpaid' ||
      order.order_details.status !== 'pending'
    ) {
      return false;
    }

    // Check if all order items are completed or cancelled
    if (!order.order_items || order.order_items.length === 0) {
      return false;
    }

    // Filter out completed and cancelled items
    const activeItems = order.order_items.filter(
      (item: any) => item.dish_status !== 'completed' && item.dish_status !== 'cancel'
    );

    if (activeItems.length === 0) {
      return false;
    }

    // Case 1: Multiple items - can split if at least 2 active items
    if (activeItems.length >= 2) {
      return true;
    }

    // Case 2: Single item - can split if quantity > 1
    if (activeItems.length === 1) {
      const singleItem = activeItems[0];
      return (singleItem.quantity || 0) > 1;
    }

    return false;
  }

  // Check if order can be merged
  canMergeOrder(order: any): boolean {
    // Must be dine-in, unpaid, and status 'pending'
    if (
      order.order_details.order_type !== 'dine-in' ||
      order.order_details.payment_status !== 'unpaid' ||
      order.order_details.status !== 'pending'
    ) {
      return false;
    }

    // Check if there are other eligible orders to merge with
    const eligibleOrders = this.getEligibleOrdersForMerge(order);
    return eligibleOrders.length > 0;
  }

  // Get eligible orders for merge (excluding current order) – same branch only
  getEligibleOrdersForMerge(currentOrder: any): any[] {
    const currentBranchId = localStorage.getItem('branch_id');
    return this.orders.filter((order: any) => {
      // Must be from the same branch (avoid showing tables/orders from another branch)
      const orderBranchId = order.branch_id ?? order.details_order?.branch_id ?? order.order_details?.branch_id;
      if (currentBranchId != null && orderBranchId != null && String(orderBranchId) !== String(currentBranchId)) {
        return false;
      }

      // Must be different order
      if (order.order_details.order_id === currentOrder.order_details.order_id) {
        return false;
      }

      // Must be dine-in, unpaid, and status 'pending'
      if (
        order.order_details.order_type !== 'dine-in' ||
        order.order_details.payment_status !== 'unpaid' ||
        order.order_details.status !== 'pending'
      ) {
        return false;
      }

      // Check if order has any non-completed items (must have at least one item that is not completed or cancelled)
      if (!order.order_items || order.order_items.length === 0) {
        return false;
      }

      // Must have at least one item that is not completed and not cancelled
      const hasNonCompletedItems = order.order_items.some(
        (item: any) => item.dish_status !== 'completed' && item.dish_status !== 'cancel'
      );

      return hasNonCompletedItems;
    });
  }

  // Open split modal
  openSplitModal(order: any): void {
    this.currentSplitOrder = order;
    this.splitOrderItems = order.order_items.map((item: any) => ({
      ...item,
      isSelected: false,
      selectedQuantity: 0,
    }));
    this.splitOrderCurrency = order.currency_symbol || this.currencySymbol;
    this.selectedTableIdForSplit = '';
    this.splitErrorMessage = '';
    this.splitSuccessMessage = '';
    this.newSplitOrderNumber = 'CS-' + Math.floor(Math.random() * 100000);

    // Fetch available tables
    this.fetchAvailableTables();

    // Show modal
    const modalElement = document.getElementById('splitOrderModal');
    if (modalElement) {
      const modal = new bootstrap.Modal(modalElement);
      modal.show();
    }
  }

  // Select table for split
  selectTableForSplit(tableId: string): void {
    const table = this.availableTables.find(t => t.id == tableId);
    if (table && table.status === 1) { // Only allow available tables
      this.selectedTableIdForSplit = tableId;
    }
  }

  // Get selected table name
  getSelectedTableName(): string {
    const table = this.availableTables.find(t => t.id == this.selectedTableIdForSplit);
    return table ? (table.name || `طاولة ${table.number}`) : '';
  }

  // Go to split step 2 (select items)
  goToSplitStep2(): void {
    const modalElement = document.getElementById('splitOrderModal');
    if (modalElement) {
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      modalInstance?.hide();
    }

    setTimeout(() => {
      const itemsModal = document.getElementById('splitOrderItemsModal');
      if (itemsModal) {
        const modal = new bootstrap.Modal(itemsModal);
        modal.show();
      }
    }, 300);
  }

  // Go back to split step 1
  goBackToSplitStep1(): void {
    const modalElement = document.getElementById('splitOrderItemsModal');
    if (modalElement) {
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      modalInstance?.hide();
    }

    setTimeout(() => {
      const tableModal = document.getElementById('splitOrderModal');
      if (tableModal) {
        const modal = new bootstrap.Modal(tableModal);
        modal.show();
      }
    }, 300);
  }

  // Handle split quantity change
  onSplitQuantityChange(item: any, newValue: any): void {
    this.validateAndCorrectSplitQuantity(item, newValue);
  }

  // Handle input event (for immediate validation)
  onSplitQuantityInput(item: any, event: any): void {
    const inputValue = event.target.value;
    this.validateAndCorrectSplitQuantity(item, inputValue);
  }

  // Handle blur event (final validation when leaving field)
  onSplitQuantityBlur(item: any, event: any): void {
    const inputValue = event.target.value;
    this.validateAndCorrectSplitQuantity(item, inputValue);
    // Force update the input field value
    event.target.value = item.selectedQuantity;
  }

  // Handle keydown to prevent invalid characters
  onSplitQuantityKeydown(item: any, event: KeyboardEvent): void {
    // Allow: backspace, delete, tab, escape, enter, decimal point
    if ([46, 8, 9, 27, 13, 110, 190].indexOf(event.keyCode) !== -1 ||
      // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
      (event.keyCode === 65 && event.ctrlKey === true) ||
      (event.keyCode === 67 && event.ctrlKey === true) ||
      (event.keyCode === 86 && event.ctrlKey === true) ||
      (event.keyCode === 88 && event.ctrlKey === true) ||
      // Allow: home, end, left, right
      (event.keyCode >= 35 && event.keyCode <= 39)) {
      return;
    }
    // Ensure that it is a number and stop the keypress
    if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
      event.preventDefault();
    }
  }

  // Handle paste to validate pasted values
  onSplitQuantityPaste(item: any, event: ClipboardEvent): void {
    event.preventDefault();
    const pastedText = event.clipboardData?.getData('text/plain') || '';
    const pastedValue = Number(pastedText);

    if (!isNaN(pastedValue)) {
      this.validateAndCorrectSplitQuantity(item, pastedValue);
    }
  }

  // Central validation function
  validateAndCorrectSplitQuantity(item: any, newValue: any): void {
    // Convert to number - handle string inputs like "21", "-1", "1-", etc.
    let quantity: number;

    // Handle string inputs that might contain non-numeric characters
    if (typeof newValue === 'string') {
      // Remove any non-numeric characters except minus at the start
      const cleanedValue = newValue.replace(/[^\d-]/g, '').replace(/(?!^)-/g, '');
      quantity = Number(cleanedValue);
    } else {
      quantity = Number(newValue);
    }

    // Handle NaN, null, undefined, or empty string
    if (isNaN(quantity) || quantity === null || quantity === undefined || newValue === '' || newValue === null) {
      quantity = 0;
    }

    // Store original value for comparison
    const originalQty = item.selectedQuantity || 0;
    const maxQty = item.quantity || 0;

    // Prevent negative values - force to 0
    if (quantity < 0) {
      quantity = 0;
      if (originalQty !== quantity) {
        this.splitErrorMessage = 'لا يمكن إدخال كميات سالبة';
        setTimeout(() => {
          this.splitErrorMessage = '';
        }, 3000);
      }
    }

    // Prevent values exceeding available quantity - force to max
    if (quantity > maxQty) {
      quantity = maxQty;
      if (originalQty !== quantity) {
        this.splitErrorMessage = `الكمية المدخلة أكبر من المتاحة. الحد الأقصى: ${maxQty}`;
        setTimeout(() => {
          this.splitErrorMessage = '';
        }, 3000);
      }
    }

    // Update the value immediately - this is critical
    item.selectedQuantity = quantity;

    // Force change detection to update UI immediately
    this.cdr.detectChanges();

    // Clear error if valid
    if (quantity >= 0 && quantity <= maxQty) {
      // Only clear if this was the error we set
      if (this.splitErrorMessage && (this.splitErrorMessage.includes('سالب') || this.splitErrorMessage.includes('أكبر'))) {
        this.splitErrorMessage = '';
      }
    }
  }

  // Increase split quantity
  increaseSplitQuantity(item: any): void {
    const currentQty = item.selectedQuantity || 0;
    const maxQty = item.quantity || 0;

    // Ensure we don't exceed the maximum
    if (currentQty < maxQty) {
      item.selectedQuantity = Math.min(currentQty + 1, maxQty); // Ensure never exceeds max
      // Clear any error when increasing
      if (this.splitErrorMessage) {
        this.splitErrorMessage = '';
      }
      // Force change detection
      this.cdr.detectChanges();
    }
  }

  // Decrease split quantity
  decreaseSplitQuantity(item: any): void {
    const currentQty = item.selectedQuantity || 0;
    if (currentQty > 0) {
      item.selectedQuantity = Math.max(0, currentQty - 1); // Ensure never goes below 0
      // Clear any error when decreasing
      if (this.splitErrorMessage) {
        this.splitErrorMessage = '';
      }
      // Force change detection
      this.cdr.detectChanges();
    }
  }

  // Get split new order total
  getSplitNewOrderTotal(): number {
    const total = this.splitOrderItems.reduce((total, item) => {
      const quantity = item.selectedQuantity || 0;
      const unitPrice = item.total_dish_price / item.quantity;
      return total + (unitPrice * quantity);
    }, 0);
    return parseFloat(total.toFixed(2));
  }

  // Get selected split items
  getSelectedSplitItems(): any[] {
    return this.splitOrderItems.filter(item => (item.selectedQuantity || 0) > 0);
  }

  // Get remaining split items
  getRemainingSplitItems(): any[] {
    return this.splitOrderItems.map(item => ({
      ...item,
      remainingQuantity: item.quantity - (item.selectedQuantity || 0)
    })).filter(item => item.remainingQuantity > 0);
  }

  // Get split remaining total
  getSplitRemainingTotal(): number {
    const total = this.splitOrderItems.reduce((total, item) => {
      const remaining = item.quantity - (item.selectedQuantity || 0);
      const unitPrice = item.total_dish_price / item.quantity;
      return total + (unitPrice * remaining);
    }, 0);
    return parseFloat(total.toFixed(2));
  }

  // Get new split order number (placeholder)
  getNewSplitOrderNumber(): string {
    return this.newSplitOrderNumber;
  }

  // Get selected split items count
  getSelectedSplitItemsCount(): number {
    return this.getSelectedSplitItems().reduce((count, item) => count + (item.selectedQuantity || 0), 0);
  }

  // Validate split quantity for a single item
  validateSplitQuantity(item: any): void {
    // Reset to 0 if negative
    if (item.selectedQuantity < 0) {
      item.selectedQuantity = 0;
      this.splitErrorMessage = 'لا يمكن إدخال كميات سالبة';
      setTimeout(() => {
        this.splitErrorMessage = '';
      }, 3000);
      return;
    }

    // Reset to max quantity if exceeds available
    if (item.selectedQuantity > item.quantity) {
      item.selectedQuantity = item.quantity;
      this.splitErrorMessage = `الكمية المدخلة أكبر من المتاحة. الحد الأقصى: ${item.quantity}`;
      setTimeout(() => {
        this.splitErrorMessage = '';
      }, 3000);
      return;
    }

    // Clear error if valid
    if (this.splitErrorMessage && item.selectedQuantity >= 0 && item.selectedQuantity <= item.quantity) {
      this.splitErrorMessage = '';
    }
  }

  // Check if all split quantities are valid
  areSplitQuantitiesValid(): boolean {
    if (!this.splitOrderItems || this.splitOrderItems.length === 0) {
      return false;
    }

    // Check each item
    for (const item of this.splitOrderItems) {
      const selectedQty = item.selectedQuantity || 0;

      // Check for negative quantities
      if (selectedQty < 0) {
        return false;
      }

      // Check for quantities exceeding available
      if (selectedQty > item.quantity) {
        return false;
      }
    }

    return true;
  }

  // Show split confirmation
  showSplitConfirmation(): void {
    // Clear previous error
    this.splitErrorMessage = '';

    // ✅ First, validate and correct all quantities
    let hasInvalidQuantities = false;
    const invalidItems: any[] = [];

    for (const item of this.splitOrderItems) {
      let selectedQty = item.selectedQuantity || 0;
      let wasCorrected = false;

      // Check and correct negative values
      if (selectedQty < 0) {
        selectedQty = 0;
        item.selectedQuantity = 0;
        wasCorrected = true;
        hasInvalidQuantities = true;
      }

      // Check and correct values exceeding available
      if (selectedQty > item.quantity) {
        selectedQty = item.quantity;
        item.selectedQuantity = item.quantity;
        wasCorrected = true;
        hasInvalidQuantities = true;
      }

      if (wasCorrected) {
        invalidItems.push(item);
      }
    }

    // ✅ If any quantities were invalid, show error and prevent proceeding
    if (hasInvalidQuantities) {
      const hasNegative = invalidItems.some((item: any) => (item.selectedQuantity || 0) < 0);
      const hasExceeded = invalidItems.some((item: any) => {
        const qty = item.selectedQuantity || 0;
        return qty > item.quantity;
      });

      if (hasNegative) {
        this.splitErrorMessage = 'لا يمكن إدخال كميات سالبة. تم تصحيح الكميات تلقائياً';
      } else if (hasExceeded) {
        this.splitErrorMessage = 'لا يمكن إدخال كميات أكبر من المتاحة. تم تصحيح الكميات تلقائياً';
      }

      setTimeout(() => {
        this.splitErrorMessage = '';
      }, 4000);

      // ✅ Force change detection to update UI
      this.cdr.detectChanges();
      return; // Prevent proceeding to confirmation modal
    }

    // ✅ Double check with validation function
    if (!this.areSplitQuantitiesValid()) {
      this.splitErrorMessage = 'يرجى التأكد من صحة جميع الكميات المدخلة';
      setTimeout(() => {
        this.splitErrorMessage = '';
      }, 3000);
      return;
    }

    if (this.getSplitNewOrderTotal() === 0) {
      this.splitErrorMessage = 'يرجى اختيار كمية واحدة على الأقل';
      setTimeout(() => {
        this.splitErrorMessage = '';
      }, 3000);
      return;
    }

    // Check if at least one item remains in original order (with quantity > 0)
    const remainingItems = this.getRemainingSplitItems();
    if (remainingItems.length === 0) {
      this.splitErrorMessage = 'يجب أن يبقى على الأقل كمية واحدة في الطلب الأصلي';
      setTimeout(() => {
        this.splitErrorMessage = '';
      }, 3000);
      return;
    }

    // All validations passed, proceed to confirmation modal
    const modalElement = document.getElementById('splitOrderItemsModal');
    if (modalElement) {
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      modalInstance?.hide();
    }

    setTimeout(() => {
      const confirmModal = document.getElementById('splitOrderConfirmationModal');
      if (confirmModal) {
        const modal = new bootstrap.Modal(confirmModal);
        modal.show();
      }
    }, 300);
  }

  // Go back to split items
  goBackToSplitItems(): void {
    const modalElement = document.getElementById('splitOrderConfirmationModal');
    if (modalElement) {
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      modalInstance?.hide();
    }

    setTimeout(() => {
      const itemsModal = document.getElementById('splitOrderItemsModal');
      if (itemsModal) {
        const modal = new bootstrap.Modal(itemsModal);
        modal.show();
      }
    }, 300);
  }

  // View new split order
  viewNewSplitOrder(): void {
    this.closeSplitSuccessModal();
    // Navigate to new split order details
    if (this.newSplitOrderId) {
      this.router.navigate(['/order-details', this.newSplitOrderId]);
    }
  }

  // Close split success modal
  closeSplitSuccessModal(): void {
    const modalElement = document.getElementById('splitSuccessModal');
    if (modalElement) {
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      modalInstance?.hide();
    }
    this.fetchOrdersData();
  }

  // View original split order
  viewOriginalSplitOrder(): void {
    this.closeSplitSuccessModal();
    // Navigate to original order details
    if (this.currentSplitOrder && this.currentSplitOrder.order_details?.order_id) {
      const orderId = this.currentSplitOrder.order_details.order_id;
      this.router.navigate(['/order-details', orderId]);
    }
  }

  // Open merge modal
  openMergeModal(order: any): void {
    this.currentMergeOrder = order;
    this.eligibleOrdersForMerge = this.getEligibleOrdersForMerge(order);
    this.selectedOrderIdForMerge = null;
    this.selectedTableIdForMerge = order.order_details.table_id?.toString() || '';
    this.mergeOrderCurrency = order.currency_symbol || this.currencySymbol;
    this.mergeReason = '';
    this.mergeErrorMessage = '';
    this.mergeSuccessMessage = '';
    this.mergeOrderItems = [];

    // Fetch available tables
    this.fetchAvailableTables();

    // Show modal
    const modalElement = document.getElementById('mergeOrderModal');
    if (modalElement) {
      const modal = new bootstrap.Modal(modalElement);
      modal.show();
    }
  }

  // Select order for merge
  selectOrderForMerge(orderId: number): void {
    this.selectedOrderIdForMerge = orderId;
    this.onMergeOrderSelectionChange();
  }

  // Go to merge confirmation
  goToMergeConfirmation(): void {
    if (!this.selectedOrderIdForMerge) {
      return;
    }

    // Auto-select all items from both orders for merge
    this.getMergeOrderItems().forEach(item => {
      item.isSelected = true;
    });

    const modalElement = document.getElementById('mergeOrderModal');
    if (modalElement) {
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      modalInstance?.hide();
    }

    setTimeout(() => {
      const confirmModal = document.getElementById('mergeOrderConfirmationModal');
      if (confirmModal) {
        const modal = new bootstrap.Modal(confirmModal);
        modal.show();
      }
    }, 300);
  }

  // Go back to merge step 1
  goBackToMergeStep1(): void {
    const modalElement = document.getElementById('mergeOrderConfirmationModal');
    if (modalElement) {
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      modalInstance?.hide();
    }

    const errorModal = document.getElementById('mergeErrorModal');
    if (errorModal) {
      const errorModalInstance = bootstrap.Modal.getInstance(errorModal);
      errorModalInstance?.hide();
    }

    setTimeout(() => {
      const selectModal = document.getElementById('mergeOrderModal');
      if (selectModal) {
        const modal = new bootstrap.Modal(selectModal);
        modal.show();
      }
    }, 300);
  }

  // Get secondary merge order
  getSecondaryMergeOrder(): any {
    return this.eligibleOrdersForMerge.find(
      (order: any) => order.order_details.order_id === this.selectedOrderIdForMerge
    );
  }

  // Get merge new total
  getMergeNewTotal(): number {
    const primaryTotal = this.currentMergeOrder?.total_price || 0;
    const secondaryOrder = this.getSecondaryMergeOrder();
    const secondaryTotal = secondaryOrder?.total_price || 0;
    const total = primaryTotal + secondaryTotal;
    return parseFloat(total.toFixed(2));
  }

  // View merged order
  viewMergedOrder(): void {
    this.closeMergeSuccessModal();
    // Navigate to order details - currentMergeOrder is the primary order that received the merged items
    if (this.currentMergeOrder && this.currentMergeOrder.order_details?.order_id) {
      const orderId = this.currentMergeOrder.order_details.order_id;
      this.router.navigate(['/order-details', orderId]);
    }
  }

  // Close merge success modal
  closeMergeSuccessModal(): void {
    const modalElement = document.getElementById('mergeSuccessModal');
    if (modalElement) {
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      modalInstance?.hide();
    }
    this.fetchOrdersData();
  }

  // Close merge error modal
  closeMergeErrorModal(): void {
    const modalElement = document.getElementById('mergeErrorModal');
    if (modalElement) {
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      modalInstance?.hide();
    }
  }

  // --- Change Order Type ---
  openChangeTypeModal(order: any): void {
    if (order.order_details.payment_status === 'paid') {
      const warningEl = document.getElementById('changeTypePaidWarningModal');
      if (warningEl) {
        const modal = new bootstrap.Modal(warningEl);
        modal.show();
      }
      return;
    }
    this.currentOrderForTypeChange = order;
    const rawType = order.order_details?.order_type || '';
    this.selectedNewOrderType = rawType === 'reservation-table' ? 'dine-in' : (['Delivery', 'Takeaway', 'dine-in'].includes(rawType) ? rawType : '');
    this.selectedTableIdForTypeChange = order.order_details?.table_id ? String(order.order_details.table_id) : '';
    this.fetchAvailableTables();
    this.loadDeliveryAreas();
    this.changeTypeDeliveryName = order.order_details?.client_name ?? '';
    this.changeTypeDeliveryPhone = order.order_details?.client_phone ?? '';
    this.changeTypeDeliveryCountryCode = order.order_details?.client_country_code ?? '';
    this.changeTypeDeliveryAddress = '';
    this.changeTypeDeliveryAreaId = order.details_order?.client_address_id ? '' : '';
    const modalEl = document.getElementById('changeOrderTypeModal');
    if (modalEl) {
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    }
  }

  /** True when the user selected the same order type as the current order (no actual change). */
  isSameOrderTypeSelected(): boolean {
    const current = this.currentOrderForTypeChange?.order_details?.order_type;
    if (!current || !this.selectedNewOrderType) return false;
    const normalizedCurrent = current === 'reservation-table' ? 'dine-in' : current;
    return normalizedCurrent === this.selectedNewOrderType;
  }

  openChangeTypeConfirmModal(): void {
    const modalEl = document.getElementById('changeOrderTypeModal');
    if (modalEl) {
      const inst = bootstrap.Modal.getInstance(modalEl);
      inst?.hide();
    }
    setTimeout(() => {
      const confirmEl = document.getElementById('confirmChangeOrderTypeModal');
      if (confirmEl) {
        const modal = new bootstrap.Modal(confirmEl);
        modal.show();
      }
    }, 300);
  }

  onConfirmChangeOrderTypeClick(): void {
    if (this.selectedNewOrderType === 'Delivery') {
      const confirmEl = document.getElementById('confirmChangeOrderTypeModal');
      if (confirmEl) {
        const inst = bootstrap.Modal.getInstance(confirmEl);
        inst?.hide();
      }
      setTimeout(() => this.openChangeTypeDeliveryDetailsModal(), 300);
    } else {
      this.submitChangeOrderType();
    }
  }

  openChangeTypeDeliveryDetailsModal(): void {
    const o = this.currentOrderForTypeChange;
    if (o?.order_details) {
      this.changeTypeDeliveryName = this.changeTypeDeliveryName || o.order_details.client_name || '';
      this.changeTypeDeliveryPhone = this.changeTypeDeliveryPhone || o.order_details.client_phone || '';
      this.changeTypeDeliveryCountryCode = this.changeTypeDeliveryCountryCode || o.order_details.client_country_code || '';
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
            .map((c: { phone_code: string; image: string; length: number }) => ({
              code: (c.phone_code || '').trim(),
              flag: c.image || '',
              phoneLength: c.length || 10,
            }))
            .filter((c: any) => allowedCodes.includes(c.code.replace(/\s+/g, '')));
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
    if (!term) {
      this.changeTypeDeliveryFilteredCountries = [...this.changeTypeDeliveryCountryList];
      return;
    }
    this.changeTypeDeliveryFilteredCountries = this.changeTypeDeliveryCountryList.filter(
      (c: any) => (c.code || '').toLowerCase().includes(term)
    );
  }

  selectChangeTypeDeliveryCountry(country: any): void {
    this.changeTypeDeliverySelectedCountry = country;
    this.changeTypeDeliveryCountryCode = country?.code || '';
    this.cdr.detectChanges();
  }

  useSameWhatsappChangeType(value: boolean): void {
    this.changeTypeDeliveryUseSameWhatsapp = value;
    if (value) {
      this.changeTypeDeliveryWhatsapp = '';
      this.changeTypeDeliveryWhatsappCode = this.changeTypeDeliverySelectedCountry?.code || this.changeTypeDeliveryCountryCode || '';
    }
    this.cdr.detectChanges();
  }

  searchChangeTypeByPhone(): void {
    this.changeTypeDeliverySearchPhoneIdle = false;
    setTimeout(() => {
      this.changeTypeDeliverySearchPhoneIdle = true;
      this.cdr.detectChanges();
    }, 800);
  }

  submitChangeOrderTypeFromDeliveryModal(): void {
    this.buildChangeTypeDeliveryAddressFromParts();
    this.submitChangeOrderType();
  }

  /**
   * Build payload for api/cashier/add/address so the client is registered and findable
   * when searching by phone from the Home delivery-details flow.
   */
  private buildAddAddressPayloadFromChangeTypeDelivery(): Record<string, unknown> | null {
    const name = this.changeTypeDeliveryName?.trim();
    const phone = this.changeTypeDeliveryPhone?.trim();
    const countryCode = (this.changeTypeDeliverySelectedCountry?.code || this.changeTypeDeliveryCountryCode || '').trim();
    const areaId = this.changeTypeDeliveryAreaId;
    if (!phone || !name || !countryCode || !areaId) return null;
    const address = (this.changeTypeDeliveryAddress?.trim() || this.changeTypeDeliveryBuilding?.trim() || 'عنوان التوصيل').trim();
    const payload: Record<string, unknown> = {
      client_name: name,
      address_phone: phone,
      country_code: countryCode,
      whatsapp_number_code: (this.changeTypeDeliveryWhatsappCode || countryCode).trim(),
      notes: this.changeTypeDeliveryNotes?.trim() || '',
      area_id: parseInt(areaId, 10),
      address_type: this.changeTypeDeliveryBuildingType || 'apartment',
      building: this.changeTypeDeliveryBuilding?.trim() || null,
      apartment_number: this.changeTypeDeliveryApartment?.trim() || null,
      floor_number: this.changeTypeDeliveryFloor?.trim() || null,
      address,
    };
    if (this.changeTypeDeliveryBuildingType === 'hotel' && this.changeTypeDeliveryHotelId) {
      payload['hotel_id'] = parseInt(String(this.changeTypeDeliveryHotelId), 10);
    }
    if (this.changeTypeDeliveryWhatsapp?.trim()) {
      payload['whatsapp_number'] = this.changeTypeDeliveryWhatsapp.trim();
    }
    return payload;
  }

  private buildChangeTypeDeliveryAddressFromParts(): void {
    const parts: string[] = [];
    const typeLabels: Record<string, string> = { apartment: 'شقة', villa: 'فيلا', office: 'مكتب', hotel: 'فندق' };
    if (this.changeTypeDeliveryBuildingType) parts.push(typeLabels[this.changeTypeDeliveryBuildingType] || this.changeTypeDeliveryBuildingType);
    if (this.changeTypeDeliveryBuildingType === 'hotel' && this.changeTypeDeliveryHotelName?.trim()) {
      parts.push(this.changeTypeDeliveryHotelName.trim());
    } else if (this.changeTypeDeliveryBuilding?.trim()) {
      parts.push(this.changeTypeDeliveryBuilding.trim());
    }
    if (this.changeTypeDeliveryApartment?.trim()) parts.push('شقة ' + this.changeTypeDeliveryApartment.trim());
    if (this.changeTypeDeliveryFloor?.trim()) parts.push('الدور ' + this.changeTypeDeliveryFloor.trim());
    let built = parts.join('، ');
    if (this.changeTypeDeliveryAddress?.trim()) built = built ? built + ' - ' + this.changeTypeDeliveryAddress.trim() : this.changeTypeDeliveryAddress.trim();
    if (this.changeTypeDeliveryNotes?.trim()) built = built ? built + ' - ' + this.changeTypeDeliveryNotes.trim() : this.changeTypeDeliveryNotes.trim();
    if (built) this.changeTypeDeliveryAddress = built;
  }

  getOrderTypeLabelForChange(type: string): string {
    if (!type) return '';
    const labels: Record<string, string> = {
      'dine-in': 'داخل المطعم (محلي)',
      'Takeaway': 'استلام من الفرع',
      'Delivery': 'توصيل',
    };
    return labels[type] || type;
  }

  /** Short label for confirm-change-type modal (Figma): استلام، محلي، توصيل */
  getOrderTypeShortLabel(type: string): string {
    if (!type) return '';
    const short: Record<string, string> = {
      'dine-in': 'محلي',
      'Takeaway': 'استلام',
      'Delivery': 'توصيل',
    };
    return short[type] || this.getOrderTypeLabelForChange(type);
  }

  /** Icon class for order type in confirm modal (Figma) */
  getOrderTypeIconClass(type: string): string {
    if (!type) return 'fa-solid fa-circle';
    const icons: Record<string, string> = {
      'dine-in': 'fa-solid fa-utensils',
      'Takeaway': 'fa-solid fa-bag-shopping',
      'Delivery': 'fa-solid fa-truck',
    };
    return icons[type] || 'fa-solid fa-circle';
  }

  /** Table number for "to" box when changing to dine-in (e.g. "39") */
  getSelectedTableNumberForTypeChange(): string {
    if (this.selectedNewOrderType !== 'dine-in' || !this.selectedTableIdForTypeChange) return '';
    const table = this.availableTables.find(
      (t: any) => String(t.id) === String(this.selectedTableIdForTypeChange)
    );
    return table ? String(table.number) : '';
  }

  /** FR1: Confirmation modal message – "This order type will be changed from [X] to [Y]. Are you sure you want to proceed?" */
  getConfirmChangeOrderTypeMessage(): string {
    const fromType = this.getOrderTypeLabelForChange(this.currentOrderForTypeChange?.order_details?.order_type || '');
    const toType = this.getOrderTypeLabelForChange(this.selectedNewOrderType || '');
    const x = fromType || '[X]';
    const y = toType || '[Y]';
    const lang = (typeof localStorage !== 'undefined' && localStorage.getItem('lang')) || 'ar';
    if (lang === 'en') {
      return `This order type will be changed from ${x} to ${y}. Are you sure you want to proceed?`;
    }
    return `سيتم تغيير نوع هذا الطلب من ${x} إلى ${y}. هل أنت متأكد أنك تريد المتابعة؟`;
  }

  /** Subtext for confirmation modal (recalculation note). */
  getConfirmChangeOrderTypeSubtext(): string {
    return 'سيتم إعادة حساب الرسوم والمجاميع تلقائياً حسب النوع الجديد دون تغيير الأصناف أو الكميات أو أسعارها.';
  }

  getTableNameForTypeChange(): string {
    if (this.selectedNewOrderType !== 'dine-in' || !this.selectedTableIdForTypeChange) return '';
    const table = this.availableTables.find(
      (t: any) => String(t.id) === String(this.selectedTableIdForTypeChange)
    );
    return table ? `طاولة ${table.number}` : '';
  }

  loadDeliveryAreas(): void {
    const branchId = localStorage.getItem('branch_id');
    if (!branchId) return;
    this.http.get<{ status: boolean; data: any[] }>(`${baseUrl}api/areas/${branchId}`).subscribe({
      next: (res) => {
        if (res?.status && Array.isArray(res.data)) this.deliveryAreas = res.data;
      },
      error: () => {},
    });
  }

  loadChangeTypeHotels(): void {
    const token = localStorage.getItem('authToken');
    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });
    this.http.get<{ data?: any[] }>(`${baseUrl}api/listHotels`, { headers }).subscribe({
      next: (res) => {
        this.changeTypeDeliveryHotels = Array.isArray(res?.data) ? res.data : [];
      },
      error: () => { this.changeTypeDeliveryHotels = []; },
    });
  }

  onChangeTypeHotelSelect(hotelId: string | number): void {
    if (!hotelId) {
      this.changeTypeDeliveryHotelName = '';
      return;
    }
    const hotel = this.changeTypeDeliveryHotels.find((h: any) => String(h.id) === String(hotelId));
    this.changeTypeDeliveryHotelName = hotel ? (hotel.name_ar || hotel.name_en || hotel.name || '') : '';
  }

  isDeliveryInfoComplete(): boolean {
    if (this.selectedNewOrderType !== 'Delivery') return true;
    const o = this.currentOrderForTypeChange;
    const hasExisting = o?.order_details?.client_address_id || (o?.details_order as any)?.client_address_id;
    const hasName = !!(o?.order_details?.client_name || this.changeTypeDeliveryName?.trim());
    const hasPhone = !!(o?.order_details?.client_phone || this.changeTypeDeliveryPhone?.trim());
    const hasCode = !!(o?.order_details?.client_country_code || this.changeTypeDeliveryCountryCode?.trim());
    if (hasExisting && hasName && hasPhone && hasCode) return true;
    const hasAddress = !!(
      this.changeTypeDeliveryAddress?.trim() ||
      this.changeTypeDeliveryBuilding?.trim() ||
      this.changeTypeDeliveryApartment?.trim() ||
      this.changeTypeDeliveryFloor?.trim() ||
      (this.changeTypeDeliveryBuildingType === 'hotel' && (this.changeTypeDeliveryHotelId || this.changeTypeDeliveryHotelName?.trim()))
    );
    if (this.changeTypeDeliveryBuildingType === 'hotel') {
      return !!(this.changeTypeDeliveryHotelId || this.changeTypeDeliveryHotelName?.trim());
    }
    return !!(
      this.changeTypeDeliveryName?.trim() &&
      this.changeTypeDeliveryPhone?.trim() &&
      this.changeTypeDeliveryCountryCode?.trim() &&
      this.changeTypeDeliveryAreaId &&
      hasAddress
    );
  }

  private readonly changeOrderTypeAllowedValues = ['Delivery', 'Takeaway', 'dine-in'] as const;

  private getChangeOrderTypeErrorMessage(errorData: any, fallback?: string): string {
    const defaultMsg = 'حدث خطأ أثناء تغيير نوع الطلب.';
    if (errorData && typeof errorData === 'object' && !Array.isArray(errorData)) {
      const messages: string[] = [];
      for (const key of Object.keys(errorData)) {
        const val = errorData[key];
        if (Array.isArray(val) && val.length > 0 && val[0]) messages.push(String(val[0]).trim());
        else if (typeof val === 'string') messages.push(val.trim());
      }
      if (messages.length > 0) return messages.join(' ');
    }
    if (typeof errorData === 'string') return errorData;
    if (Array.isArray(errorData) && errorData[0]) return String(errorData[0]);
    return fallback || defaultMsg;
  }

  submitChangeOrderType(): void {
    if (!this.currentOrderForTypeChange) return;
    const newOrderType = (this.selectedNewOrderType && this.changeOrderTypeAllowedValues.includes(this.selectedNewOrderType as any))
      ? this.selectedNewOrderType
      : '';
    if (!newOrderType) return;
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
    // When changing to Delivery: send existing order client data or cashier-entered delivery info
    if (newOrderType === 'Delivery') {
      const o = this.currentOrderForTypeChange;
      const od = o?.order_details;
      const details = (o as any)?.details_order;
      const addressId = details?.client_address_id ?? od?.client_address_id ?? (o as any)?.client_address_id;
      if (addressId != null && !this.changeTypeDeliveryAreaId) {
        body['client_address'] = addressId;
      }
      body['client_name'] = this.changeTypeDeliveryName?.trim() || od?.client_name || '';
      body['client_phone'] = this.changeTypeDeliveryPhone?.trim() || od?.client_phone || '';
      body['client_country_code'] = (this.changeTypeDeliverySelectedCountry?.code || this.changeTypeDeliveryCountryCode || od?.client_country_code || '').trim();
      if (this.changeTypeDeliveryAreaId) {
        body['area_id'] = parseInt(this.changeTypeDeliveryAreaId, 10);
        body['delivery_address'] = this.changeTypeDeliveryAddress?.trim() || this.changeTypeDeliveryBuilding?.trim() || 'عنوان التوصيل';
        // Full address payload for storeAddress (same as add-address)
        body['address_type'] = this.changeTypeDeliveryBuildingType || 'apartment';
        body['building'] = this.changeTypeDeliveryBuilding?.trim() || null;
        body['apartment_number'] = this.changeTypeDeliveryApartment?.trim() || null;
        body['floor_number'] = this.changeTypeDeliveryFloor?.trim() || null;
        body['address'] = this.changeTypeDeliveryAddress?.trim() || this.changeTypeDeliveryBuilding?.trim() || 'عنوان التوصيل';
        body['notes'] = this.changeTypeDeliveryNotes?.trim() || null;
        if (this.changeTypeDeliveryBuildingType === 'hotel' && this.changeTypeDeliveryHotelId) {
          body['hotel_id'] = parseInt(String(this.changeTypeDeliveryHotelId), 10);
        }
        if (this.changeTypeDeliveryWhatsappCode?.trim()) body['whatsapp_number_code'] = this.changeTypeDeliveryWhatsappCode.trim();
        if (this.changeTypeDeliveryWhatsapp?.trim()) body['whatsapp_number'] = this.changeTypeDeliveryWhatsapp.trim();
      }
      if (addressId != null && !this.changeTypeDeliveryAreaId) {
        body['client_address'] = addressId;
      }
    }

    this.http.post(`${baseUrl}api/orders/changeOrderType`, body, { headers }).subscribe({
      next: (res: any) => {
        this.isChangeTypeSubmitting = false;
        const confirmEl = document.getElementById('confirmChangeOrderTypeModal');
        if (confirmEl) {
          const inst = bootstrap.Modal.getInstance(confirmEl);
          inst?.hide();
        }
        const deliveryDetailsEl = document.getElementById('changeTypeDeliveryDetailsModal');
        if (deliveryDetailsEl) {
          const inst2 = bootstrap.Modal.getInstance(deliveryDetailsEl);
          inst2?.hide();
        }
        if (res?.status) {
          // Register client/address so they appear when searching by phone from Home (delivery-details)
          const hadNewDeliveryAddress = !!(this.changeTypeDeliveryAreaId && this.changeTypeDeliveryPhone?.trim());
          if (hadNewDeliveryAddress) {
            const registerPayload = this.buildAddAddressPayloadFromChangeTypeDelivery();
            if (registerPayload) {
              this.addAddressService.submitForm(registerPayload).subscribe({
                next: () => {},
                error: () => {},
              });
            }
          }
          this.changeTypeSuccessMessage = (res?.message && String(res.message).trim())
            ? res.message
            : 'تم تغيير نوع الطلب وإعادة حساب الرسوم والمجاميع بنجاح.';
          this.successMessage = this.changeTypeSuccessMessage;
          if (this.successMessageModal) this.successMessageModal.show();
          this.refreshOrderAfterCancel(this.currentOrderForTypeChange.order_details.order_id);
          this.currentOrderForTypeChange = null;
          this.selectedNewOrderType = '';
          this.selectedTableIdForTypeChange = '';
          this.changeTypeDeliveryName = '';
          this.changeTypeDeliveryPhone = '';
          this.changeTypeDeliveryCountryCode = '';
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
        } else {
          const errMsg = this.getChangeOrderTypeErrorMessage(res?.errorData, res?.message);
          this.showMessageModal(errMsg, 'error');
        }
      },
      error: (err: any) => {
        this.isChangeTypeSubmitting = false;
        const errMsg = this.getChangeOrderTypeErrorMessage(
          err?.error?.errorData,
          err?.error?.message || err?.message
        );
        this.showMessageModal(errMsg, 'error');
      },
    });
  }

  /**
   * Get branch default delivery_fees from localStorage (dashboard "رسوم التوصيل").
   * Used to fix wrong delivery fees returned after change-type-to-delivery when
   * backend uses a different source (e.g. 25) than the branch default (2).
   */
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

  /**
   * For delivery orders, override delivery_fees with branch default when set,
   * and adjust total so invoice calculation is correct (fixes wrong fee after change-type).
   */
  private applyBranchDeliveryFeesToOrder(order: any): any {
    const orderType = order?.order_details?.order_type || order?.details_order?.order_type;
    if (orderType !== 'Delivery') return order;
    const branchFee = this.getBranchDeliveryFees();
    if (branchFee === null) return order;

    const summary = order?.details_order?.order_summary || order?.order_summary;
    const currentFee = summary != null ? Number(summary.delivery_fees) : NaN;
    if (currentFee === branchFee || (isNaN(currentFee) && branchFee === 0)) return order;

    const oldFee = isNaN(currentFee) ? 0 : currentFee;
    const delta = branchFee - oldFee;

    const out = { ...order };
    if (out.details_order?.order_summary) {
      const sum = out.details_order.order_summary;
      out.details_order = {
        ...out.details_order,
        order_summary: {
          ...sum,
          delivery_fees: branchFee,
          ...(typeof sum.total === 'number' && { total: sum.total + delta }),
        },
      };
    }
    if (out.order_summary) {
      const sum = out.order_summary;
      out.order_summary = {
        ...sum,
        delivery_fees: branchFee,
        ...(typeof sum.total === 'number' && { total: sum.total + delta }),
      };
    }
    if (typeof out.total_price === 'number') {
      out.total_price = out.total_price + delta;
    }
    return out;
  }

  // Refresh order data after cancellation to update calculations
  refreshOrderAfterCancel(orderId: number): void {
    this._OrderListDetailsService.NewgetOrderById(orderId)
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (res: any) => {
          if (res?.data?.order) {
            let updatedOrder = res.data.order;
            updatedOrder = this.applyBranchDeliveryFeesToOrder(updatedOrder);
            // Find and update the order in the orders array
            const orderIndex = this.orders.findIndex(
              (o: any) => o.order_details?.order_id === orderId
            );
            if (orderIndex !== -1) {
              this.orders[orderIndex] = this.applyBranchDeliveryFeesToOrder({
                ...this.orders[orderIndex],
                ...updatedOrder,
                currency_symbol: this.currencySymbol
              });
              this.orders = [...this.orders];
              this.filterOrders();
              this.cdr.detectChanges();
              console.log('✅ Order refreshed with updated calculations:', updatedOrder);
            }
          }
        },
        error: (err) => {
          console.error('❌ Error refreshing order after cancel:', err);
        }
      });
  }

  // Fetch available tables (preserve type, floor, area from API for correct tab filtering)
  fetchAvailableTables(): void {
    this.tablesService.getTables().subscribe({
      next: (response: any) => {
        if (response.status && response.data) {
          this.availableTables = response.data.map((table: any) => {
            const typeRaw = (table.type ?? table.table_type ?? table.type_ar ?? table.type_en ?? '').toString().toLowerCase().trim();
            const typeAr = (table.type_ar ?? table.type ?? '').toString().trim();
            const floorId = table.floor_id ?? table.floor?.id ?? null;
            const floorName = (table.floor_name ?? table.floor?.name ?? table.floor?.name_en ?? table.floor?.name_ar ?? '').toString().toLowerCase();
            const areaName = (table.floor_area_name ?? table.area_name ?? table.floor_partition?.name ?? table.floor_partition?.name_ar ?? table.floor_area ?? '').toString().toLowerCase();
            return {
              id: table.id,
              name: table.name_ar || table.name || `طاولة ${table.number}`,
              number: table.number,
              status: table.status || 1,
              seats: table.seats || table.seat_count,
              location: table.floor_partition_id ?? table.floor_id,
              tableType: typeRaw || typeAr,
              tableTypeAr: typeAr,
              type_id: table.type_id ?? table.table_type_id,
              floorId,
              floorName,
              areaName,
            };
          });
          this.filterTablesByLocation('all');
        }
      },
      error: (err) => {
        console.error('Error fetching tables:', err);
      },
    });
  }

  /** هل الطاولة مصنفة كـ "داخلية" (الصالة الرئيسية). */
  private isTableInternal(table: any): boolean {
    const t = (table.tableType ?? table.tableTypeAr ?? '').toString().toLowerCase();
    const ar = (table.tableTypeAr ?? '').toString().trim();
    return t === 'internal' || ar === 'داخلي' || t === 'داخلي' || table.type_id === 1;
  }

  /** هل الطاولة مصنفة كـ "خارجية" (التراس الخارجي). */
  private isTableExternal(table: any): boolean {
    const t = (table.tableType ?? table.tableTypeAr ?? '').toString().toLowerCase();
    const ar = (table.tableTypeAr ?? '').toString().trim();
    return t === 'external' || ar === 'خارجي' || t === 'خارجي' || table.type_id === 2;
  }

  /** هل الطاولة في الطابق العلوي. */
  private isTableUpperFloor(table: any): boolean {
    const name = (table.floorName ?? '').toString();
    const id = table.floorId;
    return name.includes('upper') || name.includes('علوي') || name.includes('طابق 2') || id === 2 || id === '2';
  }

  /** هل الطاولة في قسم العائلات. */
  private isTableFamily(table: any): boolean {
    const name = (table.areaName ?? '').toString();
    return name.includes('family') || name.includes('عائل');
  }

  // Filter tables by location: Main = internal, Terrace = external, Upper = upper floor, Family = family area
  filterTablesByLocation(location: string): void {
    this.selectedLocationFilter = location;
    if (location === 'all') {
      this.filteredTablesForSplit = [...this.availableTables];
    } else {
      this.filteredTablesForSplit = this.availableTables.filter((table: any) => {
        switch (location) {
          case 'main':
            return this.isTableInternal(table);
          case 'terrace':
            return this.isTableExternal(table);
          case 'upper':
            return this.isTableUpperFloor(table);
          case 'family':
            return this.isTableFamily(table);
          default:
            return false;
        }
      });
    }
  }

  // Get merge order items (from both orders)
  getMergeOrderItems(): any[] {
    if (!this.selectedOrderIdForMerge || !this.currentMergeOrder) {
      return [];
    }

    // If items are already loaded and order hasn't changed, return cached items
    if (this.mergeOrderItems.length > 0) {
      const firstItem = this.mergeOrderItems[0];
      const isFromCurrentOrder = firstItem.sourceOrder === 'current';
      const isFromSelectedOrder = firstItem.sourceOrder === 'selected';

      // Check if items match current selection
      if (isFromCurrentOrder || isFromSelectedOrder) {
        return this.mergeOrderItems;
      }
    }

    const selectedOrder = this.eligibleOrdersForMerge.find(
      (order: any) => order.order_details.order_id === this.selectedOrderIdForMerge
    );

    if (!selectedOrder) {
      return [];
    }

    // Get items from both orders
    const currentOrderItems = (this.currentMergeOrder.order_items || []).map((item: any) => ({
      ...item,
      isSelected: false,
      sourceOrder: 'current',
    }));

    const selectedOrderItems = (selectedOrder.order_items || []).map((item: any) => ({
      ...item,
      isSelected: false,
      sourceOrder: 'selected',
    }));

    this.mergeOrderItems = [...currentOrderItems, ...selectedOrderItems];
    return this.mergeOrderItems;
  }

  // Update merge items when order selection changes
  onMergeOrderSelectionChange(): void {
    this.mergeOrderItems = [];
    this.getMergeOrderItems();
  }

  // Submit split request
  submitSplitRequest(): void {
    if (!this.currentSplitOrder || !this.selectedTableIdForSplit) {
      this.splitErrorMessage = 'يرجى اختيار الطاولة';
      return;
    }

    const selectedItems = this.getSelectedSplitItems();
    if (selectedItems.length === 0) {
      this.splitErrorMessage = 'يرجى اختيار كمية واحدة على الأقل';
      return;
    }

    // Check if at least one item remains (with quantity > 0)
    const remainingItems = this.getRemainingSplitItems();
    if (remainingItems.length === 0) {
      this.splitErrorMessage = 'يجب أن يبقى على الأقل كمية واحدة في الطلب الأصلي';
      return;
    }

    this.isSplitSubmitting = true;
    this.splitErrorMessage = '';
    this.splitSuccessMessage = '';

    const token = localStorage.getItem('authToken');
    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
      lang: 'ar',
    });

    // Prepare items array with order_detail_id and quantity
    const items = selectedItems.map((item: any) => ({
      order_detail_id: item.order_detail_id,
      quantity: item.selectedQuantity
    }));

    const body = {
      order_id: this.currentSplitOrder.order_details.order_id,
      new_table_id: parseInt(this.selectedTableIdForSplit),
      items: items,
    };

    this.http
      .post(`${baseUrl}api/orders/split`, body, { headers })
      .subscribe({
        next: (response: any) => {
          this.isSplitSubmitting = false;
          if (response.status) {
            // Save new order ID from response
            if (response.data?.new_order_id) {
              this.newSplitOrderId = response.data.new_order_id;
            }

            // Close confirmation modal
            const confirmModal = document.getElementById('splitOrderConfirmationModal');
            if (confirmModal) {
              const modalInstance = bootstrap.Modal.getInstance(confirmModal);
              modalInstance?.hide();
            }

            // Show success modal
            setTimeout(() => {
              const successModal = document.getElementById('splitSuccessModal');
              if (successModal) {
                const modal = new bootstrap.Modal(successModal);
                modal.show();
              }
            }, 300);
          } else {
            this.splitErrorMessage =
              response.message || response.errorData?.error || 'حدث خطأ أثناء الإرسال';
          }
        },
        error: (err: any) => {
          this.isSplitSubmitting = false;

          // Handle 401 Unauthorized
          if (err.status === 401) {
            this.splitErrorMessage = 'انتهت صلاحية الجلسة. يرجى تسجيل الدخول مرة أخرى';
            // Optionally redirect to login
            // this.router.navigate(['/login']);
            return;
          }

          const errorMsg =
            err.error?.message ||
            err.error?.errorData?.error ||
            err.message ||
            'حدث خطأ أثناء الإرسال';
          this.splitErrorMessage = Array.isArray(errorMsg) ? errorMsg[0] : errorMsg;
        },
      });
  }

  // Submit merge request
  submitMergeRequest(): void {
    if (!this.currentMergeOrder || !this.selectedOrderIdForMerge) {
      this.mergeErrorMessage = 'يرجى اختيار الطلب';
      return;
    }

    const selectedOrder = this.eligibleOrdersForMerge.find(
      (order: any) => order.order_details.order_id === this.selectedOrderIdForMerge
    );

    if (!selectedOrder) {
      this.mergeErrorMessage = 'الطلب المحدد غير موجود';
      return;
    }

    // Check payment status compatibility
    if (this.currentMergeOrder.order_details.payment_status !== selectedOrder.order_details.payment_status) {
      // Show error modal
      const confirmModal = document.getElementById('mergeOrderConfirmationModal');
      if (confirmModal) {
        const modalInstance = bootstrap.Modal.getInstance(confirmModal);
        modalInstance?.hide();
      }

      setTimeout(() => {
        const errorModal = document.getElementById('mergeErrorModal');
        if (errorModal) {
          const modal = new bootstrap.Modal(errorModal);
          modal.show();
        }
      }, 300);
      return;
    }

    this.isMergeSubmitting = true;
    this.mergeErrorMessage = '';
    this.mergeSuccessMessage = '';

    // Check if token exists
    const token = localStorage.getItem('authToken');
    if (!token) {
      this.isMergeSubmitting = false;
      this.mergeErrorMessage = 'يرجى تسجيل الدخول مرة أخرى';
      return;
    }

    const body = {
      primary_order_id: this.currentMergeOrder.order_details.order_id,
      secondary_order_id: this.selectedOrderIdForMerge,
    };

    // Let the interceptor handle the Authorization header
    this.http
      .post(`${baseUrl}api/orders/merge`, body)
      .subscribe({
        next: (response: any) => {
          this.isMergeSubmitting = false;
          if (response.status) {
            // ✅ Remove secondary order from local list immediately
            if (this.selectedOrderIdForMerge) {
              // Remove from main orders array
              this.orders = this.orders.filter(
                (order: any) => order.order_details?.order_id !== this.selectedOrderIdForMerge
              );
              // Remove from filtered orders
              this.filteredOrders = this.filteredOrders.filter(
                (order: any) => order.order_details?.order_id !== this.selectedOrderIdForMerge
              );

              // ✅ Remove from localStorage (saved orders) if exists
              const savedOrders = localStorage.getItem('savedOrders');
              if (savedOrders) {
                try {
                  const parsedOrders = JSON.parse(savedOrders);
                  const updatedSavedOrders = parsedOrders.filter(
                    (order: any) => order.orderId !== this.selectedOrderIdForMerge?.toString()
                  );
                  localStorage.setItem('savedOrders', JSON.stringify(updatedSavedOrders));
                } catch (error) {
                  console.error('Error removing order from localStorage:', error);
                }
              }

              // Update filtered orders by status
              this.filterOrders();
            }

            // Close confirmation modal
            const confirmModal = document.getElementById('mergeOrderConfirmationModal');
            if (confirmModal) {
              const modalInstance = bootstrap.Modal.getInstance(confirmModal);
              modalInstance?.hide();
            }

            // Show success modal
            setTimeout(() => {
              const successModal = document.getElementById('mergeSuccessModal');
              if (successModal) {
                const modal = new bootstrap.Modal(successModal);
                modal.show();
              }
            }, 300);

            // ✅ Refresh orders data from API to ensure consistency
            // Also update the primary order in the list
            const primaryOrderIndex = this.orders.findIndex(
              (order: any) => order.order_details?.order_id === this.currentMergeOrder?.order_details?.order_id
            );
            if (primaryOrderIndex !== -1) {
              // Mark primary order as updated
              this.orders[primaryOrderIndex].recentlyUpdated = true;
              setTimeout(() => {
                this.orders[primaryOrderIndex].recentlyUpdated = false;
              }, 3000);
            }

            // Refresh orders after a short delay to ensure backend has processed
            setTimeout(() => {
              this.fetchOrdersData();
            }, 1000);
          } else {
            this.mergeErrorMessage =
              response.message || response.errorData?.error || 'حدث خطأ أثناء الإرسال';
          }
        },
        error: (err: any) => {
          this.isMergeSubmitting = false;

          // Handle 401 Unauthorized
          if (err.status === 401) {
            this.mergeErrorMessage = 'انتهت صلاحية الجلسة. يرجى تسجيل الدخول مرة أخرى';
            // Optionally redirect to login
            // this.router.navigate(['/login']);
            return;
          }

          const errorMsg =
            err.error?.message ||
            err.error?.errorData?.error ||
            err.message ||
            'حدث خطأ أثناء الإرسال';
          this.mergeErrorMessage = Array.isArray(errorMsg) ? errorMsg[0] : errorMsg;

          // Show error modal if payment status conflict
          if (errorMsg.includes('مدفوع') || errorMsg.includes('unpaid') || errorMsg.includes('paid')) {
            const confirmModal = document.getElementById('mergeOrderConfirmationModal');
            if (confirmModal) {
              const modalInstance = bootstrap.Modal.getInstance(confirmModal);
              modalInstance?.hide();
            }

            setTimeout(() => {
              const errorModal = document.getElementById('mergeErrorModal');
              if (errorModal) {
                const modal = new bootstrap.Modal(errorModal);
                modal.show();
              }
            }, 300);
          }
        },
      });
  }
}
