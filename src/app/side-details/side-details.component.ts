import {
  Component,
  Input,
  OnInit,
  ChangeDetectorRef,
  DoCheck,
  AfterViewInit,
  ElementRef,
  ViewChild,
  ɵsetAllowDuplicateNgModuleIdsForTest,
  inject,
  OnDestroy,
} from '@angular/core';
import { ProductsService } from '../services/products.service';
import { PlaceOrderService } from '../services/place-order.service';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { catchError, finalize, firstValueFrom, Observable, of, Subject, tap } from 'rxjs';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { CommonModule, DecimalPipe } from '@angular/common';
import { CourierModalComponent } from '../courier-modal/courier-modal.component';
import { CartItemsModalComponent } from '../cart-items-modal/cart-items-modal.component';
import { v4 as uuidv4 } from 'uuid';
import { PrintedInvoiceService } from '../services/printed-invoice.service';
import { PillDetailsService } from '../services/pill-details.service';
import { ActivatedRoute } from '@angular/router';
import { DatePipe } from '@angular/common';
import { Router } from '@angular/router';
import { log } from 'node:console';
import { stringify } from 'node:querystring';
import { AddAddressService } from '../services/add-address.service';
import { AuthService } from '../services/auth.service';
import { TranslateModule, TranslateService } from '@ngx-translate/core';
import { NgxCountriesDropdownModule } from 'ngx-countries-dropdown';
import { baseUrl } from '../environment';

import { IndexeddbService } from '../services/indexeddb.service';

declare var bootstrap: any;
interface Country {
  code: string;
  flag: string;
}
@Component({
  selector: 'app-side-details',
  imports: [FormsModule, RouterLink, RouterLinkActive, CommonModule, TranslateModule, NgxCountriesDropdownModule
  ],
  providers: [DatePipe],

  templateUrl: './side-details.component.html',
  styleUrl: './side-details.component.css',
})
export class SideDetailsComponent implements OnInit, AfterViewInit, OnDestroy {
  @ViewChild('printedPill') printedPill!: ElementRef;
  @ViewChild('couponModalRef') couponModalRef!: ElementRef;
  translate = inject(TranslateService);
  private destroy$ = new Subject<void>();

  cartItems: any[] = [];
  totalPrice: number = 100;
  isUserLoggedIn: boolean = false;
  validCoupon: boolean = false;
  currencySymbol = localStorage.getItem('currency_symbol');
  errorMessage: string = '';
  successMessage: string = '';
  cancelMessage: string = ''
  falseMessage: string = '';
  tableError: string = '';
  cashiermachine: string = '';
  couponError: string = ''; // Added this line
  trueMessage: string = '';
  isLoading: boolean = false;
  additionalNote: string = '';
  savedNote: string = '';
  selectedOrderType: any;
  selectedPaymentMethod: string = 'cash';
  selectedPaymentStatus: string = 'unpaid';
  appliedCoupon: any;
  branchData: any = null;
  serviceFeeDisplay: string = '';
  selectedCourierId: number | null = null;
  discountAmount: number = 0;
  address: string = '';
  addressPhone: string = '';
  clientName: any;
  tableNumber: string | null = null;
  successModal: any;
  FormDataDetails: any;
  invoices: any;
  pillDetails: any;
  branchDetails: any;
  pillId!: any;
  orderDetails: any[] = [];
  date: string | null = null;
  time: string | null = null;
  invoiceSummary: any;
  addressDetails: any;
  isDeliveryOrder: boolean = false;
  paymentStatus: any = '';
  paymentMethod: any = '';
  trackingStatus: any = '';
  orderNumber: any;
  addresDetails: any;
  isShow: boolean = true;
  note: string = 'لا يوجد';
  cuponValue: any;
  couponType: any;
  cashier_machine_id = Number(localStorage.getItem('cashier_machine_id'));
  createdOrderId!: any;
  showPrices?: boolean;
  test?: boolean;
  orderedId: any;
  addressIdFromResponse: any;
  loading: boolean = false;
  cash_amountt!: number;
  credit_amountt!: number;
  delivery_fees = Number(localStorage.getItem('delivery_fees'));
  cashierLast: any
  cashierFirst: any;
  onholdOrdernote: any;
  table_number: any;
  table_id: any;
  coupon_Code: any;
  couponCode: any;
  couponTitle: any;
  coupon_value: any;
  client: any = localStorage.getItem('client');
  clientPhone: any = localStorage.getItem('clientPhone');
  clientStoredInLocal: any = localStorage.getItem('client');
  clientPhoneStoredInLocal: any = localStorage.getItem('clientPhone');
  referenceNumber: any;
  payment_status: any;
  credit_amount: any;
  cash_amount: any;
  selectedCountry: Country = { code: '+20', flag: "assets/images/egypt.png" };
  dropdownOpen = false;
  loginData = {
    email_or_phone: '',
    password: '',
  };
  countryList: Country[] = [];
  filteredCountries: Country[] = []; // List for filtered countries
  searchTerm: string = ''; // Search term for filtering
  isPasswordVisible: boolean = false;
  EmailOrPhone: boolean = true
  passwordError!: string
  clientError: any;
  constructor(
    private productsService: ProductsService,
    private http: HttpClient,
    private plaseOrderService: PlaceOrderService,
    private modalService: NgbModal,
    private printedInvoiceService: PrintedInvoiceService,
    private pillDetailsService: PillDetailsService,
    private route: ActivatedRoute,
    private orderService: PillDetailsService,
    private cdr: ChangeDetectorRef,
    private datePipe: DatePipe,
    private router: Router,
    private formDataService: AddAddressService,
    public authService: AuthService,
    private dbService: IndexeddbService,
  ) {
    this.cashier_machine_id = this.getCashierMachineId();
  }

  private getCashierMachineId(): number {
    // First try from balanceData
    const balanceData = localStorage.getItem('balanceData');
    if (balanceData) {
      try {
        const parsed = JSON.parse(balanceData);
        if (parsed.cashier_machine_id) {
          return Number(parsed.cashier_machine_id);
        }
      } catch (e) {
        console.error('Error parsing balanceData', e);
      }
    }

    // Fallback to direct localStorage
    const directId = localStorage.getItem('cashier_machine_id');
    return directId ? Number(directId) : 0;
  }
  ngAfterViewInit() {
    this.successModal = new bootstrap.Modal(
      document.getElementById('successModal')
    );

  }

  ngOnInit(): void {
    this.loadCart();
    this.loadBranchData();
    this.restoreCoupon();

    this.loadFormData();
    this.loadOrderType();
    this.loadTableNumber();
    this.fetchCountries();
    this.loadAdditionalNote();


    // this.fetchTrackingStatus();
    // this.getNoteFromLocalStorage();
    this.cashier_machine_id = Number(
      localStorage.getItem('cashier_machine_id')
    );
    const storedData: string | null = localStorage.getItem('balanceData');

    if (storedData !== null) {
      // Safe to parse since storedData is guaranteed to be a string
      const transactionDataFromLocalStorage = JSON.parse(storedData);

      // Access the cashier_machine_id
      this.cashier_machine_id =
        transactionDataFromLocalStorage.cashier_machine_id;

      console.log(this.cashier_machine_id, 'one'); // Output: 1
    } else {
      console.log('No data found in localStorage.');
    }
    const storedItems = localStorage.getItem('cart');
    if (storedItems) {
      this.cartItems = JSON.parse(storedItems);
    }

    const storedFormData = localStorage.getItem('FormDataDetails');
    if (storedFormData) {
      this.FormDataDetails = JSON.parse(storedFormData);
    }

    this.selectedOrderType = localStorage.getItem('selectedOrderType');
    this.finalOrderId = localStorage.getItem('finalOrderId');
    const savedOrder = localStorage.getItem('savedOrders');
    if (savedOrder) {
      const parsedOrders = JSON.parse(savedOrder);
      const targetOrder = parsedOrders.find((order: any) => order.orderId === this.finalOrderId);
      this.onholdOrdernote = targetOrder?.note || '';
      this.table_number = targetOrder?.tableNumber || '';
      this.table_id = targetOrder?.table_id || '';
      this.payment_status = targetOrder?.payment_status || '';
      this.credit_amount = targetOrder?.credit_amount || '';
      this.cash_amount = targetOrder?.cash_amount || '';
      this.coupon_Code = targetOrder?.coupon_code || '';
      this.coupon_value = this.validCoupon ? targetOrder?.invoiceSummary?.coupon_value || '' : null;

      console.log(this.onholdOrdernote);

    }


    this.couponCode = localStorage.getItem('couponCode') || this.coupon_Code || '';
    if (this.couponCode) {
      this.applyCoupon()
    }
    this.selectedPaymentStatus =
      localStorage.getItem('selectedPaymentStatus') || this.payment_status || '';

    const savedCash = localStorage.getItem('cash_amountt');
    const savedCredit = localStorage.getItem('credit_amountt');
    const delivery_fees = localStorage.getItem('delivery_fees');

    this.cash_amountt = Number(savedCash) || this.cash_amount;
    this.credit_amountt = Number(savedCredit) || this.credit_amount;
    this.selectedPaymentStatus = "unpaid"

    const savedCode = localStorage.getItem('selectedCountryCode');
    if (savedCode) {
      this.selectedCountry.code = savedCode;
      this.selectedCountryCode = savedCode; // If you use a separate property
    }

    // Load initial cart from localStorage
    // const storedCart = localStorage.getItem('cart');
    // this.cartItems = storedCart ? JSON.parse(storedCart) : [];

    // Subscribe to cart changes
    this.productsService.cart$.subscribe(cart => {
      this.cartItems = cart;
      this.updateTotalPrice();

      // ✅ If coupon is applied → recheck automatically
      if (this.appliedCoupon) {
        this.applyCoupon();
      }
    });


  }
  finalOrderId: any;
  // toqa
  selectedCountryCode: any;
  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
  loadTableNumber(): void {
    const tableNumber = localStorage.getItem('table_number');
    if (tableNumber) {
      this.tableNumber = JSON.parse(tableNumber);
      localStorage.setItem('selectedOrderType', 'dine-in')
      this.selectedOrderType = 'dine-in';
    }
  }
  loadAdditionalNote(): void {
    const note = localStorage.getItem('additionalNote');
    if (note) {
      this.additionalNote = note;
      this.savedNote = note;
    }
  }
  // ngDoCheck(): void {
  //   this.loadCart();
  // }
  loadBranchData() {
    const branchDataString = localStorage.getItem('branchData');
    if (branchDataString) {
      this.branchData = JSON.parse(branchDataString);
    }
  }

  // loadCart() {
  //   // const storedCart = localStorage.getItem('cart');
  //   const storedCart =  this.dbService.getCartItems();
  //   this.cartItems = storedCart ? JSON.parse(storedCart) : [];
  //   this.updateTotalPrice();
  // }

  loadCart() {
  this.dbService.getCartItems()
    .then((cartItems: any[]) => {
      this.cartItems = cartItems || [];
      this.updateTotalPrice();
      console.log('✅ Cart loaded from IndexedDB:', this.cartItems);
      this.cdr.detectChanges();
    })
    .catch((error: any) => {
      console.error('❌ Error loading cart from IndexedDB:', error);
      this.cartItems = [];
      this.updateTotalPrice();
      this.cdr.detectChanges();
    });

}

  loadFormData() {

    const FormData = localStorage.getItem('form_data');
    if (FormData) {
      this.FormDataDetails = JSON.parse(FormData);
      this.clientName =
        this.FormDataDetails.client_name || 'لم يتم تحديد الإسم';
      if (this.FormDataDetails.address) {
        /*         this.address = "  المبني :  " + this.FormDataDetails.building + " ,  " + this.FormDataDetails.address + " الدور " + this.FormDataDetails.floor_number + " رقم " + this.FormDataDetails.apartment_number || 'لم يتم تحديد العنوان';
         */ this.address =
          this.FormDataDetails.address || 'لم يتم تحديد العنوان';
      }
      this.addressPhone =
        this.FormDataDetails.address_phone || 'لم يتم تحديد رقم الهاتف';
    }
  }

  updateTotalPrice() {
    this.totalPrice = this.cartItems.reduce(
      (total, item) => total + item.quantity * item.price,
      0
    );
    localStorage.setItem('cart', JSON.stringify(this.cartItems)); // Update local storage
  }
  saveCart() {
    // localStorage.setItem('cart', JSON.stringify(this.cartItems));
     return this.dbService.clearCart()
    .then(() => {
      const savePromises = this.cartItems.map(item =>
        this.dbService.addToCart(item)
      );
      return Promise.all(savePromises);
    })
    .then(() => {
      console.log('✅ Cart saved to IndexedDB');
    })
    .catch(error => {
      console.error('❌ Error saving cart to IndexedDB:', error);
      throw error;
    });
  }
  updateTotalPrices() {
    this.cartItems.forEach((item) => {
      item.totalPrice = item.quantity * item.price;
    });
    this.totalPrice = this.cartItems.reduce(
      (total, item) => total + item.totalPrice,
      0
    );
    // this.loadCouponFromLocalStorage()
    this.applyCoupon();


  }
  increaseQuantity(index: number) {
    this.cartItems[index].quantity++;
    this.updateTotalPrices();
    this.saveCart();
  }
  decreaseQuantity(index: number) {
    if (this.cartItems[index].quantity > 1) {
      this.cartItems[index].quantity--;
    } else {
      this.cartItems.splice(index, 1);
      // If the cart is empty, clear coupon, note, and messages
      if (this.cartItems.length === 0) {
        this.appliedCoupon = null;
        this.couponCode = '';
        this.discountAmount = 0;
        this.additionalNote = '';
        this.savedNote = '';
        this.successMessage = ''; // Clear success message
        this.errorMessage = ''; // Clear error message
        this.removeCouponFromLocalStorage();
      }
    }
    this.updateTotalPrices();
    this.saveCart();
  }
  removeItem(productId: number, sizeId?: number): void {
    this.productsService.removeFromCart(productId, sizeId);
    this.loadCart();
  }
  cancelMessagefn() {
    //  this.finalOrderId = localStorage.getItem('finalOrderId')
    if (this.finalOrderId) {
      this.cancelMessage = "هل تريد غلق الطلب المعلق"
    } else {
      this.cancelMessage = "هل تريد الغاء الطلب"
    }
  }
  cancelOrder(): void {
    this.clearCart();
    localStorage.removeItem('finalOrderId');
    this.finalOrderId = " ";
    this.falseMessage = 'تم إلغاء الطلب بنجاح.';
    setTimeout(() => {
      this.falseMessage = '';
    }, 1500);
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
      backdrop.remove();
    }
    // const modal = document.getElementById('cancelModal');
    // if (modal) {
    //   modal.classList.remove('show');
    //   modal.setAttribute('aria-hidden', 'true');
    //   modal.setAttribute('style', 'display: none');
    // }

    const cancelModal = document.getElementById('cancelModal');
    if (cancelModal) {
      const modalInstance = bootstrap.Modal.getInstance(cancelModal);
      if (modalInstance) {
        modalInstance.hide();
      }
    }
    this.closeModal();
    localStorage.removeItem('table_number');
    localStorage.removeItem('table_id');
    localStorage.removeItem('address_id');
    console.log('fatema');

    localStorage.removeItem('form_data');
    localStorage.removeItem('notes');
    localStorage.removeItem('additionalNote');
    localStorage.removeItem('selectedHotel');
    localStorage.removeItem('hotel_id');
    localStorage.removeItem('selected_address');
    this.tableNumber = null;
    this.FormDataDetails = null;
  }
  clearCart(): void {
    this.productsService.clearCart();
    this.cartItems = [];
    this.appliedCoupon = null; // Clear applied coupon
    this.couponCode = ''; // Clear coupon input field
    this.discountAmount = 0; // Reset discount amount
    this.additionalNote = ''; // Clear order note
    this.savedNote = ''; // Clear saved note
    this.successMessage = ''; // Clear success message
    this.errorMessage = ''; // Clear error message
    this.removeCouponFromLocalStorage(); // Remove coupon from localStorage
    this.saveCart(); // Update localStorage
    // this.clearSelectedCourier(); // Clear selected courier
    this.clearOrderType(); // Clear selected order type
  }
  getCartTotal(): number {
    if (!this.branchData) return 0;

    const taxEnabled = this.branchData.tax_application;
    const couponEnabled = this.branchData.coupon_application;
    const couponPercentage = this.appliedCoupon?.value_type;

    // Step 1: Calculate subtotal from cart items
    const rawSubtotal = this.getTotal();

    let subtotal;
    if (!couponEnabled && !taxEnabled && this.appliedCoupon) {
      subtotal = this.appliedCoupon?.amount_after_coupon;
    } else {
      subtotal = rawSubtotal;
    }

    let taxAmount = 0;

    // Step 2: Calculate tax if tax is disabled (weird condition?)
    if (!taxEnabled) {
      // taxAmount = this.getTotalAfterServices() * (this.branchData.tax_percentage / 100);
      taxAmount = this.getTax();
      /*       console.log('Subtotal before tax:', subtotal);
            console.log('Calculated taxAmount:', taxAmount); */
    }
    // if (this.discountAmount) {
    //   if (taxEnabled && !couponEnabled) {
    //     subtotal = this.getTotal() - this.discountAmount; // getTax already includes tax
    //   } else {
    //     subtotal = this.getTotal() + this.discountAmount;
    //     console.log('Subtotal after coupon:', subtotal);
    //   }
    // }
    // Step 3: Apply coupon
    if (this.appliedCoupon) {
      if (taxEnabled && !couponEnabled && couponPercentage === 'percentage') {
        subtotal = this.appliedCoupon.amount_after_coupon + this.getTax(); // getTax already includes tax
      } else {
        subtotal = this.appliedCoupon.amount_after_coupon;
        // console.log('Subtotal after coupon:', subtotal);
      }
    }

    // Step 4: Ensure subtotal is not negative
    // subtotal = Math.max(subtotal, 0);

    // Step 5: Calculate service fee (based on raw subtotal only)
    let serviceFee = 0;
    if (
      this.selectedOrderType === 'في المطعم' ||
      this.selectedOrderType === 'dine-in'
    ) {
      if (!couponEnabled && !taxEnabled && this.appliedCoupon) {
        serviceFee = this.getServiceOnAmountAfterCoupon();
      } else {
        serviceFee = this.getServiceFeeAmount();
      }
    }

    // Step 6: Delivery fee
    let deliveryFee = 0;
    if (
      this.selectedOrderType === 'توصيل' ||
      this.selectedOrderType === 'Delivery'
    ) {
      deliveryFee = this.delivery_fees;
    }

    // Step 7: Final total calculation
    let total = 0;

    if (!taxEnabled && !this.appliedCoupon) {
      total =
        subtotal +
        taxAmount +
        serviceFee +
        deliveryFee; /*  console.log(subtotal, taxAmount, serviceFee, deliveryFee); */
      /*       console.log(total, 'first');
       */
    } else if (!taxEnabled && couponEnabled) {
      total = subtotal + serviceFee + deliveryFee;
      // console.log(total, 'second');
    } else {
      total = subtotal + taxAmount + serviceFee + deliveryFee;
      // console.log(total, 'third', subtotal, taxAmount, serviceFee, deliveryFee);
    }

    // Step 8: Final safeguard
    return total > 0 ? total : 0;
  }

  getServiceOnAmountAfterCoupon(): number {
    const serviceType = this.branchData.service_fees_type;
    const serviceValue = this.branchData.service_fees;
    const subTotal = this.appliedCoupon?.amount_after_coupon;
    let serviceFee = 0;
    if (serviceType === 'percentage') {
      serviceFee = (subTotal * serviceValue) / 100;
    } else {
      serviceFee = serviceValue;
    }
    return serviceFee;
  }
  getServiceFeeAmount(): number {
    if (!this.branchData) return 0;

    const taxEnabled = this.branchData.tax_application;
    const serviceType = this.branchData.service_fees_type;
    const serviceValue = this.branchData.service_fees;
    const taxPercentage: number =
      parseFloat(this.branchData?.tax_percentage) || 0;

    // Step 1: Get total of cart items (before any discount or tax)
    let cartSubtotal = this.getTotal();
    /*     console.log(cartSubtotal);
     */ // Step 2: Determine base amount for service fee
    let baseAmount = cartSubtotal;

    if (taxEnabled && serviceType === 'percentage') {
      // When tax is enabled and service fee is percentage → apply on subtotal before tax
      baseAmount = this.cartItems.reduce((total, item) => {
        const priceBeforeTax =
          this.getItemTotal(item) / (1 + taxPercentage / 100);
        return total + priceBeforeTax;
      }, 0);
      // console.log(baseAmount);
    }

    // Step 3: Calculate service fee
    let serviceFee = 0;
    if (serviceType === 'percentage') {
      serviceFee = (baseAmount * serviceValue) / 100;
    } else {
      serviceFee = serviceValue;
    }

    return serviceFee;
  }

  totalAfterServices: number = 0;

  getTotalAfterServices() {
    const taxEnabled = this.branchData.tax_application;
    const couponEnabled = this.branchData.coupon_application;
    if (!couponEnabled && !taxEnabled && this.appliedCoupon) {
      this.totalAfterServices =
        this.getTotal() + this.getServiceOnAmountAfterCoupon();
    } else {
      this.totalAfterServices = this.getTotal() + this.getServiceFeeAmount();
    }
    return this.totalAfterServices;
  }
  getTotalAfterDelivery() {
    const deliveryFees = this.delivery_fees;
    return this.getTotal() + deliveryFees;
  }
  // getTax(): any {
  //   if (this.branchData?.tax_application === true) {
  //     const taxPercentage = this.branchData?.tax_percentage ?? 10;
  //     const subtotal = this.getTotalAfterServices();
  //     const taxAmount = subtotal - subtotal / (1 + taxPercentage / 100);
  //     return taxAmount;
  //   } else if (this.branchData?.tax_application === false) {
  //     const taxPercentage = this.branchData?.tax_percentage ?? 10;
  //     const subtotal = this.getTotalAfterServices();
  //     const taxAmount = (subtotal * taxPercentage) / 100;
  //     return taxAmount;
  //   }
  // }
  getTax(): number {
    if (!this.branchData) return 0;
    const taxEnabled = this.branchData.tax_application;
    const couponEnabled = this.branchData.coupon_application;
    const taxPercentage = this.branchData.tax_percentage ?? 10;
    const isDineIn =
      this.selectedOrderType === 'في المطعم' ||
      this.selectedOrderType === 'dine-in';
    const isDeliveryOrder =
      this.selectedOrderType === 'توصيل' ||
      this.selectedOrderType === 'Delivery';

    let subtotal;
    if (this.appliedCoupon) {
      subtotal = this.appliedCoupon?.amount_after_coupon;
      // console.log(subtotal, 'hhhh');
    } else if ((subtotal = isDineIn)) {
      subtotal = isDineIn ? this.getTotalAfterServices() : this.getTotal();
    } else {
      subtotal = this.getTotal();
    }
    let taxAmount = 0;

    if (this.branchData.tax_application === true) {
      taxAmount = subtotal - subtotal / (1 + taxPercentage / 100);
    } else {
      if (isDineIn && this.appliedCoupon) {
        subtotal =
          this.appliedCoupon?.amount_after_coupon +
          this.getServiceOnAmountAfterCoupon();
      }
      if (isDeliveryOrder && this.appliedCoupon) {
        const deliveryFees = this.delivery_fees;
        subtotal = this.appliedCoupon?.amount_after_coupon;
      }

      taxAmount = (subtotal * taxPercentage) / 100;
      /*       console.log(taxAmount, 'here');
       */
    }

    return taxAmount;
  }

  getTotalItemCount(): number {
    return this.cartItems.reduce(
      (total: any, item: { quantity: any }) => total + item.quantity,
      0
    );
  }
  // applyCoupon() {
  //   const token = localStorage.getItem('authToken');

  //   if (!token) {
  //     this.errorMessage = 'يجب تسجيل الدخول لتطبيق الكوبون.';
  //     return;
  //   }

  //   if (!this.couponCode.trim()) {
  //     this.errorMessage = 'يرجى إدخال الكوبون.';
  //     return;
  //   }

  //   this.errorMessage = '';
  //   this.successMessage = '';
  //   this.isLoading = true;

  //   const headers = new HttpHeaders({
  //     'Authorization': `Bearer ${token}`,
  //     'Content-Type': 'application/json'
  //   });

  //   const amount = this.getTotal();
  //   const branchId = localStorage.getItem('branch_id');
  //   const apiUrl = 'https://erpsystem.testdomain100.online/api/coupons/check-coupon';

  //   const requestData = {
  //     code: this.couponCode,
  //     amount: amount,
  //     branch_id: branchId
  //   };

  //   this.http.post(apiUrl, requestData, { headers }).pipe(
  //     tap((response: any) => {
  //       if (response.status) {
  //         this.appliedCoupon = response.data;

  //         // Use the 'amount_after_coupon' from API response
  //         const amountAfterCoupon = parseFloat(response.data.amount_after_coupon);
  //         const originalAmount = amount;
  //         this.discountAmount = originalAmount - amountAfterCoupon;

  //         // Ensure discount is not negative
  //         this.discountAmount = Math.max(this.discountAmount, 0);

  //         this.successMessage = `تم تطبيق الكوبون! تم خصم ${this.discountAmount.toFixed(2)} ${response.data.currency_symbol} من الإجمالي.`;

  //         // Save the applied coupon data in local storage
  //         this.saveCouponToLocalStorage(response.data);

  //         // Update the total price with the amount after coupon
  //         const backdrop = document.querySelector('.modal-backdrop');

  //         this.totalPrice = amountAfterCoupon;
  //         if (backdrop) {
  //           backdrop.remove();
  //         }

  //         const modal = document.getElementById('couponModal');
  //         if (modal) {
  //           modal.classList.remove('show');
  //           modal.setAttribute('aria-hidden', 'true');
  //           modal.setAttribute('style', 'display: none');
  //         }

  //       } else {
  //         this.errorMessage = response.message || 'حدث خطأ ما!';
  //       }
  //     }),
  //     catchError(error => {
  //       this.errorMessage = error.error?.message || 'حدث خطأ أثناء التحقق من الكوبون.';
  //       return of(null);
  //     })
  //   ).subscribe(() => this.isLoading = false);
  // }
  applyCoupon() {

    const token = localStorage.getItem('authToken');
    if (!token) {
      this.errorMessage = 'يجب تسجيل الدخول لتطبيق الكوبون.';
      return;
    }
    if (!this.couponCode.trim()) {

      this.errorMessage = 'يرجى إدخال الكوبون.';
      return;
    }
    this.errorMessage = '';
    this.successMessage = '';
    this.isLoading = true;
    // Prepare HTTP headers and API endpoint.
    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    });
    const branchId = localStorage.getItem('branch_id');
    const apiUrl = `${baseUrl}api/coupons/check-coupon`;
    // Ensure branchData exists; otherwise, default values.
    const taxEnabled: boolean = this.branchData?.tax_application ?? false;
    const couponOnTotalAfterTax: boolean =
      this.branchData?.coupon_application ?? false;
    const taxPercentage: number =
      parseFloat(this.branchData?.tax_percentage) || 0;
    let baseAmount = 0;
    // Log settings for debugging
    // console.log('Applying coupon with settings:', {
    //   taxEnabled,
    //   couponOnTotalAfterTax,
    //   taxPercentage,
    // });

    // CASE 1: Tax enabled and coupon applies before tax
    if (taxEnabled && !couponOnTotalAfterTax) {
      baseAmount = this.cartItems.reduce((total, item) => {
        const priceBeforeTax =
          this.getItemTotal(item) / (1 + taxPercentage / 100);
        return total + priceBeforeTax;
      }, 0);
      // console.log("copon", baseAmount);
    }
    // CASE 2: Tax disabled but coupon applies after tax
    else if (!taxEnabled && couponOnTotalAfterTax) {
      // Using getTotal() plus getTax() ensures correct total with tax included.
      baseAmount = this.getTotal() + this.getTax();
      console.log(baseAmount, 'cashier3');
    }
    // CASE 3: Tax enabled and coupon applies after tax
    else if (taxEnabled && couponOnTotalAfterTax) {
      // Apply coupon on cart total calculated by getTotal()
      baseAmount = this.getTotal();
    }
    // CASE 4: Tax disabled and coupon applies on cart total (default)
    else {
      baseAmount = this.getTotal();
      // console.log(baseAmount, 'coupon');
    }
    // Log computed base amount for debugging.
    // console.log('Computed baseAmount:', baseAmount);
    // Validate computed baseAmount.
    if (!baseAmount || isNaN(baseAmount)) {
      this.errorMessage = 'فشل حساب إجمالي الطلب. تحقق من الأسعار والكميات.';
      this.isLoading = false;
      return;
    }
    const requestData = {
      code: this.couponCode,
      amount: baseAmount,
      branch_id: branchId,
      dishes: this.cartItems.map(item => {
        const dishData: any = {
          dish_id: item.dish.id,
          quantity: item.quantity
        };

        if (item.selectedSize?.id) {
          dishData.size_id = item.selectedSize.id;
        }

        return dishData;
      })
    };

    this.http
      .post(apiUrl, requestData, { headers })
      .pipe(
        tap((response: any) => {
          if (response.status) {
            this.validCoupon = true
            this.appliedCoupon = response.data;
            this.couponTitle = response.data.coupon_title
            // if (response.data.value_type === 'percentage') {
            //   this.discountAmount =
            //     (baseAmount * parseFloat(response.data.coupon_value)) / 100;
            //   console.log(this.discountAmount, 'coupon');
            // } else if (response.data.value_type === 'fixed') {
            //   this.discountAmount = parseFloat(response.data.coupon_value);
            //   console.log(this.discountAmount, 'fixed');
            // }
            // this.discountAmount = Math.min(this.discountAmount, baseAmount);
            this.discountAmount = response.data.total_discount
            this.successMessage = `تم تطبيق الكوبون! تم خصم ${this.discountAmount.toFixed(
              2
            )} ${response.data.currency_symbol} من الإجمالي.`;

            localStorage.setItem(
              'appliedCoupon',
              JSON.stringify(response.data)
            );
            localStorage.setItem(
              'discountAmount',
              this.discountAmount.toString()
            );

            localStorage.setItem('couponCode', this.couponCode);
            localStorage.setItem('couponTitle', this.couponTitle);

            this.updateTotalPrice();

            const modalEl = document.getElementById('couponModal');
            if (modalEl) {
              let bsModal = bootstrap.Modal.getInstance(modalEl);
              if (!bsModal) {
                bsModal = new bootstrap.Modal(modalEl);
              }
              bsModal.hide();

              const backdrops = document.querySelectorAll('.modal-backdrop');
              backdrops.forEach((el) => el.remove());

              document.body.classList.remove('modal-open');
              document.body.style.removeProperty('padding-right');
            }
          } else {
            this.validCoupon = false;
            this.removeCouponFromLocalStorage()
            this.couponCode = ' ';
            if (response.errorData?.error) {
              this.errorMessage = response.errorData.error;
            }
          }
        }),
        catchError((error) => {
          if (error.error?.errorData?.error) {
            this.errorMessage = error.error?.errorData.error;
          }

          return of(null);
        })
      )
      .subscribe(() => (this.isLoading = false));
  }
  restoreCoupon() {
    const storedCoupon = localStorage.getItem('appliedCoupon');
    if (storedCoupon) {
      this.appliedCoupon = JSON.parse(storedCoupon);

      const taxEnabled: boolean = this.branchData?.tax_application ?? false;
      const couponOnTotalAfterTax: boolean =
        this.branchData?.coupon_application ?? false;
      const taxPercentage: number =
        parseFloat(this.branchData?.tax_percentage) || 0;

      let baseAmount = 0;

      if (taxEnabled && !couponOnTotalAfterTax) {
        baseAmount = this.cartItems.reduce((total, item) => {
          const priceBeforeTax =
            this.getItemTotal(item) / (1 + taxPercentage / 100);
          return total + priceBeforeTax;
        }, 0);
      } else if (!taxEnabled && couponOnTotalAfterTax) {
        baseAmount = this.getTotal() + this.getTax();
      } else {
        baseAmount = this.getTotal();
      }

      if (this.appliedCoupon) {
        if (this.appliedCoupon.value_type === 'percentage') {
          this.discountAmount =
            (baseAmount * parseFloat(this.appliedCoupon.coupon_value)) / 100;
        } else if (this.appliedCoupon.value_type === 'fixed') {
          this.discountAmount = parseFloat(this.appliedCoupon.coupon_value);
        }
      }
      // localStorage.setItem('discountAmount', this.discountAmount.toString());

      this.discountAmount = Math.min(this.discountAmount, baseAmount);
    }
    this.updateTotalPrice();
  }
  getLocalDiscount() {
    let discount = localStorage.getItem('discountAmount');
    return discount;
  }
  // loadCouponFromLocalStorage() {
  //   const storedCoupon = localStorage.getItem('appliedCoupon');
  //   console.log(storedCoupon);

  //   if (storedCoupon) {
  //     const parsedCoupon = JSON.parse(storedCoupon);
  //     this.appliedCoupon = parsedCoupon;

  //     // Recalculate discount based on current cart state
  //     const taxEnabled = this.branchData?.tax_application ?? false;
  //     const couponOnTotalAfterTax = this.branchData?.coupon_application ?? false;
  //     const taxPercentage = parseFloat(this.branchData?.tax_percentage) || 0;

  //     let baseAmount = 0;

  //     if (taxEnabled && !couponOnTotalAfterTax) {
  //       baseAmount = this.cartItems.reduce((total, item) => {
  //         const priceBeforeTax = this.getItemTotal(item) / (1 + taxPercentage / 100);
  //         return total + priceBeforeTax;
  //       }, 0);
  //       console.log(baseAmount ,"first");

  //     } else if (!taxEnabled && couponOnTotalAfterTax) {
  //       baseAmount = this.getTotal() + this.getTax();
  //       console.log(baseAmount ,"sec");

  //     } else {
  //       baseAmount = this.getTotal();
  //       console.log(baseAmount ,"third");

  //     }

  //     if (parsedCoupon.value_type === 'percentage') {
  //       this.discountAmount = (baseAmount * parseFloat(parsedCoupon.coupon_value)) / 100;
  //     } else {
  //       this.discountAmount = parseFloat(parsedCoupon.coupon_value);
  //       console.log(this.discountAmount)
  //     }
  //     this.discountAmount = Math.min(this.discountAmount, baseAmount);

  //   }
  // }

  clearOrderType() {
    this.selectedOrderType = '';
    localStorage.removeItem('selectedOrderType');
  }

  removeCoupon() {
    this.appliedCoupon = null;
    this.discountAmount = 0;
    this.couponCode = '';
    this.successMessage = '';
    this.errorMessage = '';
    this.removeCouponFromLocalStorage();
    this.updateTotalPrice();
    setTimeout(() => {
      const modalEl = document.getElementById('couponModal');
      if (modalEl) {
        let bsModal = bootstrap.Modal.getInstance(modalEl);
        if (!bsModal) {
          bsModal = new bootstrap.Modal(modalEl);
        }
        bsModal.hide();

        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach((el) => el.remove());

        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
      }
    }, 1000);
  }

  removeCouponFromLocalStorage() {
    localStorage.removeItem('couponCode');
    localStorage.removeItem('discountAmount');
    localStorage.removeItem('appliedCoupon');
  }
  getTotal(): number {
    return this.cartItems.reduce(
      (sum, item) => sum + this.getItemTotal(item),
      0
    );
  }
  getItemTotal(item: any): number {
    let basePrice = 0;

    // Case 1: new cart structure (with selectedSize, dish, and selectedAddons)
    if (item.dish || item.selectedSize || item.selectedAddons) {
      const sizePrice = Number(item.selectedSize?.price ?? 0);
      const dishPrice = Number(item.dish?.price ?? 0);
      const addonsTotal = item.selectedAddons?.reduce(
        (sum: number, addon: { price: any }) => sum + Number(addon.price ?? 0),
        0
      ) ?? 0;
      basePrice = sizePrice || dishPrice;
      return (basePrice + addonsTotal) * (Number(item.quantity) || 1);
    }

    // Case 2: older/test item structure (with final_price or dish_price)
    const fallbackPrice = Number(item.final_price ?? item.dish_price ?? 0);
    return fallbackPrice * (Number(item.quantity) || 1);
  }

  clearMessage() {
    this.errorMessage = '';
  }
  selectedCourier: { id: number; name: string } | null = null;
  FormData: {
    address: string;
    address_phone: number;
    client_name: string;
  } | null = null;

  getAddressId(): Promise<number | null> {
    return new Promise((resolve, reject) => {
      const formValue = JSON.parse(localStorage.getItem('form_data') || '{}');
      const note = localStorage.getItem('notes') || '';
      formValue.address = this.address;
      const formDataWithNote = { ...formValue, country_code: formValue.country_code.code, whatsapp_number_code: formValue.whatsapp_number_code.code, notes: note };
      console.log(formDataWithNote, 'aaaaaaaaaaaaaaaa');

      this.formDataService.submitForm(formDataWithNote).subscribe({
        next: (response) => {
          if (response.status) {
            console.log(
              'Full form submission response:',
              response.data.address_id
            );
            if (!response.data || !response.data.address_id) {
              console.warn(
                'Missing address_id in response data:',
                response.data
              );
              resolve(null);
              return;
            }

            this.addressIdFromResponse = response.data.address_id;
            localStorage.setItem('address_id', this.addressIdFromResponse)
            console.log('Received address_id:', this.addressIdFromResponse);
            resolve(this.addressIdFromResponse);

            return this.addressIdFromResponse;
          }
          if (!response.status) {
            this.falseMessage = 'يرجى اختيار عنوان التوصيل ';
            setTimeout(() => {
              this.falseMessage = '';
            }, 1500);
            this.isLoading = false;
            return;
          }
          console.log("rrrr")
        },
        error: (err) => {
          console.error('❌ Error submitting form:', err);
          resolve(null);
        },
      });
    });
  }

  // async submitOrder() {
  //   if (!this.cartItems.length) {
  //     this.falseMessage = 'العربة فارغة، أضف بعض العناصر قبل تنفيذ الطلب.';
  //     setTimeout(() => {
  //       this.falseMessage = '';
  //     }, 1500);
  //     return;
  //   }
  //   if (!this.selectedOrderType) {
  //     this.falseMessage = 'يرجى تحديد نوع الطلب قبل المتابعة.';
  //     setTimeout(() => {
  //       this.falseMessage = '';
  //     }, 1500);
  //     return;
  //   }
  //   // if (this.selectedOrderType === 'Delivery' && !this.selectedCourier?.id) {
  //   //   setTimeout(() => { this.falseMessage = ""; }, 1500);
  //   //   this.falseMessage = "يرجى اختيار الطيار للتوصيل.";
  //   //   return;
  //   // }

  //   this.isLoading = true;
  //   // /fatma
  //   this.loading=true

  //   const branchId = Number(localStorage.getItem('branch_id')) || null;
  //   const tableId = Number(localStorage.getItem('table_id')) || null;
  //   const addressId = await this.getAddressId();
  //   console.log(addressId, 'testtttttttttttt');
  //   const authToken = localStorage.getItem('authToken');
  //   const cashier_machine_id = localStorage.getItem('cashier_machine_id');
  //   if (!branchId) {
  //     this.falseMessage = 'فشل تحديد الفرع. الرجاء إعادة تسجيل الدخول.';
  //     setTimeout(() => {
  //       this.falseMessage = '';
  //     }, 1500);
  //     this.isLoading = false;
  //     return;
  //   }
  //   if (!authToken) {
  //     this.falseMessage = 'فشل التحقق من الهوية. الرجاء تسجيل الدخول مجددًا.';
  //     setTimeout(() => {
  //       this.falseMessage = '';
  //     }, 1500);
  //     this.isLoading = false;
  //     return;
  //   }
  //   const paymentStatus =
  //     this.selectedPaymentMethod === 'cash'
  //       ? this.selectedPaymentStatus
  //       : 'paid';

  //   const orderData: any = {
  //     type: this.selectedOrderType,
  //     branch_id: branchId,
  //     payment_method: this.selectedPaymentMethod,
  //     payment_status: paymentStatus,
  //     cashier_machine_id: cashier_machine_id,
  //     note:
  //       this.additionalNote ||
  //       this.savedNote ||
  //       this.applyAdditionalNote() ||
  //       '',
  //     // coupon_code: this.appliedCoupon?.code || this.couponCode || '',
  //     items: this.cartItems
  //       .map((item) => ({
  //         dish_id: item.dish?.id || null,
  //         dish_name: item.dish?.name || '',
  //         dish_description: item.dish?.description || '',
  //         dish_price: item.dish?.price || 0,
  //         currency_symbol: item.dish?.currency_symbol || '',
  //         dish_image: item.dish?.image || null,
  //         quantity: item.quantity || 1,
  //         sizeId: item.selectedSize?.id || null,
  //         size: item.size || '',
  //         sizeName: item.selectedSize?.name || '',
  //         sizeDescription: item.selectedSize?.description || '',
  //         note: item.note || '',
  //         finalPrice: item.finalPrice || 0,
  //         selectedAddons: item.selectedAddons || [],
  //         addon_categories: item.addon_categories
  //           ?.map((category: { id: any; addons: { id: any }[] }) => {
  //             const selectedAddons = category.addons?.filter((addon) =>
  //               item.selectedAddons.some(
  //                 (selected: { id: any }) => selected.id === addon.id
  //               )
  //             );
  //             return selectedAddons.length > 0
  //               ? {
  //                   id: category.id,
  //                   addon: selectedAddons.map((addon) => addon.id),
  //                 }
  //               : null;
  //           })
  //           .filter((category: null) => category !== null),
  //       }))
  //       .filter((item) => item.dish_id),
  //   };
  //   // inside your submitOrder() function, after building orderData...
  //   if (this.appliedCoupon && this.couponCode?.trim()) {
  //     orderData.coupon_code = this.couponCode.trim();
  //     orderData.discount_amount = this.discountAmount;
  //     orderData.coupon_type = this.appliedCoupon.value_type;
  //   } else if (this.couponCode?.trim()) {
  //     orderData.coupon_code = this.couponCode.trim();
  //   }
  //   // if (this.appliedCoupon) {
  //   //   orderData.coupon_value = this.appliedCoupon.coupon_value;
  //   //   orderData.value_type = this.appliedCoupon.value_type;
  //   //   orderData.discount_amount = this.discountAmount;
  //   // }
  //   if (!orderData.items.length) {
  //     this.falseMessage = 'لا يمكن تقديم الطلب بدون عناصر صالحة.';
  //     setTimeout(() => {
  //       this.falseMessage = '';
  //     }, 1500);
  //     this.isLoading = false;
  //     return;
  //   }
  //   if (
  //     this.selectedOrderType === 'dine-in' ||
  //     this.selectedOrderType === 'في المطعم'
  //   ) {
  //     if (!tableId) {
  //       this.falseMessage = 'يرجى اختيار طاولة.';
  //       setTimeout(() => {
  //         this.falseMessage = '';
  //       }, 1500);
  //       this.isLoading = false;
  //       return;
  //     }
  //     orderData.table_id = tableId;
  //   }
  //   if (
  //     this.selectedOrderType === 'Delivery' ||
  //     this.selectedOrderType === 'توصيل'
  //   ) {
  //     if (!addressId) {
  //       this.falseMessage = 'يرجى اختيار عنوان التوصيل ';
  //       setTimeout(() => {
  //         this.falseMessage = '';
  //       }, 1500);
  //       this.isLoading = false;
  //       return;
  //     }
  //     orderData.address_id = addressId;
  //     // orderData.delivery_id = this.selectedCourier.id;
  //   }
  //   // console.log(':package: New Final Order Data:', JSON.stringify(orderData, null, 2));
  //   const headers = new HttpHeaders({
  //     Authorization: `Bearer ${authToken}`,
  //     'Accept-Language': 'ar',
  //   });
  //   this.plaseOrderService.placeOrder(orderData) .pipe(
  //   finalize(() => {
  //     this.loading=false
  //     console.log('Request finalized');
  //   })
  // ).subscribe({
  //     next: (response): void => {
  //       console.log(response)
  //        this.tableError=response.errorData.error.table_id
  //       this.orderedId = response.data.order_id;
  //       console.log(this.orderedId, 'orderedId');
  //       if (this.selectedOrderType === 'Takeaway') {
  //         const dataOrderId = response.data.order_id;

  //         this.createdOrderId = dataOrderId;

  //         this.fetchPillsDetails(dataOrderId);
  //         this.printInvoice();
  //         // remove couponCode if exist
  //         const couponCode=localStorage.getItem('couponCode');
  //         if(couponCode)
  //           localStorage.removeItem('couponCode')
  //       }
  //       if (!response.status) {
  //         this.falseMessage = response.errorData?.error
  //           ? `${response.errorData.error}`
  //           : `${response.message || 'حدث خطأ أثناء تقديم الطلب'}`;
  //         setTimeout(() => {
  //           this.falseMessage = '';
  //         }, 1500);
  //         this.isLoading = false;
  //         return;
  //       }
  //       const orderId = response.data?.order_id;
  //       if (!orderId) {
  //         this.falseMessage = '  لم يتم استلام رقم الطلب من الخادم.';
  //         setTimeout(() => {
  //           this.falseMessage = '';
  //         }, 1500);
  //         this.isLoading = false;
  //         return;
  //       }
  //       this.clearCart();
  //       localStorage.removeItem('table_number');
  //       localStorage.removeItem('table_id');
  //       localStorage.removeItem('address_id');
  //       localStorage.removeItem('form_data');
  //       localStorage.removeItem('additionalNote');
  //       this.tableNumber = null;
  //       this.FormDataDetails = null;
  //       this.successModal.show();
  //       this.falseMessage = 'تم تقديم الطلب بنجاح!';
  //       setTimeout(() => {
  //         this.falseMessage = '';
  //       }, 1500);
  //       this.isLoading = false;
  //     },
  //     error: (error) => {
  //       console.error('  API Error:', error.error);
  //       if (error.error?.errorData?.error) {
  //         this.falseMessage = `  ${error.error.errorData.error}`;
  //       } else if (error.error?.message) {
  //         this.falseMessage = `  ${error.error.message}`;
  //       } else if (error.error?.errors) {
  //         const errorMessages = Object.values(error.error.errors)
  //           .flat()
  //           .join('\n');
  //         this.falseMessage = `  ${errorMessages}`;
  //       } else {
  //         this.falseMessage = '  حدث خطأ غير متوقع، يرجى المحاولة لاحقًا.';
  //       }
  //       this.isLoading = false;
  //     },
  //     complete: () => {
  //       this.isLoading = false;
  //     },
  //   });
  //   console.log(orderData);
  // }
  formSubmitted = false;
  amountError = false;
  addressRequestInProgress: boolean = false;

  async submitOrder() {
    if (this.isLoading) {
      console.warn("🚫 Request already in progress, ignoring duplicate submit.");
      return;
    }

    this.isLoading = true;
    this.loading = true;
    if (!this.cartItems.length) {
      this.isLoading = false;
      this.falseMessage = 'العربة فارغة، أضف بعض العناصر قبل تنفيذ الطلب.';
      setTimeout(() => {
        this.falseMessage = '';
      }, 1500);
      return;
    }
    if (!this.selectedOrderType) {
      this.isLoading = false;
      this.falseMessage = 'يرجى تحديد نوع الطلب قبل المتابعة.';
      setTimeout(() => {
        this.falseMessage = '';
      }, 1500);
      return;
    }

    const branchId = Number(localStorage.getItem('branch_id')) || null;
    const tableId = Number(localStorage.getItem('table_id')) || this.table_id || null;
    // const tableId = Number(4) ;

    const formData = JSON.parse(localStorage.getItem('form_data') || '{}');

    if (this.selectedPaymentStatus === 'paid' && this.credit_amountt > 0 && !this.referenceNumber) {
      this.isLoading = false;
      this.falseMessage = '❌ رقم المرجع مطلوب عند الدفع بالفيزا.';
      return;
    }
    let addressId = null;
    console.log(this.selectedOrderType, 'gggggggggggg');
    // if (this.selectedOrderType === 'Delivery') {
    //   addressId = localStorage.getItem('address_id');
    //   if (!localStorage.getItem('address_id')) {
    //     addressId = await this.getAddressId();
    //   }
    // }
    if (this.selectedOrderType === 'Delivery') {
      addressId = localStorage.getItem('address_id');

      if (!addressId && !this.addressRequestInProgress) {
        this.addressRequestInProgress = true;
        try {
          addressId = await this.getAddressId();
          if (addressId) {
            localStorage.setItem('address_id', addressId.toString());
          }
        } finally {
          this.addressRequestInProgress = false;
        }
      }

    }

    // Also add addressId for Takeaway only if exists (optional, depends on backend)

    const authToken = localStorage.getItem('authToken');
    const cashier_machine_id = localStorage.getItem('cashier_machine_id');

    if (!branchId) {
      this.falseMessage = 'فشل تحديد الفرع. الرجاء إعادة تسجيل الدخول.';
      setTimeout(() => {
        this.falseMessage = '';
      }, 1500);
      this.isLoading = false;
      this.loading = false;
      return;
    }
    if (!authToken) {
      this.falseMessage = 'فشل التحقق من الهوية. الرجاء تسجيل الدخول مجددًا.';
      setTimeout(() => {
        this.falseMessage = '';
      }, 1500);
      this.isLoading = false;
      this.loading = false;
      return;
    }
    this.formSubmitted = true;
    this.amountError = false;

    if (!this.selectedPaymentStatus) {
      // No payment status selected
      setTimeout(() => {
        this.formSubmitted = false;
      }, 2500);
      return;
    }

    if (this.selectedPaymentStatus === 'paid') {
      const isDelivery =
        this.selectedOrderType === 'Delivery' ||
        this.selectedOrderType === 'توصيل';
      if (!isDelivery) {

        const totalEntered =
          Number(this.cash_amountt || 0) + Number(this.credit_amountt || 0);
        const cartTotal = Number(this.getCartTotal().toFixed(2));

        if (totalEntered < cartTotal) {
          this.amountError = true;
          console.log('❌ Entered amount less than total:', totalEntered, cartTotal);

          setTimeout(() => {
            this.amountError = false;
            console.log('🔁 Cleared error state');
          }, 2500);

          return;
        }

        console.log('✅ Valid payment amount:', totalEntered, cartTotal);
      }
    }

    this.isLoading = true;
    this.loading = true;
    this.falseMessage = '';
    this.tableError = '';
    this.couponError = ''; // Clear previous coupon error

    const paymentStatus =
      this.selectedPaymentMethod === 'cash'
        ? this.selectedPaymentStatus
        : 'paid';
    console.log(this.selectedPaymentMethod, 'selectedPaymentMethod');
    const iddd = localStorage.getItem('hotel_id');
    const orderData: any = {
      orderId: this.finalOrderId,
      type: this.selectedOrderType,
      branch_id: branchId,
      payment_method: this.selectedPaymentMethod,
      payment_status: paymentStatus,
      cash_amount: this.cash_amountt || null, ///////////////// alaa
      credit_amount: this.credit_amountt || null,
      cashier_machine_id: cashier_machine_id,
      // client_country_code: this.selectedCountry.code || "+20",
      ...(this.clientPhoneStoredInLocal ? { client_country_code: this.selectedCountry.code || "+20" } : {}),
      ...(this.clientPhoneStoredInLocal ? { client_phone: this.clientPhoneStoredInLocal } : {}),
      ...(this.clientStoredInLocal ? { client_name: this.clientStoredInLocal } : {}),
      // "whatsapp_number_code" :"+20",
      // "whatsapp_number" : "01102146215" ,
      note:
        this.additionalNote ||
        this.savedNote ||
        this.applyAdditionalNote() || this.onholdOrdernote ||
        '',
      items: this.cartItems
        .map((item) => ({
          dish_id: item.dish?.id || null,
          dish_name: item.dish?.name || '',
          dish_description: item.dish?.description || '',
          dish_price: item.dish?.price || 0,
          currency_symbol: item.dish?.currency_symbol || '',
          dish_image: item.dish?.image || null,
          quantity: item.quantity || 1,
          sizeId: item.selectedSize?.id || null,
          size: item.size || '',
          sizeName: item.selectedSize?.name || '',
          sizeDescription: item.selectedSize?.description || '',
          note: item.note || '',
          finalPrice: item.finalPrice || 0,
          selectedAddons: item.selectedAddons || [],
          addon_categories: item.addon_categories
            ?.map((category: { id: any; addons: { id: any }[] }) => {
              const selectedAddons = category.addons?.filter((addon) =>
                item.selectedAddons.some(
                  (selected: { id: any }) => selected.id === addon.id
                )
              );
              return selectedAddons.length > 0
                ? {
                  id: category.id,
                  addon: selectedAddons.map((addon) => addon.id),
                }
                : null;
            })
            .filter((category: null) => category !== null),
        }))
        .filter((item) => item.dish_id),
    };

    if (this.appliedCoupon && this.couponCode?.trim()) {
      orderData.coupon_code = this.couponCode.trim();
      orderData.discount_amount = this.discountAmount;
      orderData.coupon_type = this.appliedCoupon.value_type;
    } else if (this.couponCode?.trim()) {
      orderData.coupon_code = this.couponCode.trim();
    }
    if (this.credit_amountt > 0) {
      orderData.reference_number = this.referenceNumber;
    }

    // if (this.appliedCoupon) {
    //   orderData.coupon_value = this.appliedCoupon.coupon_value;
    //   orderData.value_type = this.appliedCoupon.value_type;
    //   orderData.discount_amount = this.discountAmount;
    // }
    if (this.selectedOrderType === 'Delivery' && addressId) {
      orderData.address_id = addressId;
    }
    if (this.selectedPaymentMethod == "unpaid") {
      orderData.credit_amount = null;
      orderData.cash_amount = null;
    }
    if (!orderData.items.length) {
      this.falseMessage = 'لا يمكن تقديم الطلب بدون عناصر صالحة.';
      setTimeout(() => {
        this.falseMessage = '';
      }, 1500);
      this.isLoading = false;
      this.loading = false;
      return;
    }

    if (
      this.selectedOrderType === 'dine-in' ||
      this.selectedOrderType === 'في المطعم'
    ) {
      if (!tableId
      ) {
        this.falseMessage = 'يرجى اختيار طاولة.';
        setTimeout(() => {
          this.falseMessage = '';
        }, 1500);
        this.isLoading = false;
        this.loading = false;
        return;
      }
      orderData.table_id = tableId;
    }

    if (
      this.selectedOrderType === 'Delivery' ||
      this.selectedOrderType === 'توصيل'
    ) {
      if (!addressId) {
        this.falseMessage = 'يرجى اختيار عنوان التوصيل ';
        setTimeout(() => {
          this.falseMessage = '';
        }, 1500);
        this.isLoading = false;
        this.loading = false;
        return;
      }
      orderData.address_id = addressId;
      orderData.client_country_code = formData.country_code.code;
      orderData.client_phone = formData.address_phone;
      orderData.client_name = formData.client_name;
    }

    // const headers = new HttpHeaders({
    //   Authorization: `Bearer ${authToken}`,
    //   'Accept-Language': 'ar',
    // });
    // console.log(orderData.address_id, 'orderData.address_id');
    // console.log(orderData, 'orderData');
    // console.log(this.credit_amountt, 'orderData');
    // console.log(orderData.credit_amount, 'orderData');
    console.log('pppppppp', orderData);

    this.plaseOrderService.placeOrder(orderData).subscribe({
      next: async (response): Promise<void> => {
        console.log('API Response:', response);
        // Clear previous errors
        this.falseMessage = '';
        this.tableError = '';
        this.couponError = '';
        this.cashiermachine = '';
        this.pillId = response.data?.invoice_id
        this.orderedId = response.data?.order_id;
        if (!response.status) {
          if (response.errorData?.error?.cashier_machine_id) {
            this.cashiermachine =
              response.errorData?.error?.cashier_machine_id[0];
          }
          // Handle coupon validation error
          else if (response.errorData?.coupon_code) {
            this.couponError = response.errorData.coupon_code;
          }
          // Handle table error
          else if (response.errorData?.table_id) {
            this.tableError = response.errorData.table_id;
          }

          // Handle generic error
          else {
            this.falseMessage = response.errorData?.error
              ? `${response.errorData.error}`
              : `${response.message || 'حدث خطأ أثناء تقديم الطلب'}`;
            console.log(this.clientError, "gggggggg");

          }

          setTimeout(() => {
            this.falseMessage = '';
            this.tableError = '';
            this.couponError = '';
            this.cashiermachine = '';
          }, 3500);
          this.isLoading = false;
          this.loading = false;
          return;
        }

        if (this.selectedOrderType === 'Takeaway') {
          const dataOrderId = response.data.order_id;
          this.createdOrderId = dataOrderId;
          await this.fetchPillsDetails(this.pillId);
          setTimeout(() => {
            this.printInvoice();
          }, 200)

          this.removeCouponFromLocalStorage();
        }

        const orderId = response.data?.order_id;
        if (!orderId) {
          this.falseMessage = 'لم يتم استلام رقم الطلب من الخادم.';
          setTimeout(() => {
            this.falseMessage = '';
          }, 1500);
          this.isLoading = false;
          this.loading = false;
          return;
        }
        const savedOrders = JSON.parse(localStorage.getItem('savedOrders') || '[]');
        const orderIdToRemove = orderData.orderId;
        const updatedOrders = savedOrders.filter(
          (savedOrder: any) => savedOrder.orderId !== orderIdToRemove
        );
        localStorage.setItem('savedOrders', JSON.stringify(updatedOrders));
        console.log(orderData, 'jjjjj');

        this.clearCart();
        localStorage.removeItem('table_number');
        localStorage.removeItem('table_id');
        localStorage.removeItem('address_id');
        localStorage.removeItem('form_data');
        localStorage.removeItem('notes');
        localStorage.removeItem('deliveryForm');
        localStorage.removeItem('additionalNote');
        localStorage.removeItem('selectedHotel');
        localStorage.removeItem('hotel_id');
        localStorage.removeItem('selectedPaymentStatus');
        localStorage.removeItem('cash_amountt');
        localStorage.removeItem('delivery_fees');
        localStorage.removeItem('credit_amountt');
        localStorage.removeItem('selected_address');
        localStorage.removeItem('finalOrderId');
        localStorage.removeItem('client');
        localStorage.removeItem('clientPhone');
        this.client = " ";
        this.clientPhone = " "
        this.finalOrderId = " ";
        this.cash_amountt = 0; ///////////////// alaa
        this.credit_amountt = 0;
        this.selectedPaymentStatus = '';
        this.resetAddress()
        this.tableNumber = null;
        this.FormDataDetails = null;
        this.successMessage = 'تم تنفيذ طلبك بنجاح';
        this.successModal.show();

        setTimeout(() => {
          this.falseMessage = '';
        }, 1500);
        this.isLoading = false;
        this.loading = false;
      },
      error: (error) => {
        console.error('API Error:', error.error);
        // Handle error response
        if (error.error?.errorData?.error?.coupon_code) {
          this.couponError = error.error.errorData.error.coupon_code[0];
          console.log("1");

        } else if (error.error?.errorData?.error?.client_phone) {
          this.falseMessage = error.error?.errorData?.error?.client_phone[0];
          console.log("2");

        }
        else if (error.error?.errorData?.table_id) {
          this.tableError = error.error?.errorData?.table_id[0];
          console.log("3");

        }
        else if (error.error?.errorData?.error) {
          this.falseMessage = `${error.error.errorData.error}`;
          console.log("43");

        }
        else if (error.error?.message) {
          this.falseMessage = `${error.error.message}`;
          console.log("5");

        } else if (error.error?.errorData.error) {
          const errorMessages = Object.values(error.error?.errorData.error)
            .flat()
            .join('\n');
          this.falseMessage = `${errorMessages}`;
          console.log("6");

        } else {
          this.falseMessage = 'حدث خطأ غير متوقع، يرجى المحاولة لاحقًا.';
        }
        setTimeout(() => {
          this.falseMessage = '';
        }, 2000);
        this.isLoading = false;
        this.loading = false;
      },

      complete: () => {
        this.isLoading = false;
        this.loading = false;
      },

    });
  }

  // private extractDateAndTime(branch: any): void {
  //   const { created_at } = branch;

  //   if (created_at) {
  //     const dateObj = new Date(created_at); // Automatically handles the UTC 'Z'

  //     this.date = this.datePipe.transform(dateObj, 'yyyy-MM-dd') ?? '';
  //     this.time = this.datePipe.transform(dateObj, 'hh:mm a') ?? '';
  //   }
  // }

  private extractDateAndTime(branch: any): void {
    const { created_at } = branch;

    if (created_at) {
      const dateObj = new Date(created_at); // Automatically handles the UTC 'Z'

      this.date = this.datePipe.transform(dateObj, 'yyyy-MM-dd') ?? '';
      this.time = this.datePipe.transform(dateObj, 'hh:mm a') ?? '';
    }
  }

  calculateSubtotal(orderItems: any[]): number {
    let subtotal = 0;

    orderItems.forEach((item) => {
      subtotal += item.total_dish_price; // Calculate total for each dish
    });

    return subtotal;
  }
  setCashAmount(value: number) {
    this.cash_amountt = Number((value ?? 0).toFixed(2)) || this.cash_amount;
    localStorage.setItem('cash_amountt', JSON.stringify(this.credit_amountt));
  }

  setCreditAmount(value: number) {
    this.credit_amountt = Number((value ?? 0).toFixed(2)) || this.credit_amount;
    localStorage.setItem('credit_amountt', JSON.stringify(this.cash_amountt));
  }
  // setCashAmount(value: number | null): void {
  //   this.cash_amountt = Number((value ?? 0).toFixed(2))|| this.cash_amount;
  //   localStorage.setItem('cash_amountt', JSON.stringify(this.cash_amountt));

  //   const total = this.getCartTotal();
  //   const remain = total - this.cash_amountt;
  //   this.credit_amountt = Number((remain >= 0 ? remain : 0).toFixed(2));
  //   localStorage.setItem('credit_amountt', JSON.stringify(this.credit_amountt));

  //   this.amountError = false;
  // }

  // setCreditAmount(value: number | null): void {
  //   this.credit_amountt = Number((value ?? 0).toFixed(2))|| this.credit_amount;
  //   localStorage.setItem('credit_amountt', JSON.stringify(this.credit_amountt));

  //   const total = this.getCartTotal();
  //   const remain = total - this.credit_amountt;
  //   this.cash_amountt = Number((remain >= 0 ? remain : 0).toFixed(2)) ;
  //   localStorage.setItem('cash_amountt', JSON.stringify(this.cash_amountt));

  //   this.amountError = false;
  // }


  // getNoteFromLocalStorage() {
  //   throw new Error('Method not implemented.');
  // }
  // fetchTrackingStatus() {
  //   this.pillDetailsService
  //     .getPillsDetailsById(this.pillId)
  //     .subscribe((response) => {
  //       if (response.status && response.data.invoices.length > 0) {
  //         this.trackingStatus =
  //           response.data.invoices[0]['tracking-status'] || '';
  //       }
  //     });
  // }
  // fetchPillsDetails(pillId: string): void {
  //   this.pillDetailsService.getPillsDetailsById(this.pillId).subscribe({
  //     next: (response: any) => {
  //       this.invoices = response.data.invoices;
  //       console.log(response, "alaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa");


  //       const statusMap: { [key: string]: string } = {
  //         completed: 'مكتمل',
  //         pending: 'في انتظار الموافقة',
  //         cancelled: 'ملغي',
  //         packing: 'يتم تجهيزها',
  //         readyForPickup: 'جاهز للاستلام',
  //         on_way: 'في الطريق',
  //         in_progress: 'يتم تحضير الطلب',
  //         delivered: 'تم التوصيل',
  //       };

  //       const trackingKey = this.invoices[0]?.['tracking-status'];
  //       if (trackingKey === 'completed') {
  //         this.isShow = false;
  //       }
  //       this.trackingStatus = statusMap[trackingKey] || trackingKey;
  //       this.orderNumber = response.data.order_id;
  //       this.couponType = this.invoices[0].invoice_summary.coupon_type;

  //       // console.log(this.couponType, 'couponType');

  //       this.addresDetails = this.invoices[0]?.address_details || {};

  //       this.paymentStatus =
  //         this.invoices[0]?.transactions[0]?.['payment_status'];
  //       // console.log(trackingKey, 'trackingStatus');
  //       //  if (this.trackingStatus === 'completed' ) {
  //       //   this.deliveredButton?.nativeElement.click();
  //       //   }
  //       this.cashierLast = this.invoices[0]?.cashier_info.last_name;
  //       this.cashierFirst = this.invoices[0]?.cashier_info.first_name;
  //       this.paymentMethod =
  //         this.invoices[0]?.transactions[0]?.['payment_method'];
  //       this.isDeliveryOrder = this.invoices?.some(
  //         (invoice: any) => invoice.order_type === 'Delivery'
  //       );
  //       console.log(this.paymentStatus, 'paymentStatus');

  //       this.branchDetails = this.invoices?.map(
  //         (e: { branch_details: any }) => e.branch_details
  //       );
  //       this.orderDetails = this.invoices?.map((e: any) => e.orderDetails);

  //       // this.invoiceSummary = this.invoices?.map((e: any) => e.invoice_summary );
  //       // this.invoiceSummary = this.invoices?.map((e: any) => ({
  //       //   ...e.invoice_summary,
  //       //   currency_symbol: e.currency_symbol,
  //       // }));
  //       this.invoiceSummary = this.invoices?.map((e: any) => {
  //         console.log(e);
  //         let summary = {
  //           ...e.invoice_summary,
  //           currency_symbol: e.currency_symbol,
  //         };

  //         // Convert coupon_value if it's a percentage
  //         // if (summary.coupon_type === 'percentage') {
  //         //   const couponValue = parseFloat(summary.coupon_value); // "10.00" → 10
  //         //   const subtotal = parseFloat(summary.subtotal_price);
  //         //   summary.coupon_value = ((couponValue / 100) * subtotal).toFixed(2); // Convert to currency
  //         // }

  //         return summary;
  //       });
  //       console.log(this.invoiceSummary, 'test tax')

  //       this.addressDetails = this.invoices?.map((e: any) => e.address_details);

  //       if (this.branchDetails?.length) {
  //         this.extractDateAndTime(this.branchDetails[0]);
  //       }
  //     },
  //     error: (error: any) => {
  //       console.error(' Error fetching pill details:', error);
  //     },
  //   });
  // }

  async fetchPillsDetails(pillId: string): Promise<void> {
    try {
      const response: any = await firstValueFrom(
        this.pillDetailsService.getPillsDetailsById(this.pillId)
      );

      this.invoices = response.data.invoices;
      console.log(response, "alaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa");

      const statusMap: { [key: string]: string } = {
        completed: 'مكتمل',
        pending: 'في انتظار الموافقة',
        cancelled: 'ملغي',
        packing: 'يتم تجهيزها',
        readyForPickup: 'جاهز للاستلام',
        on_way: 'في الطريق',
        in_progress: 'يتم تحضير الطلب',
        delivered: 'تم التوصيل',
      };

      const trackingKey = this.invoices[0]?.['tracking-status'];
      if (trackingKey === 'completed') {
        this.isShow = false;
      }
      this.trackingStatus = statusMap[trackingKey] || trackingKey;
      this.orderNumber = response.data.order_id;
      this.couponType = this.invoices[0].invoice_summary.coupon_type;

      this.addresDetails = this.invoices[0]?.address_details || {};
      this.paymentStatus = this.invoices[0]?.transactions[0]?.['payment_status'];
      this.cashierLast = this.invoices[0]?.cashier_info.last_name;
      this.cashierFirst = this.invoices[0]?.cashier_info.first_name;
      this.paymentMethod = this.invoices[0]?.transactions[0]?.['payment_method'];

      this.isDeliveryOrder = this.invoices?.some(
        (invoice: any) => invoice.order_type === 'Delivery'
      );

      this.branchDetails = this.invoices?.map(
        (e: { branch_details: any }) => e.branch_details
      );
      this.orderDetails = this.invoices?.map((e: any) => e.orderDetails);

      this.invoiceSummary = this.invoices?.map((e: any) => {
        let summary = {
          ...e.invoice_summary,
          currency_symbol: e.currency_symbol,
        };
        return summary;
      });

      this.addressDetails = this.invoices?.map((e: any) => e.address_details);

      if (this.branchDetails?.length) {
        this.extractDateAndTime(this.branchDetails[0]);
      }
    } catch (error) {
      console.error('Error fetching pill details:', error);
    }
  }

  async printInvoice() {
    // if (!this.invoices?.length || !this.invoiceSummary?.length) {
    //   console.warn('Invoice data not ready.');
    //   return;
    // }

    try {
      /*       await this.printedInvoiceService
              .printInvoice(
                this.orderedId,
                this.cashier_machine_id,
                this.selectedPaymentMethod
              )
              .toPromise(); */
      // const test = await this.printedInvoiceService
      //   .printInvoice(
      //     this.orderedId,
      //     this.cashier_machine_id,
      //     this.selectedPaymentMethod
      //   )
      //   .toPromise();
      // console.log(test, 'test');
      const printContent = document.getElementById('printSection');
      if (!printContent) {
        console.error('Print section not found.');
        return;
      }

      const originalHTML = document.body.innerHTML;

      const copies = this.isDeliveryOrder
        ? [
          { showPrices: true, test: true },
          { showPrices: false, test: false },
          { showPrices: true, test: true },
        ]
        : [
          { showPrices: true, test: true },
          { showPrices: false, test: false },
        ];

      for (const copy of copies) {
        this.showPrices = copy.showPrices;
        this.test = copy.test;

        await new Promise((resolve) => setTimeout(resolve, 300));

        const singlePageHTML = `
        <div>
          ${printContent.innerHTML}
        </div>
      `;

        document.body.innerHTML = singlePageHTML;

        await new Promise((resolve) =>
          setTimeout(() => {
            window.print();
            resolve(true);
          }, 200)
        );
      }

      document.body.innerHTML = originalHTML;
      /*       location.reload();
       */
      setTimeout(() => {
        location.reload();
      }, 200);
    } catch (error) {
      console.error('Error printing invoice from service:', error);
    }
  }

  // printInvoice() {
  //   this.printedInvoiceService
  //     .printInvoice(this.pillId, this.cashier_machine_id)
  //     .subscribe({
  //       next: (response) => {
  //         console.log('Invoice printed successfully from service:', response);

  //         let printContent = document.getElementById('printSection')!.innerHTML;
  //         let originalContent = document.body.innerHTML;

  //         document.body.innerHTML = printContent;

  //         window.print();

  //         document.body.innerHTML = originalContent;
  //         // location.reload();
  //       },
  //       error: (err) => {
  //         console.error('Error printing invoice from service:', err);
  //       },
  //     });
  // }



  hasDeliveryOrDineIn(): boolean {
    return this.invoices?.some((invoice: { order_type: string }) =>
      ['Delivery', 'Dine-in'].includes(invoice.order_type)
    );
  }
  hasDineInOrder(): boolean {
    return this.invoices?.some(
      (invoice: { order_type: string }) => invoice.order_type === 'Dine-in'
    );
  }
  changePaymentStatus(status: string) {
    this.paymentStatus = status;

    this.cdr.detectChanges();
  }
  changeTrackingStatus(status: string) {
    this.trackingStatus = status.trim();
    console.log('تم تحديث حالة التوصيل:', this.trackingStatus);

    this.cdr.detectChanges();
  }
  saveOrder() {
    if (!this.paymentStatus || !this.trackingStatus) {
      alert('يجب تحديد حالة الدفع وحالة التوصيل قبل الحفظ!');
      return;
    }

    this.orderService
      .updateInvoiceStatus(this.orderNumber, this.paymentStatus, this.trackingStatus)
      .subscribe({
        next: (response) => {
          localStorage.setItem(
            'pill_detail_data',
            JSON.stringify(response.data)
          );
          this.router.navigate(['/pills']);
        },
        error: (err) => {
          console.error(' خطأ في حفظ الطلب:', err);
        },
      });
  }
  getDiscount(): number {
    if (
      !this.couponType ||
      !this.invoices ||
      !this.invoices[0]?.invoice_summary
    ) {
      return 0; // No discount if no coupon is applied or data is missing
    }

    const invoiceSummary = this.invoices[0].invoice_summary;
    const couponValue = parseFloat(invoiceSummary.coupon_value);

    if (isNaN(couponValue)) {
      return 0; // If coupon_value is not a valid number, return 0
    }

    if (this.couponType === 'percentage') {
      return (invoiceSummary.subtotal_price * couponValue) / 100;
    } else if (this.couponType === 'fixed') {
      return couponValue;
    }

    return 0; // Default to 0 if no valid coupon type
  }

  // To get final price after discount
  getFinalPrice(): number {
    return this.invoices[0].invoice_summary.subtotal_price - this.getDiscount();
  }
  selectOrderType(type: string) {
    this.clearOrderTypeData();
    const typeMapping: { [key: string]: string } = {
      'في المطعم': 'dine-in',
      'خارج المطعم': 'Takeaway',
      توصيل: 'Delivery',
    };
    this.selectedOrderType = typeMapping[type] || type;

    localStorage.setItem('selectedOrderType', this.selectedOrderType);
  }

  clearOrderTypeData() {
    // Clear data based on the previously selected order type
    switch (this.selectedOrderType) {
      case 'dine-in':
        // Clear table number and table ID
        this.tableNumber = null;
        localStorage.removeItem('table_number');
        localStorage.removeItem('table_id');
        break;

      case 'Delivery':
        // Clear delivery address, courier, and form data
        // this.address = '';
        // this.clientName = ' ';
        // this.addressPhone = '';
        // localStorage.removeItem('form_data');

        // localStorage.removeItem('address_id');
        break;

      case 'Takeaway':
        // No specific data to clear for Takeaway
        break;

      default:
        break;
    }
  }

  loadOrderType() {
    const savedOrderType = localStorage.getItem('selectedOrderType');
    if (savedOrderType) {
      this.selectedOrderType = savedOrderType;
    }
  }

  openCartItemsModal() {
    const modalRef = this.modalService.open(CartItemsModalComponent, {
      size: 'md',
    });
    modalRef.componentInstance.cartItems = [...this.cartItems];
    modalRef.componentInstance.updateParentCart = (updatedCart: any[]) => {
      this.cartItems = updatedCart;
      this.updateTotalPrice();
      localStorage.setItem('cart', JSON.stringify(this.cartItems));
    };
  }
  applyNote(): void {
    this.savedNote = this.additionalNote;
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach((el) => el.remove());

    const noteModal = document.getElementById('noteModal');
    if (noteModal) {
      const modalInstance = bootstrap.Modal.getInstance(noteModal);
      if (modalInstance) {
        modalInstance.hide();
      }
    }
    localStorage.setItem('additionalNote', this.additionalNote);
    this.closeModal();
  }
  applyAdditionalNote() {
    const orderNote = localStorage.getItem('additionalNote');
    return orderNote;
  }
  removeNote() {
    localStorage.removeItem('additionalNote');
    this.additionalNote = ' ';
    // Attempt to close the modal.
    const modalEl = document.getElementById('noteModal');
    if (modalEl) {
      let bsModal = bootstrap.Modal.getInstance(modalEl);
      if (!bsModal) {
        bsModal = new bootstrap.Modal(modalEl);
      }
      bsModal.hide();

      // ✅ Remove backdrop manually
      const backdrop = document.querySelector('.modal-backdrop');
      if (backdrop) {
        backdrop.remove();
      }

      // ✅ Remove body class if still exists
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('padding-right');
    }
    this.closeModal();
  }
  // clearSelectedCourier() {
  //   this.selectedCourier = null;
  //   localStorage.removeItem('selectedCourier');
  // }
  setCameFromSideDetails(): void {
    localStorage.setItem('cameFromSideDetails', 'true');
  }

  updatePaymentOptions() {
    // If Visa is selected, force "paid" as payment status
    if (this.selectedPaymentMethod === 'visa') {
      this.selectedPaymentStatus = 'paid';
    }

    // If switching to Dine-in, reset payment to Cash
    if (
      this.selectedOrderType === 'dine-in' ||
      this.selectedOrderType === 'في المطعم'
    ) {
      this.selectedPaymentMethod = 'cash';
      this.selectedPaymentStatus = 'paid'; // Default for dine-in
    }
  }
  closeModal() {
    const modals = document.querySelectorAll('.modal.show');
    modals.forEach((modalEl: any) => {
      const modalInstance = bootstrap.Modal.getInstance(modalEl);
      if (modalInstance) {
        modalInstance.hide();
      }
    });
    // إزالة أي Backdrop يدويًا
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach((backdrop) => backdrop.remove());
  }
  onPaymentStatusChange() {
    // const savedStatus = localStorage.getItem('selectedPaymentStatus');
    // this.selectedPaymentStatus = savedStatus || 'unpaid';
    console.log('Payment Status:', this.selectedPaymentStatus); // paid or unpaid
    if (this.selectedPaymentStatus == 'unpaid') {
      this.cash_amountt = 0;
      this.credit_amountt = 0;
    }
    localStorage.setItem('selectedPaymentStatus', this.selectedPaymentStatus);
  }
  sharedOrderId: any;

  // saveOrderToLocalStorage() {
  //   this.falseMessage = '';
  //   const branchId = Number(localStorage.getItem('branch_id')) || null;
  //   const tableId = Number(localStorage.getItem('table_id')) || null;
  //   // ===== INVOICE SUMMARY =====
  //   const taxEnabled = this.branchData.tax_application;
  //   const couponEnabled = this.branchData.coupon_application;
  //   const rawSubtotal = this.cartItems.reduce(
  //     (sum, item) => sum + (this.getItemTotal(item) || item.price),
  //     0
  //   );
  //   let subtotal = rawSubtotal;
  //   if (this.appliedCoupon) {
  //     if (taxEnabled && !couponEnabled) {
  //       subtotal = this.appliedCoupon.amount_after_coupon + this.getTax();
  //     } else {
  //       subtotal = this.appliedCoupon.amount_after_coupon;
  //     }
  //   }
  //   subtotal = Math.max(subtotal, 0);
  //   let taxAmount = 0;
  //   if (!taxEnabled) {
  //     taxAmount = subtotal * (this.branchData.tax_percentage / 100);
  //   }
  //   let serviceFee = 0;
  //   if (this.selectedOrderType === 'في المطعم' || this.selectedOrderType === 'dine-in') {
  //     serviceFee = this.getServiceFeeAmount();
  //   }
  //   let deliveryFee = 0;
  //   if (this.selectedOrderType === 'توصيل' || this.selectedOrderType === 'Delivery') {
  //     deliveryFee = this.branchData.delivery_fees;
  //   }
  //   const total = subtotal + taxAmount + serviceFee + deliveryFee;
  //   const authToken = localStorage.getItem('authToken');
  //   const cashier_machine_id = localStorage.getItem('cashier_machine_id');
  //   const paymentStatus = this.selectedPaymentMethod === 'cash' ? this.selectedPaymentStatus : 'paid';
  //   console.log(this.selectedPaymentMethod, 'selectedPaymentMethod');
  //   const orderData: any = {
  //     type: this.selectedOrderType,
  //     branch_id: branchId,
  //     payment_method: this.selectedPaymentMethod,
  //     payment_status: paymentStatus,
  //     cash_amount: this.cash_amountt, ///////////////// alaa
  //     credit_amount: this.credit_amountt,
  //     cashier_machine_id: cashier_machine_id,
  //     note:
  //       this.additionalNote ||
  //       this.savedNote ||
  //       this.applyAdditionalNote() ||
  //       '',
  //     items: this.cartItems
  //       .map((item) => ({
  //         dish_id: item.dish?.id || null,
  //         dish_name: item.dish?.name || '',
  //         dish_description: item.dish?.description || '',
  //         dish_price: item.dish?.price || 0,
  //         currency_symbol: item.dish?.currency_symbol || '',
  //         dish_image: item.dish?.image || null,
  //         quantity: item.quantity || 1,
  //         sizeId: item.selectedSize?.id || null,
  //         size: item.size || '',
  //         sizeName: item.selectedSize?.name || '',
  //         sizeDescription: item.selectedSize?.description || '',
  //         note: item.note || '',
  //         finalPrice: item.finalPrice || 0,
  //         selectedAddons: item.selectedAddons || [],
  //         addon_categories: item.addon_categories
  //           ?.map((category: { id: any; addons: { id: any }[] }) => {
  //             const selectedAddons = category.addons?.filter((addon) =>
  //               item.selectedAddons.some(
  //                 (selected: { id: any }) => selected.id === addon.id
  //               )
  //             );
  //             return selectedAddons.length > 0
  //               ? {
  //                 id: category.id,
  //                 addon: selectedAddons.map((addon) => addon.id),
  //               }
  //               : null;
  //           })
  //           .filter((category: null) => category !== null),
  //       }))
  //       .filter((item) => item.dish_id),
  //   };
  //   if (this.appliedCoupon && this.couponCode?.trim()) {
  //     orderData.coupon_code = this.couponCode.trim();
  //     orderData.discount_amount = this.discountAmount;
  //     orderData.coupon_type = this.appliedCoupon.value_type;
  //   } else if (this.couponCode?.trim()) {
  //     orderData.coupon_code = this.couponCode.trim();
  //   }
  //   const invoiceSummary = {
  //     subtotal_price: rawSubtotal.toFixed(2),
  //     coupon_value: (this.discountAmount || 0).toFixed(2),
  //     delivery_fees: deliveryFee.toFixed(2),
  //     service_fee: serviceFee.toFixed(2),
  //     tax_application: taxEnabled ? 1 : 0,
  //     tax_percentage: this.branchData.tax_percentage || 0,
  //     tax_value: taxAmount.toFixed(2),
  //     total_price: total.toFixed(2),
  //     currency_symbol: "د.ك"
  //   };
  //   let addressData: any = {};
  //   if (this.selectedOrderType.toLowerCase() === "delivery") {
  //     const formData = JSON.parse(localStorage.getItem("form_data") || '{}');
  //     addressData = {
  //       client_name: formData.client_name || '',
  //       address_phone: formData.address_phone || '',
  //       country_code: formData.country_code || '',
  //       apartment_number: formData.apartment_number || '',
  //       building: formData.building || '',
  //       address_type: formData.address_type || '',
  //       address: formData.address || '',
  //       propertyType: formData.propertyType || '',
  //       buildingName: formData.buildingName || '',
  //       note: formData.note || '',
  //       floor_number: formData.floor_number || '',
  //       landmark: formData.landmark || '',
  //       villaName: formData.villaName || '',
  //       villaNumber: formData.villaNumber || '',
  //       companyName: formData.companyName || '',
  //       buildingNumber: formData.buildingNumber || ''
  //     };
  //   }
  //   if (!this.cartItems.length) {
  //     this.falseMessage = 'العربة فارغة، أضف بعض العناصر.';
  //     setTimeout(() => {
  //       this.falseMessage = '';
  //     }, 1500);
  //     return;
  //   }
  //   if (!this.selectedOrderType) {
  //     this.falseMessage = 'يرجى تحديد نوع الطلب قبل المتابعة.';
  //     setTimeout(() => {
  //       this.falseMessage = '';
  //     }, 1500);
  //     return;
  //   }
  //   if (!branchId) {
  //     this.falseMessage = 'فشل تحديد الفرع. الرجاء إعادة تسجيل الدخول.';
  //     setTimeout(() => {
  //       this.falseMessage = '';
  //     }, 1500);
  //     this.isLoading = false;
  //     this.loading = false;
  //     return;
  //   }
  //   if (!authToken) {
  //     this.falseMessage = 'فشل التحقق من الهوية. الرجاء تسجيل الدخول مجددًا.';
  //     setTimeout(() => {
  //       this.falseMessage = '';
  //     }, 1500);
  //     this.isLoading = false;
  //     this.loading = false;
  //     return;
  //   }
  //   if (!orderData.items.length) {
  //     this.falseMessage = 'لا يمكن تقديم الطلب بدون عناصر صالحة.';
  //     setTimeout(() => {
  //       this.falseMessage = '';
  //     }, 1500);
  //     this.isLoading = false;
  //     this.loading = false;
  //     return;
  //   }
  //   if (!this.cartItems.length) {
  //     this.falseMessage = "🛒 العربة فارغة، أضف بعض العناصر.";
  //     return;
  //   }
  //   if (
  //     this.selectedOrderType === 'dine-in' ||
  //     this.selectedOrderType === 'في المطعم'
  //   ) {
  //     orderData.table_id = tableId;
  //   }
  //   // Save to local storage
  //   let savedOrders = JSON.parse(localStorage.getItem('savedOrders') || '[]');
  //   if (!Array.isArray(savedOrders)) savedOrders = [];

  //   savedOrders.push(orderData);
  //   if (savedOrders.length > 10) savedOrders.shift();

  //   localStorage.setItem('savedOrders', JSON.stringify(savedOrders));

  //   // Cleanup
  //   this.clearCart();
  //   this.couponCode = '';
  //   this.appliedCoupon = null;
  //   this.discountAmount = 0;
  //   this.savedNote = '';
  //   this.updateTotalPrice();
  //   localStorage.removeItem('form_data');

  //   this.falseMessage = "✅ تم حفظ الطلب بنجاح!";
  //   console.log("✅ Order saved:", orderData);
  // }
  existingOrderId: string | null = null;

  saveOrderToLocalStorage() {
    const branchId = Number(localStorage.getItem('branch_id')) || null;
    const tableId = Number(localStorage.getItem('table_id')) || null;
    const addressId = Number(localStorage.getItem('address_id')) || null;
    const cashier_machine_id = localStorage.getItem('cashier_machine_id');

    if (!branchId) {
      this.falseMessage = '❌ فشل تحديد الفرع. الرجاء إعادة تسجيل الدخول.';
      return;
    }

    if (!this.selectedOrderType) {
      this.falseMessage = '❌ يرجى تحديد نوع الطلب.';
      return;
    }

    if (!this.cartItems.length) {
      this.falseMessage = '🛒 العربة فارغة، أضف بعض العناصر.';
      return;
    }
    const finalOrderId = localStorage.getItem('finalOrderId');
    const orderIdToUse =
      finalOrderId || Math.random().toString(36).substring(2, 10);

    const paymentStatus =
      this.selectedPaymentMethod === 'cash'
        ? this.selectedPaymentStatus
        : 'paid';

    // ===== INVOICE SUMMARY =====
    const taxEnabled = this.branchData.tax_application;
    const couponEnabled = this.branchData.coupon_application;

    // const rawSubtotal = this.cartItems.reduce(
    //   (sum, item) => sum + (this.getItemTotal(item) || item.price),
    //   0
    // );

    let subtotal = this.getTotal();

    if (this.appliedCoupon) {
      if (taxEnabled && !couponEnabled) {
        subtotal = this.appliedCoupon.amount_after_coupon + this.getTax();
      } else {
        subtotal = this.appliedCoupon.amount_after_coupon;
      }
    }

    subtotal = Math.max(subtotal, 0);

    let taxAmount = 0;
    if (!taxEnabled) {
      taxAmount = subtotal * (this.branchData.tax_percentage / 100);
    }

    let serviceFee = 0;
    if (
      this.selectedOrderType === 'في المطعم' ||
      this.selectedOrderType === 'dine-in'
    ) {
      if (!couponEnabled && !taxEnabled
        && this.appliedCoupon) {
        serviceFee = this.getServiceOnAmountAfterCoupon()
      } else {
        serviceFee = this.getServiceFeeAmount();

      }
    }

    let deliveryFee = 0;
    if (
      this.selectedOrderType === 'توصيل' ||
      this.selectedOrderType === 'Delivery'
    ) {
      deliveryFee = this.delivery_fees;
    }

    // const total = subtotal + taxAmount + serviceFee + deliveryFee;

    const invoiceSummary = {
      subtotal_price: this.getTotal().toFixed(2),
      coupon_value: (this.discountAmount || 0).toFixed(2),
      delivery_fees: deliveryFee.toFixed(2),
      service_fee: serviceFee.toFixed(2),
      tax_application: taxEnabled ? 1 : 0,
      tax_percentage: this.branchData.tax_percentage || 0,
      tax_value: this.getTax(),
      total_price: this.getCartTotal(),
      currency_symbol: this.currencySymbol,
    };
    let addressData: any = {};
    if (this.selectedOrderType.toLowerCase() === 'delivery') {
      const formData = JSON.parse(localStorage.getItem('form_data') || '{}');

      // if (Object.keys(formData).length === 0) {
      //   this.falseMessage = "❌ لا توجد بيانات للاسترجاع.";
      //   return;
      // }

      addressData = {
        client_name: formData.client_name || '',
        address_phone: formData.address_phone || '',
        country_code: formData.country_code.code || '',
        apartment_number: formData.apartment_number || '',
        building: formData.building || '',
        address_type: formData.address_type || '',
        address: formData.address || '',
        propertyType: formData.propertyType || '',
        buildingName: formData.buildingName || '',
        note: formData.note || '',
        floor_number: formData.floor_number || '',
        landmark: formData.landmark || '',
        villaName: formData.villaName || '',
        villaNumber: formData.villaNumber || '',
        companyName: formData.companyName || '',
        buildingNumber: formData.buildingNumber || '',
      };
    }

    // ===== ORDER DATA =====
    const orderData: any = {
      orderId: orderIdToUse,
      coupon_code: this.couponCode || null,
      type: this.selectedOrderType,
      branch_id: branchId,
      payment_method: this.selectedPaymentMethod,
      payment_status: paymentStatus,
      cash_amount: this.cash_amountt || null,
      credit_amount: this.credit_amountt || null,
      cashier_machine_id: cashier_machine_id,
      client_country_code: this.selectedCountry.code || "+20",
      client_phone: this.clientPhone,
      note: this.additionalNote || this.onholdOrdernote || '',
      tableNumber: this.tableNumber || this.table_number,
      table_id: tableId || this.table_id,
      created_at: new Date().toISOString(),
      order_items_count: this.getTotalItemCount(),
      invoiceSummary,
      addresses: addressData,
      items: this.cartItems
        .map((item) => ({
          dish_name: item.dish.name,
          dish_image: item.dish.image,
          dish_desc: item.dish.description || null,
          dish_price: item.dish.price ?? 0,
          final_price: this.getItemTotal(item) ?? 0,
          dish_id: item.dish?.id || null,
          quantity: item.quantity || 1,
          size_name: item.selectedSize?.name || null,
          size_price: item.selectedSize?.price ?? 0,
          sizeId: item.selectedSize?.id || null,
          note: item.note || '',
          addon_categories: item.addon_categories
            ?.map((category: { id: any; name?: string; addons: any[] }) => {
              const selectedAddons = category.addons?.filter((addon) =>
                item.selectedAddons?.some(
                  (selected: { id: any }) => selected.id === addon.id
                )
              );
              return selectedAddons.length > 0
                ? {
                  id: category.id,
                  name: category.name || '',
                  addons: selectedAddons.map((addon) => ({
                    id: addon.id,
                    name: addon.name,
                    price: addon.price,
                  })),
                }
                : null;
            })
            .filter((category: any) => category !== null),
        }))
        .filter((item) => item.dish_id),
    };

    if (!orderData.items.length) {
      this.falseMessage = '❌ الطلب لا يحتوي على عناصر صالحة.';
      return;
    }

    // Save to local storage
    let savedOrders = JSON.parse(localStorage.getItem('savedOrders') || '[]');
    if (!Array.isArray(savedOrders)) savedOrders = [];

    const existingIndex = savedOrders.findIndex(
      (o: any) => o.orderId === orderIdToUse
    );

    if (existingIndex !== -1) {
      // Update existing order
      savedOrders[existingIndex] = orderData;
    } else {
      // Save new order
      savedOrders.push(orderData);
    }

    localStorage.setItem('savedOrders', JSON.stringify(savedOrders));

    // Cleanup
    this.clearCart();
    this.couponCode = '';
    this.appliedCoupon = null;
    this.discountAmount = 0;
    this.additionalNote = ''
    this.savedNote = '';
    this.updateTotalPrice();
    localStorage.removeItem('form_data');
    localStorage.removeItem('notes');
    localStorage.removeItem('additionalNote');
    localStorage.removeItem('selectedPaymentStatus');
    localStorage.removeItem('cash_amountt');
    localStorage.removeItem('credit_amountt');
    localStorage.removeItem('table_number');
    localStorage.removeItem('table_id');
    localStorage.removeItem('address_id');
    localStorage.removeItem('selectedHotel');
    localStorage.removeItem('hotel_id');
    localStorage.removeItem('selectedPaymentStatus');
    localStorage.removeItem('delivery_fees');
    localStorage.removeItem('selected_address');
    localStorage.removeItem('client');
    localStorage.removeItem('clientPhone');
    this.client = " ";
    this.clientPhone = " "
    this.cash_amountt = 0;
    this.credit_amountt = 0;
    this.selectedPaymentStatus = '';
    this.resetAddress()
    this.tableNumber = null;
    this.FormDataDetails = null;
    this.successMessage = 'تم حفظ طلبك بنجاح';
    this.successModal.show();
    localStorage.removeItem('finalOrderId');
    this.finalOrderId = " ";
    console.log('✅ Order saved:', orderData);
  }
  resetAddress() {
    this.clientName = undefined;
    this.address = '';
    this.addressPhone = '';
  }

  // isLoading: boolean = false;


  clientInfoApplied = false; // ✅ show info now

  applyClientInfo() {
    this.isLoading = true;

    // Save to localStorage
    localStorage.setItem('client', this.client);
    localStorage.setItem('clientPhone', this.clientPhone);
    localStorage.setItem('selectedCountryCode', this.selectedCountry.code);

    this.clientStoredInLocal = this.client
    this.clientPhoneStoredInLocal = this.clientPhone
    // Simulate async saving
    setTimeout(() => {
      this.isLoading = false;
      this.clientInfoApplied = true; // ✅ show info now

      // Optionally close modal here
      this.closeModal()

    }, 500);
  }

  clearClientInfo() {
    // Clear values from component
    this.client = '';
    this.clientPhone = '';
    this.clientStoredInLocal = null;
    this.clientPhoneStoredInLocal = null
    // Remove from localStorage
    localStorage.removeItem('client');
    localStorage.removeItem('selectedCountryCode');
    localStorage.removeItem('clientName');
    localStorage.removeItem('clientPhone');

    console.log('Client info cleared');
  }

  closeClientModal() {
    // Optional: you can reset or keep values when closing the modal
    this.clearClientInfo(); // or remove this line if you want to keep input filled
  }
  fetchCountries() {
    this.authService.getCountries().subscribe({
      next: (response) => {
        if (response.data && Array.isArray(response.data)) {
          this.countryList = response.data.map(
            (country: { phone_code: string; image: string }) => ({
              code: country.phone_code,
              flag: country.image,
            })
          );
          const allowedCountryCodes: string[] = ['+20', '+962', '+964', '+212', '+963', '+965', '+966'];
          this.filteredCountries = [...this.countryList]; // Initialize filteredCountries
          this.filteredCountries = this.filteredCountries.filter((country: any) =>
            allowedCountryCodes.includes(country.code.replace(/\s+/g, '').replace(' ', '').replace('ـ', '').replace('–', ''))
          );

        } else {
          this.errorMessage = 'No country data found in the response.';
        }
      },
      error: () => {
        this.errorMessage = 'Failed to load country data.';
      },
    });
  }

  toggleDropdown() {
    this.dropdownOpen = !this.dropdownOpen;
  }

  selectCountry(country: Country) {
    this.selectedCountry = country;
    this.dropdownOpen = false;
    this.searchTerm = ''; // Clear search term after selection
    this.filteredCountries = [...this.countryList]; // Reset filtered list
    this.selectedCountryCode = country.code;

    localStorage.setItem('selectedCountryCode', country.code);
    console.log('Selected country:', this.selectedCountry);
  }

  filterCountries() {
    this.filteredCountries = this.countryList.filter((country) =>
      country.code.toLowerCase().includes(this.searchTerm.toLowerCase())
    );
  }

}
