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

declare var bootstrap: any;

@Component({
  selector: 'app-cart',
  imports: [FormsModule, CommonModule],
  standalone: true,
  templateUrl: './cart.component.html',
  styleUrl: './cart.component.css',
})
export class CartComponent {
  @Input() orderTypeFromRoute: string = '';
  cartItems: any[] = [];
  currencySymbol = localStorage.getItem('currency_symbol');
  isLoading: boolean = false;
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
  selectedStatus: any
  constructor(
    private productsService: ProductsService,
    private http: HttpClient,
    private plaseOrderService: PlaceOrderService,
    private modalService: NgbModal,
    private router: Router,
    private route: ActivatedRoute,
    private orderListById: OrderListDetailsService,
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
       
          this.http.post(`${baseUrl}/api/orders/cashier/update/order`,{
      
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

  // Component that opens the modal


}
