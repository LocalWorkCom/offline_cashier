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
import { IndexeddbService } from '../services/indexeddb.service';
import { json } from 'node:stream/consumers';

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
  allowedOrderTypes = ['Takeaway', 'Delivery', 'dine-in'];
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
    private dbService: IndexeddbService
  ) {
    // const navigation = this.router.getCurrentNavigation();
    // this.orderDetails = navigation?.extras.state?.['orderData'];
    // this.orderTypeById= this.orderDetails
    // console.log(this.orderDetails,'orderDetails')
  }

  ngOnInit(): void {
    console.log("this.isOnline",this.isOnline);
    this.selectedOrderTypeStatus = 'All';
    if(this.isOnline == false)
    {
      this.loadOrdersFromIndexedDB();
    }
    else
    {
      this.fetchOrdersFromAPI();
    }

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

  //start dalia

  // Load orders from IndexedDB
  private loadOrdersFromIndexedDB(): void {
    this.dbService.getOrders().then(orders => {
      if (orders && orders.length > 0) {
        console.log('Orders loaded from IndexedDB:', orders.length);

        this.processOrders(orders);

        // Check if data is stale (older than 5 minutes)
        this.dbService.getOrdersLastSync().then(lastSync => {
          const fiveMinutesAgo = Date.now() - (5 * 60 * 1000);
          if (this.isOnline && lastSync < fiveMinutesAgo) {
            this.fetchOrdersFromAPI();
          }
        }).catch(err => {
          console.error('Error getting last sync time:', err);
          if (this.isOnline) {
            this.fetchOrdersFromAPI();
          }
        });
      } else if (this.isOnline) {
        // No data in IndexedDB, fetch from API
        this.fetchOrdersFromAPI();
      } else {
        // Offline and no data available
        this.loading = false;
        console.warn('No orders available offline');
      }
    }).catch(err => {
      console.error('Error loading orders from IndexedDB:', err);
      if (this.isOnline) {
        this.fetchOrdersFromAPI();
      } else {
        this.loading = false;
      }
    });
  }
  // Fetch orders from API
  private fetchOrdersFromAPI(): void {
    this.loading = false;
    this.ordersListService.getOrdersList().pipe(
      finalize(() => {
        this.loading = true;
      }),
      takeUntil(this.destroy$)
    ).subscribe({
      next: (response) => {
        if (response.status && response.data.orders) {
          console.log('Orders fetched from API:', response.data.orders.length);
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
          this.loading = true;
        }
      },
      error: (err) => {
        console.error('Error fetching orders from API:', err);
        this.loading = true;

        // If we're online but API failed, try to use IndexedDB data as fallback
        if (this.isOnline) {
          this.dbService.getOrders().then(orders => {
            if (orders && orders.length > 0) {
              console.log('Using IndexedDB data as fallback:', orders.length);
              this.processOrders(orders);
            }
          });
        }
      },
    });
  }
  // Process orders (common method for both API and IndexedDB data)
  private processOrders(orders: any[]): void {
    this.currencySymbol = orders[0]?.currency_symbol;
    this.orders = orders
      .filter((order: any) =>
        this.allowedOrderTypes.includes(order.order_details?.order_type) &&
        this.allowedStatuses.includes(order.order_details?.status)
      )
      .map((order: any) => ({
        ...order,
        currency_symbol: this.currencySymbol,
      }));

    this.ordersStatus = Array.from(
      new Set(this.orders.map(order => order.order_details?.status || ""))
    ).filter(status => this.allowedStatuses.includes(status));

    if (this.ordersStatus.length > 0) this.ordersStatus.unshift('all');

    this.filterOrders();
    this.loading = true;
  }
  //end dalia

  listenToNewOrder() {
    this.newOrder.listenToNewOrder();
    this.newOrder.orderAdded$
      .pipe(takeUntil(this.destroy$))
      .subscribe((newOrder) => {
        this.orders = [newOrder.data.Order, ...this.orders];
        console.log(
          this.selectedOrderTypeStatus,
          'new selectedOrderTypeStatus arrived'
        );
        const orderType =
          newOrder.data.Order.order_details.order_type?.toLowerCase();
        const status = newOrder.data.Order.order_details.status?.toLowerCase();

        const isAllOrderTypes =
          this.selectedOrderTypeStatus?.toLowerCase() === 'all';
        const isAllStatuses = this.selectedStatus?.toLowerCase() === 'all';

        const matchesOrderType =
          isAllOrderTypes ||
          orderType === this.selectedOrderTypeStatus?.toLowerCase();

        const matchesStatus =
          isAllStatuses || status === this.selectedStatus?.toLowerCase();

        if (matchesOrderType && matchesStatus) {
          this.filteredOrders = [...this.orders];
        }

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
      item.selectedQuantity += 1;
      item.total_dish_price = item.unitPrice * item.selectedQuantity;
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
      item.total_dish_price = item.unitPrice * item.selectedQuantity;
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
      case 'pending':
        return 'بانتظار التحضير';
      case 'completed':
        return 'مكتملة ';
      case 'readyForPickup':
        if (
          this.selectedOrderTypeStatus === 'Delivery' ||
          this.selectedOrderTypeStatus === 'Takeaway'
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
    };

    return (
      images[type as 'Takeaway' | 'Delivery' | 'dine-in'] ||
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

  listenToDishChange() {
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
          const currentOrder = this.orders[orderIndex];

          
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
  removeDish(orderDetailId: number , quantity: number , order: any): void {
    this.removeLoading = true;
    const url = `${this.apiUrl}api/orders/cashier/request-cancel`;
    console.log(orderDetailId, order,"id to delete");

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
          quantity: quantity 
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

  continueOrder(order: any): void {
    console.log('tet');
    this.productsService.destroy(); // 🔥 destroy stream

    localStorage.removeItem('cart');
    localStorage.setItem('currentOrderId', order.order_details.order_id);
    localStorage.setItem('currentOrderData', JSON.stringify(order));

    this.router.navigate(['/home']);
  }
}
