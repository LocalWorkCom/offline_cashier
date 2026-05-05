import { ChangeDetectorRef, Component, Input, OnInit } from '@angular/core';
import { ProductsService } from '../services/products.service';
import { PlaceOrderService } from '../services/place-order.service';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { catchError, Observable, of, tap } from 'rxjs';
import { ActivatedRoute, RouterLink, RouterLinkActive } from '@angular/router';
import { CommonModule } from '@angular/common';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { CourierModalComponent } from '../courier-modal/courier-modal.component';
import { CartItemsModalComponent } from '../cart-items-modal/cart-items-modal.component';
import { Router } from '@angular/router';
import { OrderListDetailsService } from '../services/order-list-details.service';
import { baseUrl } from '../environment';
import { finalize } from 'rxjs/operators';
import { IndexeddbService } from '../services/indexeddb.service';
import { EditOrderModalComponent } from '../edit-order-modal/edit-order-modal.component';
import { TablesService } from '../services/tables.service';

declare var bootstrap: any;

@Component({
  selector: 'app-cart',
  imports: [FormsModule, CommonModule, EditOrderModalComponent],
  standalone: true,
  templateUrl: './cart.component.html',
  styleUrl: './cart.component.css',
})
export class CartComponent {
  @Input() orderTypeFromRoute: string = '';
  cartItems: any[] = [];
  currencySymbol = localStorage.getItem('currency_symbol');
  isLoading: boolean = false;
  originalTotal: any;
  selectedOrderType: string = '';
  orderType: string = ''; // User-selected order type
  serviceFeeDisplay: string = ''; // Display service fee correctly
  cartId: any;
  cartItemsDetailed: any;
  cartItemsLocal: any;
  error: string | undefined;
  paymenMethod: any;
  deliveryData: any;
  deliveryFees: any;
  orderDetails: any;
  orderSummary: any | null = null;
  orderItems: any;
  orderTypeById: any
  cartItemLocalStorage: any
  cartItemsLocalArray: any;
  address: string = '';
  addressPhone: string = '';
  FormDataDetails: any;
  selectedOrder: any;
  allOrderDetails: any;
  selectedStatus: any;

  // Change Driver modal properties
  driversList: any[] = [];
  selectedDriverId: number | null = null;
  isLoadingDrivers: boolean = false;
  isSavingDriver: boolean = false;
  changeDriverModal: any;

  /** Loading state for cancel-item request (set to order_detail_id while loading). */
  removeItemLoading: number | null = null;
  /** Delete item confirmation modal */
  itemToDelete: any = null;
  deleteItemErrMsg: string = '';

  /** Change Order Type modal properties */
  currentOrderForTypeChange: any = null;
  selectedNewOrderType: string = '';
  selectedTableIdForTypeChange: string = '';
  availableTables: any[] = [];
  isChangeTypeSubmitting: boolean = false;
  errorMessage: string = '';
  status_order: any;

  constructor(
    private productsService: ProductsService,
    private http: HttpClient,
    private plaseOrderService: PlaceOrderService,
    private modalService: NgbModal,
    private router: Router,
    private route: ActivatedRoute,
    private orderListById: OrderListDetailsService,
    private dbService: IndexeddbService,
    private tablesService: TablesService,
    private cdr: ChangeDetectorRef
  ) {
    const navigation = this.router.getCurrentNavigation();
    this.orderDetails = navigation?.extras.state?.['orderData'];

    if (!this.orderDetails) {
      const savedOrderDetails = localStorage.getItem('orderData');
      if (savedOrderDetails) {
        this.orderDetails = JSON.parse(savedOrderDetails);
      }
    }
  }

  isOrderSummaryLoaded = false;
  ngOnInit(): void {
    this.loadSelectedCourier();
    if (this.orderSummary) {
      this.selectedOrderType = 'Delivery';
      this.selectedOrderType = 'توصيل';

    }

    // تحقق من orderDetails ثم قم بتحديث selectedOrderType
    if (this.orderDetails && this.orderDetails.order_details) {
      this.orderTypeById = this.orderDetails.order_details.order_type;
      console.log("نوع الطلب:", this.orderTypeById);

      this.selectOrderType(this.selectedOrderType);
    }

    this.route.paramMap.subscribe((params) => {
      this.cartId = params.get('id');

      if (this.cartId) {
        this.fetchOrderDetails();
        this.loadSelectedCourier();

      }
    });

  }

  confirmedStatus: string = ''; // The final status shown to the user
  message: string = '';
  selectStatus(status: string): void {
    this.selectedStatus = status;
  }

  confirmStatus(): void {
    if (this.selectedStatus) {
      this.confirmedStatus = this.selectedStatus;

      // ✅ Send to API
      this.updateOrderStatus(this.selectedStatus);
    }
  }
  updateOrderStatus(status: string): void {
    const authToken = localStorage.getItem('authToken');
    if (!authToken) {
      console.error('No auth token found');
      return;
    }

    const headers = {
      Authorization: `Bearer ${authToken}`
    };

    this.http.post<any>(
       `${baseUrl}api/orders/change-status/${this.cartId}`,
      { status },
      { headers }
    ).subscribe({
      next: (response) => {
        this.message = response.message;
        if (response?.status && response?.data?.new_status) {
          this.confirmedStatus = response.data.new_status;

          // Optional: Clear success message after 3 seconds
          setTimeout(() => this.message = '', 3000);
        }
      },
      error: (err) => {
        console.error('Error updating order status:', err);
      }
    });
  }

  get displayStatus(): string {
    if (this.confirmedStatus === 'delivered') {
      return 'تم التوصيل';
    } else if (this.confirmedStatus === 'on_way') {
      return 'في الطريق';
    }
    return '';
  }
  storedCourier: { id: number, name: string } | null = null;
  selectedCourier: { id: number, name: string } | null = null;
  // loadSelectedCourier() {
  //   const storedCourier = localStorage.getItem('selectedCourier');
  //   if (storedCourier) {
  //     this.selectedCourier = JSON.parse(storedCourier); // Load selected courier
  //   }
  // }

  loadSelectedCourier() {
    if (!this.cartId) return;

    const key = `selectedCourier_${this.cartId}`;
    const storedCourier = localStorage.getItem(key);

    if (storedCourier) {
      this.selectedCourier = JSON.parse(storedCourier);
    } else {
      this.selectedCourier = null;
    }
  }


  getTotalItemCount(): number {
    return this.cartItems.reduce((total: any, item: { quantity: any; }) => total + item.quantity, 0);
  }


  fetchOrderDetails(): void {
    console.log(this.cartId)
    this.isLoading = true;
    this.error = '';
    this.orderListById.getOrderById(this.cartId).subscribe({
      next: (response: any) => {
        if (
          response &&
          response.data &&
          response.data.orderDetails &&
          response.data.orderDetails.length > 0
        ) {
          this.allOrderDetails = response.data
          const order = response.data.orderDetails[0];
          this.originalTotal = order.original_total;
          this.cartItems = order.order_details
          this.orderSummary = order.order_summary;
          this.currencySymbol = order.currency_symbol;
          this.paymenMethod = order.payment_method;
          this.deliveryData = order?.delivery_data

          this.deliveryFees = this.orderSummary.delivery_fees
          this.selectedOrderType = order.order_type
          this.serviceFeeDisplay = this.orderSummary.service_fees

          this.orderDetails = order;

          this.orderItems = order.order_details;

          this.isLoading = false;

          console.log(this.allOrderDetails, 'paymenMethod');

        } else {
          this.error = 'No order details available.';
          this.isLoading = false;
        }
      },
      error: (error: any) => {
        console.error('Error fetching order details:', error);
        this.error = 'Failed to fetch order details.';
        this.isLoading = false;
      },
    });
  }

  selectOrderType(type: string) {
    this.selectedOrderType = type;
    localStorage.setItem('selectedOrderType', type); // Save to local storage
  }
  // openCouriersModal() {
  //   const modalRef = this.modalService.open(CourierModalComponent, { size: 'md', centered: true });
  //   modalRef.result.then(
  //     (courier: { id: number, name: string }) => {
  //       this.selectedCourier = courier; // Store selected courier
  //       // toqa
  //       localStorage.setItem('selectedCourier', JSON.stringify(courier));
  //       console.log('Selected courier:', courier);
  //     },
  //     (reason) => {
  //       console.log('Modal dismissed:', reason);
  //     }
  //   );
  // }
  // openCouriersModal(orderId: number) {
  //   // Get saved courier from localStorage (if any)
  //   const savedCourier = localStorage.getItem('selectedCourier');
  //   const selectedCourier = savedCourier ? JSON.parse(savedCourier) : null;

  //   // Open modal and pass data
  //   const modalRef = this.modalService.open(CourierModalComponent, {
  //     size: 'md',
  //     centered: true
  //   });

  //   modalRef.componentInstance.selectedCourierId = selectedCourier?.id ?? null;
  //   modalRef.componentInstance.orderId = this.cartId;

  //   // Handle modal result
  //   modalRef.result.then(
  //     (courier: { id: number, name: string }) => {
  //       this.selectedCourier = courier;
  //       localStorage.setItem('selectedCourier', JSON.stringify(courier));

  //       // ✅ Send courier + order ID to API
  //       this.http.post('https://erpsystem.testdomain100.online/api/orders/cashier/update/order', {
  //         delivery_id: courier.id,
  //         order_id: orderId
  //       }).subscribe({
  //         next: (res) => {
  //           console.log('Courier assigned successfully:', res);
  //           // Optional: Show success message
  //         },
  //         error: (err) => {
  //           console.error('Failed to assign courier:', err);
  //           // Optional: Show error to user
  //         }
  //       });
  //     },
  //     (reason) => {
  //       console.log('Modal dismissed:', reason);
  //     }
  //   );
  // }
  // openCouriersModal(orderId: number) {
  //   const savedCourier = localStorage.getItem('selectedCourier');
  //   const selectedCourier = savedCourier ? JSON.parse(savedCourier) : null;

  //   const modalRef = this.modalService.open(CourierModalComponent, {
  //     size: 'md',
  //     centered: true
  //   });

  //   modalRef.componentInstance.selectedCourierId = selectedCourier?.id ?? null;
  //   modalRef.componentInstance.orderId = orderId;

  //   modalRef.result.then(
  //     (courier: { id: number, name: string }) => {
  //       this.selectedCourier = courier;
  //       localStorage.setItem('selectedCourier', JSON.stringify(courier));

  //       // 🔐 Get token and set headers
  //       const token = localStorage.getItem('authToken');
  //       const headers = {
  //         'Authorization': `Bearer ${token}`,
  //         'Content-Type': 'application/json'
  //       };
  //       // 🚀 Send courier + order ID to API with token

  //        this.http.post('https://erpsystem.testdomain100.online/api/orders/cashier/update/order',{
  //       //this.http.post(' https://alkoot-restaurant.com/api/orders/cashier/update/order' , {
  //         delivery_id: courier.id,
  //         order_id: orderId
  //       }, { headers }).subscribe({
  //         next: (res) => {
  //           console.log('Courier assigned successfully:', res);
  //           // Optional: show success toast
  //         },
  //         error: (err) => {
  //           console.error('Failed to assign courier:', err);
  //           // Optional: show error alert
  //         }
  //       });
  //     },
  //     (reason) => {
  //       console.log('Modal dismissed:', reason);
  //     }
  //   );
  // }
  openCouriersModal(orderId: number) {
    const savedCourier = localStorage.getItem(`selectedCourier_${orderId}`);
    const selectedCourier = savedCourier ? JSON.parse(savedCourier) : null;

    const modalRef = this.modalService.open(CourierModalComponent, {
      size: 'md',
      centered: true
    });

    modalRef.componentInstance.selectedCourierId = selectedCourier?.id ?? null;
    modalRef.componentInstance.orderId = orderId;

    modalRef.result.then(
      (courier: { id: number, name: string }) => {
        this.selectedCourier = courier;
        localStorage.setItem(`selectedCourier_${orderId}`, JSON.stringify(courier));

        const token = localStorage.getItem('authToken');
        const headers = {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        };
        // 🚀 Send courier + order ID to API with token

          this.http.post(`${baseUrl}api/orders/update-delivery-driver`,{

          delivery_id: courier.id,
          order_id: orderId
        }, { headers }).subscribe({
          next: (res) => console.log('Courier assigned successfully:', res),
          error: (err) => console.error('Failed to assign courier:', err)
        });
      },
      (reason) => {
        console.log('Modal dismissed:', reason);
      }
    );
  }

  // === Change Driver Modal Methods ===
  openChangeDriverModal() {
    this.isLoadingDrivers = true;
    this.selectedDriverId = null;
    this.driversList = [];

    // Open modal
    const modalElement = document.getElementById('changeDriverModal');
    if (modalElement) {
      this.changeDriverModal = new bootstrap.Modal(modalElement);
      this.changeDriverModal.show();
    }

    // Fetch drivers
    this.plaseOrderService.getCouriers().subscribe({
      next: (data: any) => {
        this.driversList = data.data || [];
        this.isLoadingDrivers = false;
      },
      error: (err: any) => {
        console.error('Error fetching drivers:', err);
        this.isLoadingDrivers = false;
      }
    });
  }

  saveDriverChange() {
    if (!this.selectedDriverId || !this.cartId) return;

    this.isSavingDriver = true;
    const token = localStorage.getItem('authToken');
    const headers = {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    };

    this.http.post(`${baseUrl}api/orders/update-delivery-driver`, {
      delivery_id: this.selectedDriverId,
      order_id: this.cartId
    }, { headers }).subscribe({
      next: (res: any) => {
        console.log('Driver changed successfully:', res);

        // Update the UI with the new driver
        const selectedDriver = this.driversList.find(d => d.id === this.selectedDriverId);
        if (selectedDriver) {
          const driverName = `${selectedDriver.first_name} ${selectedDriver.last_name}`;
          this.selectedCourier = {
            id: this.selectedDriverId!,
            name: driverName
          };
          localStorage.setItem(`selectedCourier_${this.cartId}`, JSON.stringify(this.selectedCourier));

          // Also update deliveryData if it exists
          if (this.deliveryData) {
            this.deliveryData.delivery_name = driverName;
          }
        }

        this.isSavingDriver = false;
        this.cdr.detectChanges();

        // Close modal
        if (this.changeDriverModal) {
          this.changeDriverModal.hide();
        }
      },
      error: (err: any) => {
        console.error('Failed to change driver:', err);
        this.isSavingDriver = false;
      }
    });
  }

  // === Item Action Methods ===

  /** Whether this item is cancelled (display in yellow, no edit/delete). */
  isItemCancelled(item: any): boolean {
    const status = (item?.dish_status ?? item?.status ?? '').toString().toLowerCase();
    return status === 'cancel' || status === 'cancelled';
  }

  /** Whether to show Cancel/Modify item buttons for this line. */
  canShowItemActions(item: any): boolean {
    if (!this.orderDetails || this.isOrderPaid()) return false;
    const d = this.orderDetails;
    if (d.status === 'cancelled' || d.status === 'cancel') return false;
    const status = item?.dish_status;
    return status === 'pending' || status === 'inprogress';
  }

  /** Cancel a single item (partial cancel). */
  cancelItem(item: any, onDone?: () => void): void {
    const detailId = item?.id ?? item?.order_detail_id;
    if (detailId == null || !this.cartId) return;

    this.removeItemLoading = detailId;
    this.deleteItemErrMsg = '';
    const url = `${baseUrl}api/orders/cashier/request-cancel`;
    const token = localStorage.getItem('authToken');
    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });
    const quantity = Number(item?.quantity) || 1;
    const body = {
      order_id: this.cartId,
      items: [{ item_id: detailId, quantity }],
      type: 'partial',
      reason: 'cashier reason',
      flag: 'cancel',
    };

    if (this.dbService) {
      this.dbService.saveOrderToPrintkitchen(this.cartId, 'cancel').then(() => {}).catch(() => {});
    }

    this.http.post(url, body, { headers }).pipe(
      finalize(() => {
        this.removeItemLoading = null;
        onDone?.();
      })
    ).subscribe({
      next: (res: any) => {
        this.message = res?.message || 'تم حذف الصنف بنجاح';
        setTimeout(() => { this.message = ''; }, 2000);
        this.fetchOrderDetails();
      },
      error: (err) => {
        this.error = err?.error?.message || 'فشل حذف الصنف';
        this.deleteItemErrMsg = err?.error?.message || 'فشل حذف الصنف';
        setTimeout(() => { this.error = ''; }, 3000);
        this.fetchOrderDetails();
      },
    });
  }

  /** Open delete-item confirmation modal. */
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

  /** Confirm delete from modal. */
  confirmDeleteItem(): void {
    if (!this.itemToDelete) return;
    this.itemToDelete.dish_status = 'cancel';
    this.cancelItem(this.itemToDelete, () => this.hideDeleteItemModal());
  }

  /** Open edit-item modal. */
  openEditModalFromDetails(item: any): void {
    const detailId = item?.order_detail_id ?? item?.id;
    if (detailId == null || !this.cartId) return;

    const hasExtraData = item.size || (item.addons && item.addons.length > 0);
    const modalSize = hasExtraData ? 'lg' : 'md';

    const editModal = this.modalService.open(EditOrderModalComponent, {
      size: modalSize,
      centered: true,
    });
    editModal.componentInstance.itemId = detailId;

    if (this.dbService) {
      this.dbService.saveOrderToPrintkitchen(this.cartId, 'edit').then(() => {}).catch(() => {});
    }

    editModal.result.then(
      (result) => {
        if (result) {
          this.message = 'تم تحديث الطلب بنجاح';
          setTimeout(() => { this.message = ''; }, 2000);
          this.fetchOrderDetails();
        }
      },
      () => {}
    );
  }

  // === Order Action Methods (from OrderDetails) ===

  isOrderPaid(): boolean {
    const d = this.orderDetails;
    if (!d) return false;
    const paymentStatus = d.payment_status ?? d.transactions?.[0]?.payment_status;
    return paymentStatus === 'paid';
  }

  /** Whether to show the order actions card (unpaid, pending, not talabat). */
  canShowOrderActions(): boolean {
    const d = this.orderDetails;
    if (!d || this.isOrderPaid()) return false;
    // console.log('CartComponent Order Status:', d.status, 'Payment Status:', d.payment_status, 'Type:', d.order_type);

    if (d.status === 'cancelled' || d.status === 'cancel') return false;
    if (d.order_type === 'talabat') return false;

    return true;
  }

  isDineIn(): boolean {
    return this.orderDetails?.order_type === 'dine-in';
  }

  cancelOrder(): void {
    if (!this.cartId) return;

    const cancelUrl = `${baseUrl}api/orders/cashier/request-cancel`;
    const token = localStorage.getItem('authToken');
    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });
    const body = {
      order_id: this.cartId,
      type: "full",
      items: this.cartItems,
      reason: "fff",
    };

    this.http.post(cancelUrl, body, { headers }).subscribe({
      next: (response: any) => {
        console.log('Order cancelled successfully:', response);
        this.message = response.message;
        this.status_order = response.status;
          // ✅ تحديث لحظى لحالة الطلب فى الموديل المعروض
          setTimeout(() => {
            const id = this.cartId;
            // نروح لصفحة وهمية بدون ما نغيّر الـ URL فعليًا
            this.router.navigateByUrl('/', { skipLocationChange: true }).then(() => {
              // نرجع تانى لنفس صفحة التفاصيل مع refresh=true
              this.router.navigate(
                ['/order-details', id],
                { queryParams: { refresh: 'true' } }
              );
            });
          }, 700);
        setTimeout(() => {
          this.message = '';
        }, 2000);
        // this.fetchOrderDetails();
      },
      error: (error) => {
        console.error('Failed to cancel order:', error);
      },
    });
  }

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
        order_id: this.cartId,
        order_number: this.orderSummary?.order_number ?? this.orderDetails?.order_number ?? this.cartId,
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
      const confirmEl = document.getElementById('confirmChangeOrderTypeModalDetails');
      if (confirmEl) {
        const inst = bootstrap.Modal.getInstance(confirmEl);
        inst?.hide();
      }
      this.router.navigate(['/orders'], { queryParams: { openOrder: this.cartId, action: 'changeType' } });
      return;
    }
    this.submitChangeOrderTypeDetails();
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

    this.http.post(`${baseUrl}api/orders/changeOrderType`, body, { headers }).subscribe({
      next: (res: any) => {
        this.isChangeTypeSubmitting = false;
        const confirmEl = document.getElementById('confirmChangeOrderTypeModalDetails');
        if (confirmEl) {
          const inst = bootstrap.Modal.getInstance(confirmEl);
          inst?.hide();
        }
        if (res?.status) {
          this.message = res?.message || 'تم تغيير نوع الطلب بنجاح';
          this.status_order = true;
          setTimeout(() => { this.message = ''; }, 3000);
          const id = this.currentOrderForTypeChange.order_details.order_id;
          this.currentOrderForTypeChange = null;
          this.selectedNewOrderType = '';
          this.selectedTableIdForTypeChange = '';
          setTimeout(() => {
            this.router.navigate(
              ['/order-details', id],
              { queryParams: { refresh: 'true' } }
            );
          }, 700);
        } else {
          this.message = (res?.errorData && typeof res.errorData === 'object' && Object.values(res.errorData).flat().filter(Boolean)[0]) || res?.message || 'حدث خطأ أثناء تغيير نوع الطلب';
          setTimeout(() => { this.message = ''; }, 4000);
        }
      },
      error: (err: any) => {
        this.isChangeTypeSubmitting = false;
        this.message = err?.error?.message || err?.error?.errorData || 'حدث خطأ أثناء تغيير نوع الطلب';
        setTimeout(() => { this.message = ''; }, 4000);
      },
    });
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

  getOrderTypeIconClass(type: string | undefined): string {
    if (!type) return 'fa-solid fa-circle';
    const icons: Record<string, string> = {
      'dine-in': 'fa-solid fa-utensils',
      'Takeaway': 'fa-solid fa-bag-shopping',
      'Delivery': 'fa-solid fa-truck',
      'talabat': 'fa-solid fa-store',
    };
    return icons[type] || 'fa-solid fa-circle';
  }

  getBusinessOrderTypeValue(): 'client_meal' | 'staff_meal' | 'charity_meal' | 'hospitality_meal' {
    const d = this.orderDetails;
    const candidates = [
      d?.business_order_type,
      d?.meal_order_type,
      d?.order_type_classification,
      d?.order_purpose_type,
      d?.order_type_business,
      d?.order_type,
      d?.order_details?.order_type,
      d?.order_details?.business_order_type,
      d?.order_details?.meal_order_type,
      d?.order_details?.order_type_classification,
      d?.order_details?.order_purpose_type,
    ];
    const valid = ['client_meal', 'staff_meal', 'charity_meal', 'hospitality_meal'];
    for (const candidate of candidates) {
      const key = String(candidate || '').toLowerCase();
      if (valid.includes(key)) {
        return key as 'client_meal' | 'staff_meal' | 'charity_meal' | 'hospitality_meal';
      }
    }
    return 'client_meal';
  }

  getBusinessOrderTypeShortLabel(value?: string): string {
    const key = String(value || this.getBusinessOrderTypeValue() || '').toLowerCase();
    const labels: Record<string, string> = {
      client_meal: 'عميل',
      staff_meal: 'موظفين',
      charity_meal: 'صدقات',
      hospitality_meal: 'ضيافة',
    };
    return labels[key] || 'عميل';
  }

  private normalizePaymentMethod(method: any): string {
    return String(method ?? '')
      .trim()
      .toLowerCase()
      .replace(/\s+/g, '_');
  }

  getPaymentMethodLabel(method: any): string {
    const m = this.normalizePaymentMethod(method);
    if (m === 'cash') return 'كاش';
    if (['credit', 'visa', 'card', 'mastercard', 'mada'].includes(m)) return 'فيزا';
    if (['deferred', 'later', 'postpaid'].includes(m)) return 'آجل';
    if (m === 'online') return 'أونلاين';
    return 'غير محدد';
  }
}
