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
  totalPrice: number = 100;
  currencySymbol = localStorage.getItem('currency_symbol');
  couponCode: string = '';
  isLoading: boolean = false;
  additionalNote: string = '';
  selectedOrderType: string = '';
  branchData: any = null; // Store branch settings
  orderType: string = ''; // User-selected order type
  serviceFeeDisplay: string = ''; // Display service fee correctly
  cartId: any
  cartItemsDetailed: any;
  storedValueLocalStorage: string | null | undefined;
  cartItemsLocal: any;
  selectedItem: any;
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
  clientName: any;
  tableNumber: string | null = null;
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
    this.loadCart();
    this.loadBranchData();
    this.loadSelectedCourier();
    if (this.orderSummary) {
      this.selectedOrderType = 'Delivery';
      this.selectedOrderType = 'توصيل';

    }

    // تحقق من orderDetails ثم قم بتحديث selectedOrderType
    if (this.orderDetails && this.orderDetails.order_details) {
      this.orderTypeById = this.orderDetails.order_details.order_type;
      console.log("نوع الطلب:", this.orderTypeById);

      // تحويل القيمة إذا لزم الأمر
      if (this.orderTypeById === 'dine-in') {
        this.selectedOrderType = 'في المطعم';
      } else if (this.orderTypeById === 'Takeaway') {
        this.selectedOrderType = 'خارج المطعم';
      } else if (this.orderTypeById === 'Delivery') {
        this.selectedOrderType = 'توصيل';
      }

      // استدعاء الدالة لضبط الاختيار
      this.selectOrderType(this.selectedOrderType);
    }
      this.fetchOrderDetails();

  }

  confirmedStatus: string = ''; // The final status shown to the user
  successMessage: string = '';
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
        `${baseUrl}api/orders/change-orderStatus/${this.cartId}`,
     
      { status },
      { headers }
    ).subscribe({
      next: (response) => {
        if (response?.status && response?.data?.new_status) {
          this.confirmedStatus = response.data.new_status;
          this.successMessage = response.message;

          // Optional: Clear success message after 3 seconds
          setTimeout(() => this.successMessage = '', 3000);
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

  selectedCourier: { id: number, name: string } | null = null;
  loadSelectedCourier() {
    const storedCourier = localStorage.getItem('selectedCourier');
    if (storedCourier) {
      this.selectedCourier = JSON.parse(storedCourier); // Load selected courier
    }
  }


  getTotalItemCount(): number {
    return this.cartItems.reduce((total: any, item: { quantity: any; }) => total + item.quantity, 0);
  }
  getCartDetailsById(cartId: string): void {
    const storedCartData = localStorage.getItem('savedOrders');

    if (storedCartData) {
      try {
        const cartItemsArray = JSON.parse(storedCartData);
        console.log(cartItemsArray, 'test')
        if (Array.isArray(cartItemsArray)) {
          // Find the cart that matches the given cartId
          const foundCart = cartItemsArray.find(
            (cart: any) => cart.orderId === cartId
          );

          if (foundCart) {
            // Set the cart items
            this.cartItems = foundCart.items || [];
            const test = foundCart
            this.paymenMethod = foundCart.payment_method
            this.deliveryData = foundCart.addresses
            this.selectedOrder = foundCart.type;
            // Debugging log
            this.additionalNote = foundCart.note

            // Update the selectedOrderType based on foundCart.type
            this.selectedOrderType = this.selectedOrder;
            // Update localStorage to keep selected order type consistent
            localStorage.setItem('selectedOrderType', this.selectedOrderType);

            // Call selectOrderType to update the UI
            this.selectOrderType(this.selectedOrderType);

            // Load form data for the foundCart if needed

          } else {
            console.warn('No cart found for ID:', cartId);
            this.cartItems = [];
          }
        } else {
          console.error('Stored cart data is not an array:', cartItemsArray);
          this.cartItems = [];
        }
      } catch (error) {
        console.error('Error parsing cart data:', error);
        this.cartItems = [];
      }
    } else {
      console.warn('No cart data found in local storage');
      this.cartItems = [];
    }
  }

  getAddressTypeTranslation(addressType: string): string {
    const translations = {
      villa: 'فيلا',
      apartment: 'شقة',
      office: 'مكتب'
    };

    return translations[addressType as keyof typeof translations] || 'غير معروف';
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

  loadBranchData() {
    const branchDataString = localStorage.getItem('branchData');
    if (branchDataString) {
      this.branchData = JSON.parse(branchDataString);
    }
  }
  loadCart() {

    const storedCart = localStorage.getItem('cart');

    this.cartItems = storedCart ? JSON.parse(storedCart) : [];

    if (this.cartItems.length > 0) {

      const foundCart = this.cartItems.find((cart: any) => cart.orderId === this.cartId);

      if (foundCart) {

        this.selectedOrderType = foundCart.type;
        console.log(this.selectedOrderType, 'tes');
      } else {
        console.warn('Cart not found with the given cartId');
      }
    } else {
      console.warn('No cart items found in localStorage');
    }

  }

  getItemTotal(item: any): number {
    return item.quantity * item.size_price;
  }

  selectOrderType(type: string) {
    this.selectedOrderType = type;
    localStorage.setItem('selectedOrderType', type); // Save to local storage
  }
  openCouriersModal() {
    const modalRef = this.modalService.open(CourierModalComponent, { size: 'md', centered: true });
    modalRef.result.then(
      (courier: { id: number, name: string }) => {
        this.selectedCourier = courier; // Store selected courier
        // toqa
        localStorage.setItem('selectedCourier', JSON.stringify(courier));
        console.log('Selected courier:', courier);
      },
      (reason) => {
        console.log('Modal dismissed:', reason);
      }
    );
  }




  getTotal(): number {
    return this.cartItems.reduce((sum, item) => sum + this.getItemTotal(item), 0);
  }

}
