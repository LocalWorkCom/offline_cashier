import {
  ChangeDetectorRef,
  Component,
  DoCheck,
  ElementRef,
  OnDestroy,
  OnInit,
  SimpleChanges,
  ViewChild,
} from '@angular/core';
import { ActivatedRoute, RouterLink, RouterLinkActive } from '@angular/router';
import { PillsService } from '../services/pills.service';
import { CommonModule } from '@angular/common';
import { OrdersService } from '../services/orders.service';
import { Router } from '@angular/router';
import { HttpHeaders } from '@angular/common/http';
import { PlaceOrderService } from '../services/place-order.service';
import { ProductsService } from '../services/products.service';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { finalize, Subject, takeUntil } from 'rxjs';
import { NewOrderService } from '../services/pusher/newOrder';
import { NewInvoiceService } from '../services/pusher/newInvoice';
import { firstValueFrom } from 'rxjs';

@Component({
  selector: 'app-orders-card',
  imports: [RouterLink, RouterLinkActive, CommonModule, ShowLoaderUntilPageLoadedDirective],
  templateUrl: './orders-card.component.html',
  styleUrl: './orders-card.component.css',
})
export class OrdersCardComponent implements OnInit, OnDestroy {
  pills: any[] = [];
  uniqueStatuses: string[] = [];
  pillsByStatus: any[] = [];
  countHoldItems: number = 0;
  selectedStatus: any = 0 || 'Delivery';
  // selectedStatusactive: string = 'all';
  orders: any[] = [];
  ordersStatus: string[] = [];
  // selectedOrderStatus: string = 'all';
  filteredOrders: any[] = [];
  storedValueLocalStorage: any;
  cartItems: any;
  selectedItem: any;
  orderId: any;
  cartId: any;
  statusTranslations: { [key: string]: string } = {
    completed: 'مكتمل',
    pending: 'في انتظار الموافقة',
    cancelled: 'ملغي',
    packing: ' يتم تجهيزها',
    readyForPickup: 'جاهز للاستلام',
    on_way: 'في الطريق',
    in_progress: 'يتم تحضير الطلب',
    delivered: 'تم الاستلام',

  };
  @ViewChild('orderTypeFirst') orderTypeFirst!: ElementRef;
  @ViewChild('statusFirst') statusFirst!: ElementRef;
  // showFirstTab: boolean = false;
  private destroy$ = new Subject<void>();
  selectedStatusx!: string;
  filteredOrdersByStatus: any[] = [];
  filteredOrdersByOrderType: any[] = [];
  selectedOrderType: any = 'dine-in'
  orderTypeCounts: any = {
    Takeaway: 0,
    Delivery: 0,
    'dine-in': 0,
  };
  orderTypeStaticCounts: any = {
    Takeaway: 0,
    Delivery: 0,
    'dine-in': 0,
  };


  error: string | undefined;
  falseMessage: string | undefined;
  isLoading: boolean | undefined;
  couponCode: null | undefined;
  additionalNote: string | undefined;
  selectedCourier: any;
  appliedCoupon: null | undefined;
  discountAmount: number | undefined;
  // orders: number=0
  hover: boolean = false;

  isAllLoading: boolean = true;
  isFilterdFromClientSide: boolean = true;
  constructor(
    private pillRequestService: PillsService,
    private ordersService: OrdersService,
    private router: Router,
    private route: ActivatedRoute,
    private cdr: ChangeDetectorRef,
    private plaseOrderService: PlaceOrderService,
    private productsService: ProductsService,
    private cdRef: ChangeDetectorRef,
    private newOrder: NewOrderService, private newInvoice: NewInvoiceService
  ) { }

  ngOnInit(): void {
    console.log('oooo');

    this.selectedOrderType = 'dine-in';
    this.listenToNewOrder()
    this.selectOrderType('dine-in');
    this.loadCartItems();
    this.fetchPillsData();
    this.fetchOrdersData();
    this.applyFilters();
    this.fetchOrderDetails();
    this.route.paramMap.subscribe({
      next: (params) => {
        this.orderId = params.get('id');
        if (this.orderId) {
          this.fetchOrderDetails();
        }
      },
      error: (err) => {
        this.error = 'Error retrieving order ID from route.';
        console.error(this.error, err);
      },
    });

    this.calculateOrderTypeCounts();
    this.calculateOrderStaticTypeCounts()
  }

  listenToNewOrder() {
    this.newOrder.listenToNewOrder('from home');
    this.newOrder.orderAdded$
      .pipe(takeUntil(this.destroy$)).subscribe((newOrder) => {
        console.log('new order in home ', this.selectedOrderType, newOrder);
        this.orders = [newOrder.data.Order.order_details, ...this.orders]
        // increase number of orderCounts for new order
        this.orderTypeCounts[newOrder.data.Order.order_details.order_type]++;
        if (newOrder.data.Order.order_details.order_type.toLowerCase() == this.selectedOrderType.toLowerCase()) {
          this.filteredOrdersByOrderType = [...this.orders]
        }
        const invoiceStatus = newOrder.data.Order.invoice.invoice_print_status;
        // get invoices
        const invoice = {
          "order_id": newOrder.data.Order.orderId,
          "order_type": newOrder.data.Order.order_details.order_type,
          "order_number": newOrder.data.Order.order_details.order_number,
          "order_items_count": newOrder.data.Order.order_details.order_items_count,
          "order_time": newOrder.data.Order.invoice.order_time,
          "invoice_number": newOrder.data.Order.invoice.invoice_number,
          "invoice_print_status": invoiceStatus


        }
        this.pillsByStatus.forEach(pill => {
          console.log();

          if (pill.status == invoiceStatus) {
            pill.pills.unshift(invoice)
          }

        });

      })
  }

  ngDoCheck() {

    const newStoredValue = localStorage.getItem('savedOrders');

    if (newStoredValue) {
      const parsedValue = JSON.parse(newStoredValue);

      if (JSON.stringify(parsedValue) !== JSON.stringify(this.cartItems)) {
        this.cartItems = parsedValue;
        // console.log(this.cartItems,'cartItems')
        this.applyFilters();
        this.cdr.detectChanges();
      }
    }
  }

  ngAfterViewInit(): void {
    setTimeout(() => {
      if (this.orderTypeFirst?.nativeElement) {
        this.orderTypeFirst.nativeElement.click();
      }
      if (this.statusFirst?.nativeElement) {
        this.statusFirst.nativeElement.click();
      }
    }, 100);
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


  // async fetchPillsData(): Promise<void> {
  //   try {
  //     const response = await firstValueFrom(this.pillRequestService.getPills());
  //     if (!response?.status) {
  //       this.pills = [];
  //       this.pillsByStatus = [];
  //       return;
  //     }

  //     const normalize = (s: unknown) => (s ?? '').toString().trim().toLowerCase();

  //     // ✅ Normalize values
  //     this.pills = (response.data?.invoices ?? []).map((p: any) => ({
  //       ...p,
  //       invoice_print_status: normalize(p.invoice_print_status),
  //       invoice_type: normalize(p.invoice_type),
  //     }))
  //       // ✅ Exclude cancelled invoices
  //       // .filter((p: { invoice_print_status: string; }) => p.invoice_print_status !== 'cancelled');

  //     const allStatuses = ['hold', 'urgent', 'done', 'returned', 'cancelled'];

  //     // ✅ Normal statuses (excluding credit_note ones)
  //     const normalStatuses = [...new Set(
  //       this.pills
  //         .filter(p => p.invoice_type !== 'credit_note')
  //         .map((p) => p.invoice_print_status)
  //         .filter(Boolean)
  //     )];

  //     // ✅ Add "returned" tab for credit_note invoices
  //     const mergedStatuses = [...new Set([...allStatuses, ...normalStatuses])];

  //     this.pillsByStatus = mergedStatuses.map((status) => {
  //       if (status === 'returned') {
  //         return {
  //           status,
  //           pills: this.pills.filter((p) => p.invoice_type === 'credit_note'),
  //         };
  //       }
  //       return {
  //         status,
  //         pills: this.pills.filter(
  //           (p) => p.invoice_type !== 'credit_note' && p.invoice_print_status === status
  //         ),
  //       };
  //     }).filter((g) => g.pills.length > 0);
  //     const returnedGroup = this.pillsByStatus.find(g => g.status === 'returned');
  //     if (returnedGroup) {
  //       console.log('Returned invoices:', returnedGroup.pills);
  //     }
  //     // ✅ Default tab: "hold" if exists, else first
  //     const preferred = this.pillsByStatus.findIndex((g) => g.status === 'hold');
  //     this.selectedStatus = preferred >= 0 ? preferred : 0;
  //   } catch (e) {
  //     console.error('fetchPillsData error:', e);
  //     this.pills = [];
  //     this.pillsByStatus = [];
  //   }
  // }
  async fetchPillsData(): Promise<void> {
    try {
      const response = await firstValueFrom(this.pillRequestService.getPills());
      if (!response?.status) {
        this.pills = [];
        this.pillsByStatus = [];
        return;
      }

      const normalize = (s: unknown) => (s ?? '').toString().trim().toLowerCase();

    // ✅ Normalize values + filter unpaid + unprinted
    this.pills = (response.data?.invoices ?? [])
      .map((p: any) => ({
        ...p,
        invoice_print_status: normalize(p.invoice_print_status),
        invoice_type: normalize(p.invoice_type),
        payment_method: normalize(p.payment_method),
      }))
    .filter((pill: any) => !(pill.order_items_count === 0 && pill.payment_status === "unpaid"))
    const allStatuses = ['hold', 'urgent', 'done', 'returned', 'cancelled'];

      // ✅ Normal statuses (excluding credit_note ones)
      const normalStatuses = [...new Set(
        this.pills
          .filter(p => p.invoice_type !== 'credit_note')
          .map((p) => p.invoice_print_status)
          .filter(Boolean)
      )];

      // ✅ Add "returned" tab for credit_note invoices
      const mergedStatuses = [...new Set([...allStatuses, ...normalStatuses])];


      this.pillsByStatus = mergedStatuses.map((status) => {
        if (status === 'returned') {
          return {
            status,
            pills: this.pills.filter((p) => p.invoice_type === 'credit_note'),
          };
        }
        return {
          status,
          pills: this.pills.filter(
            (p) => p.invoice_type !== 'credit_note' && p.invoice_print_status === status
          ),
        };
      }).filter((g) => g.pills.length > 0);

      const returnedGroup = this.pillsByStatus.find(g => g.status === 'returned');
      if (returnedGroup) {
        console.log('Returned invoices:', returnedGroup.pills);
      }

      // ✅ Default tab: "hold" if exists, else first
      const preferred = this.pillsByStatus.findIndex((g) => g.status === 'hold');
      this.selectedStatus = preferred >= 0 ? preferred : 0;
    } catch (e) {
      console.error('fetchPillsData error:', e);
      this.pills = [];
      this.pillsByStatus = [];
    }
  }

  fetchOrdersData(): void {
    this.isAllLoading = false;
    this.ordersService.getOrders().pipe(
      finalize(() => {
        this.isAllLoading = true
        console.log('Request finalized');
      })
    )
      .subscribe({
        next: (response) => {
          if (response.status) {
            this.orders = response.data.orders;
            // console.log(this.orders = response.data.orders.filter((order: { status: string; }) => order.status === 'delivered')
            // ,'e')

            this.ordersStatus = Array.from(
              new Set(this.orders.map((order) => order.order_type))
            );
            this.filteredOrdersByStatus = this.ordersStatus.map((status) => {
              const filteredOrders = this.orders.filter(
                (order) => order.order_type === status
              );

              return {
                status,
                orderStatus: filteredOrders,
                count: filteredOrders.length,
              };

            });

            this.applyFilters();

            this.calculateOrderTypeCounts();
            this.calculateOrderStaticTypeCounts()
            this.cdr.detectChanges();

            setTimeout(() => {
              if (this.orderTypeFirst) {
                this.statusFirst.nativeElement.click();
              }
            });
          }
        },
        error: (err: any) => {
          console.error('Error fetching orders:', err);
        },
      });
  }

  selectStatusGroup(index: number): void {

    this.selectedStatus = index;
    this.applyFilters();
  }

  selectOrderType(orderType: string) {
    this.selectedOrderType = orderType;
    this.applyFilters();
  }

  selectStatus(status: string): void {
    this.selectedStatusx = status;

    this.applyFilters();
  }

  getTranslatedStatus(status: string): string {
    const statusTranslations: { [key: string]: string } = {
      hold: 'معلقة',
      urgent: 'طارئة',
      done: 'مكتملة',
      returned: 'مرتجعة',
      cancelled: 'ملغية'

    };

    return statusTranslations[status] || status;
  }
  getTranslatedStatusOrder(status: string, orderType?: string): string {

    if (status === 'readyForPickup') {
      if (orderType === 'Takeaway' || orderType === 'Delivery') {
        return 'جاهزة للاستلام';
      } else if (orderType === 'dine-in') {
        return 'جاهزة للتقديم';
      }

    }

    const statusTranslations: { [key: string]: string } = {
      pending: 'بانتظار التحضير',
      in_progress: 'قيد التحضير',
      on_way: 'في الطريق',
      completed: 'مكتملة',
      cancelled: 'ملغية',
      static: 'معلقة',
      "delivered": 'تم الاستلام'
    };

    return statusTranslations[status] || status;
  }

  // applyFilters(): void {

  //   if (!this.orders.length) return;

  //   this.filterOrdersByStatus(this.selectedStatusx);

  //   if (this.selectedStatusx !== 'static') {
  //     if (this.selectedOrderType) {
  //       this.filteredOrdersByOrderType = this.filteredOrdersByStatus.filter(
  //         (order) => order.order_type === this.selectedOrderType
  //       );

  //     } else {
  //       this.filteredOrdersByOrderType = [...this.filteredOrdersByStatus];

  //     }
  //   } else {
  //     if (this.selectedOrderType === 'Delivery') {
  //       this.filteredOrdersByOrderType = this.filteredOrdersByStatus.filter(
  //         (cartItem) => cartItem.type?.trim() === 'Delivery'
  //       );


  //     } else if (this.selectedOrderType === 'dine-in') {
  //       this.filteredOrdersByOrderType = this.filteredOrdersByStatus.filter(
  //         (cartItem) => cartItem.type?.trim() === 'dine-in'
  //       );
  //     } else if (this.selectedOrderType === 'Takeaway') {
  //       this.filteredOrdersByOrderType = this.filteredOrdersByStatus.filter(
  //         (cartItem) => cartItem.type?.trim() === 'Takeaway'
  //       );

  //     }
  //   }
  //   this.calculateOrderTypeCounts()
  //   this.calculateOrderStaticTypeCounts()
  //   this.cdr.detectChanges();
  // }
  applyFilters(): void {
    this.isFilterdFromClientSide = false;

    try {
      if (!this.orders.length) {
        this.isFilterdFromClientSide = true;
        return;
      }

      this.filterOrdersByStatus(this.selectedStatusx);

      if (this.selectedStatusx !== 'static') {
        if (this.selectedOrderType) {
          this.filteredOrdersByOrderType = this.filteredOrdersByStatus.filter(
            (order) => order.order_type === this.selectedOrderType
          );
        } else {
          this.filteredOrdersByOrderType = [...this.filteredOrdersByStatus];
        }
      } else {
        if (this.selectedOrderType === 'Delivery') {
          this.filteredOrdersByOrderType = this.filteredOrdersByStatus.filter(
            (cartItem) => cartItem.type?.trim() === 'Delivery'
          );
        } else if (this.selectedOrderType === 'dine-in') {
          this.filteredOrdersByOrderType = this.filteredOrdersByStatus.filter(
            (cartItem) => cartItem.type?.trim() === 'dine-in'
          );
        } else if (this.selectedOrderType === 'Takeaway') {
          this.filteredOrdersByOrderType = this.filteredOrdersByStatus.filter(
            (cartItem) => cartItem.type?.trim() === 'Takeaway'
          );
        }
      }

      this.calculateOrderTypeCounts();
      this.calculateOrderStaticTypeCounts();
      this.cdr.detectChanges();
    } finally {
      this.isFilterdFromClientSide = true; // ✅ stop loading even if something fails
    }
  }

  translateType(type: string): string {
    switch (type) {
      case 'dine-in':
        return 'فى المطعم';
      case 'Takeaway':
        return ' إستلام';
      case 'Delivery':
        return 'توصيل';

      default:
        return type;
    }
  }
  // This method will calculate the total price for each item and the total price for the order.
  getTotalPrice(items: any[] = []): number {
    return items.reduce((total, item) => {
      const itemTotal = (item.dish_price || 0) * (item.quantity || 0);
      return total + itemTotal;
    }, 0);
  }

  // This method will give the total price for a single item (quantity * price)
  getItemTotalPrice(item: any): number {
    return (item.dish_price || 0) * (item.quantity || 0);
  }



  filterOrdersByStatus(status: string): void {

    if (status === 'all') {
      this.filteredOrdersByStatus = [...this.orders];

    } else if (status === 'static') {
      this.filteredOrdersByStatus = [...this.cartItems];


      if (this.selectedOrderType === 'Delivery') {
        this.filteredOrdersByStatus = this.filteredOrdersByStatus.filter(
          (cartItem) => cartItem.type?.trim() === 'Delivery'
        );


      } else if (this.selectedOrderType === 'dine-in') {
        this.filteredOrdersByStatus = this.filteredOrdersByStatus.filter(
          (cartItem) => cartItem.type?.trim() === 'dine-in'
        );
      } else if (this.selectedOrderType === 'Takeaway') {
        this.filteredOrdersByStatus = this.filteredOrdersByStatus.filter(
          (cartItem) => cartItem.type?.trim() === 'Takeaway'
        );

      }


    }
    this.calculateOrderTypeCounts()
    this.calculateOrderStaticTypeCounts()
  }

  calculateOrderTypeCounts(): void {
    if (!this.orders || this.orders.length === 0) {
      this.orderTypeCounts = { Takeaway: 0, Delivery: 0, 'dine-in': 0 };
    } else {
      this.orderTypeCounts = {
        Takeaway: this.orders.filter((order) => order.order_type === 'Takeaway')
          .length,
        Delivery: this.orders.filter((order) => order.order_type === 'Delivery')
          .length,
        'dine-in': this.orders.filter((order) => order.order_type === 'dine-in')
          .length,
      };
    }

    // console.log('Order Type Counts:', this.orderTypeCounts);

    this.cdr.detectChanges();
  }
  calculateOrderStaticTypeCounts() {

    if (!this.cartItems || this.cartItems.length === 0) {
      this.orderTypeStaticCounts = { Takeaway: 0, Delivery: 0, 'dine-in': 0 };
    } else {
      this.orderTypeStaticCounts = {
        Takeaway: this.cartItems.filter((order: { type: string; }) => order.type === 'Takeaway')
          .length,
        Delivery: this.cartItems.filter((order: { type: string; }) => order.type === 'Delivery')
          .length,
        'dine-in': this.cartItems.filter((order: { type: string; }) => order.type === 'dine-in')
          .length,
      };
    }

    this.cdr.detectChanges();
  }
  getUniqueStatuses(): string[] {
    const uniqueStatuses = new Set<string>();

    this.orders.forEach((order) => {
      uniqueStatuses.add(order.status);
    });

    return [...uniqueStatuses].filter(
      (status) => status === 'all' || status === 'pending'
    );
  }

  getUniqueOrderTypes(): string[] {
    const uniqueOrderTypes = new Set<string>();

    this.filteredOrdersByStatus.forEach((order) => {
      uniqueOrderTypes.add(order.order_type);
    });

    return Array.from(uniqueOrderTypes);
  }
  fetchOrderDetails(): void {
    this.storedValueLocalStorage = localStorage.getItem('cart');
    if (this.storedValueLocalStorage) {
      this.storedValueLocalStorage = JSON.parse(this.storedValueLocalStorage);
      this.cartItems = this.storedValueLocalStorage;
      // console.log('Cart items:', this.cartItems);
    } else {
      this.storedValueLocalStorage = [];
    }
  }
  // handleRedirect(item: any): void {
  //   // console.log(this.cartItems, 'cartItems');
  //   if (
  //     this.storedValueLocalStorage &&
  //     this.storedValueLocalStorage.cartItems?.length > 0
  //   ) {
  //   } else {
  //     this.selectedItem = item;
  //   }
  // }
  submitOrder(event: any, orderId: number) {
    event.preventDefault();
    event.stopPropagation();
    console.log('Form submitted!');
    if (!this.cartItems.length) {
      this.falseMessage = '🛒 العربة فارغة، أضف بعض العناصر قبل تنفيذ الطلب.';
      return;
    }

    if (this.selectedOrderType === 'Delivery' && !this.selectedCourier?.id) {
      this.falseMessage = '🚚 يرجى اختيار الطيار للتوصيل.';
      return;
    }

    this.isLoading = true;

    const branchId = Number(localStorage.getItem('branch_id')) || null;
    const tableId = Number(localStorage.getItem('table_id')) || null;
    const addressId = Number(localStorage.getItem('address_id')) || null;
    const authToken = localStorage.getItem('authToken');

    if (!branchId) {
      this.falseMessage = '❌ فشل تحديد الفرع. الرجاء إعادة تسجيل الدخول.';
      this.isLoading = false;
      return;
    }

    if (!authToken) {
      this.falseMessage =
        '❌ فشل التحقق من الهوية. الرجاء تسجيل الدخول مجددًا.';
      this.isLoading = false;
      return;
    }

    const orderData: any = {
      coupon_code: this.couponCode || null,
      type: this.selectedOrderType,
      branch_id: branchId,
      payment_method: 'cash',
      payment_status: 'paid',
      cashier_machine_id: 1,
      note: this.additionalNote || '',
      items: this.cartItems
        .map(
          (item: {
            dish: { id: any };
            quantity: any;
            sizes: any[];
            note: any;
            addon_categories: { id: any; addons: any[] }[];
          }) => ({
            dish_id: item.dish?.id || null,
            quantity: item.quantity || 1,
            sizeId:
              item.sizes?.find(
                (size: { default_size: any }) => size.default_size
              )?.id || null, // Extract size ID
            note: item.note || '',
            addon_categories:
              item.addon_categories?.map(
                (category: { id: any; addons: any[] }) => ({
                  id: category.id,
                  addon: category.addons?.map((addon) => addon.id) || [],
                })
              ) || [],
          })
        )
        .filter((item: { dish_id: any }) => item.dish_id),
    };

    if (!orderData.items.length) {
      this.falseMessage = '❌ لا يمكن تقديم الطلب بدون عناصر صالحة.';
      this.isLoading = false;
      return;
    }

    if (
      this.selectedOrderType === 'dine-in' ||
      this.selectedOrderType === 'في المطعم'
    ) {
      if (!tableId) {
        this.falseMessage = '❌ يرجى اختيار طاولة.';
        this.isLoading = false;
        return;
      }
      orderData.table_id = tableId;
    }

    if (
      this.selectedOrderType === 'Delivery' ||
      this.selectedOrderType === 'توصيل'
    ) {
      if (!addressId || !this.selectedCourier?.id) {
        this.falseMessage = '❌ يرجى اختيار عنوان التوصيل والطيار.';
        this.isLoading = false;
        return;
      }
      orderData.address_id = addressId;
      orderData.delivery_id = this.selectedCourier.id;
    }

    console.log('📦 Final Order Data:', JSON.stringify(orderData, null, 2));

    const headers = new HttpHeaders({
      Authorization: `Bearer ${authToken}`,
      'Accept-Language': 'ar',
    });

    this.plaseOrderService.placeOrder(orderData).subscribe({
      next: (response): void => {
        console.log('✅ API Response:', response);

        if (!response.status) {
          this.falseMessage = `❌ ${response.message || 'حدث خطأ أثناء تقديم الطلب'
            }`;
          this.isLoading = false;
          return;
        }

        this.clearCart();
        console.log('✅ Order placed successfully.');
        this.falseMessage = '✅ تم تقديم الطلب بنجاح!';
        this.isLoading = false;
      },
      error: (error) => {
        // console.error(' API Error:', error.error);

        if (error.error?.message) {
          this.falseMessage = ` ${error.error.message}`;
        } else if (error.error?.errors) {
          const errorMessages = Object.values(error.error.errors)
            .flat()
            .join('\n');
          this.falseMessage = `${errorMessages}`;
        } else {
          this.falseMessage = ' حدث خطأ غير متوقع، يرجى المحاولة لاحقًا.';
        }

        this.isLoading = false;
      },
      complete: () => {
        this.isLoading = false;
      },
    });

    console.log(orderData);

  }

  getRedirectUrl(order: any): string {

    if (order.order_type === 'Delivery') {
      return '/cart/' + order.order_id;
    } else {
      return '/order-details/' + order.order_id;
    }
  }
  clearCart(): void {
    this.productsService.clearCart();
    this.cartItems = [];
    this.appliedCoupon = null;
    this.discountAmount = 0;
    this.removeCouponFromLocalStorage();
    // this.saveCart();
    this.clearSelectedCourier();
    this.clearOrderType();
  }
  removeCouponFromLocalStorage() {
    localStorage.removeItem('appliedCoupon');
  }
  // saveCart() {
  //   localStorage.setItem('cart', JSON.stringify(this.cartItems));
  // }
  clearSelectedCourier() {
    this.selectedCourier = null;
    localStorage.removeItem('selectedCourier');
  }
  clearOrderType() {
    this.selectedOrderType = '';
    localStorage.removeItem('selectedOrderType');
  }
  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
    this.newOrder.stopListening();

  }
}
