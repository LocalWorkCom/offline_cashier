import {
  Component,
  Input,
  OnInit,
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  DoCheck,
  AfterViewInit,
  ElementRef,
  ViewChild,
  TemplateRef,
  ɵsetAllowDuplicateNgModuleIdsForTest,
  inject,
  OnDestroy,
} from '@angular/core';
import { ProductsService } from '../services/products.service';
import { PlaceOrderService } from '../services/place-order.service';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
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
//start hanan
import { IndexeddbService } from '../services/indexeddb.service';
import { SyncService } from '../services/sync.service';
//end hanan

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
export class SideDetailsComponent implements OnInit, AfterViewInit {
  @ViewChild('printedPill') printedPill!: ElementRef;
  @ViewChild('couponModalRef') couponModalRef!: ElementRef;
  @ViewChild('tipModalContent') tipModalContent!: TemplateRef<any>;

  translate = inject(TranslateService);
  private destroy$ = new Subject<void>();
  isOnline: boolean = navigator.onLine;
  private onlineHandler?: () => void;
  private offlineHandler?: () => void;
  pendingOrdersCount: number = 0;
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
  isUpdatingPrices: boolean = false;
  additionalNote: string = '';
  savedNote: string = '';
  addressIdformData: any = null;

  selectedOrderType: any;
  selectedPaymentMethod: any;
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
  referenceNumberTouched: boolean = false;
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
  // hanan front
  isOrderTypeSelected: boolean = false;

  selectedPaymentSuggestion: number | null = null;
  // selectedPaymentMethod: 'cash' | 'credit' | 'cash + credit' | null = null;
  // متغيرات لتخزين البيانات مؤقتاً عند فتح المودال
  tempBillAmount: number = 0;
  tempPaymentAmount: number = 0;
  tempChangeAmount: number = 0;
  // المتغير الجديد لتخزين المبلغ الذي أدخله أو اختاره الكاشير للدفع
  cashPaymentInput: any = " ";
  // المتغيرات الجديدة للدفع المختلط
  cashAmountMixed: any = " ";
  creditAmountMixed: any = " ";
  tip_aption: any;
  isPaymentInputTouched: boolean = false;

  Math = Math;

  // Memoized total to avoid recalculation each CD cycle
  private _cachedTotal: number | null = null;
  private _cachedCartItemsHash: string | null = null;

  finalTipSummary: {
    total: number; // المجموع قبل رسوم الخدمة
    serviceFee: number; // رسوم الخدمة
    billAmount: number; // المجموع الفرعي (المبلغ المستحق للدفع)
    paymentAmount: number; // قيمة الدفع الفعلية
    paymentMethod: string; // طريقة الدفع (كاش/فيزا/مختلط)
    tipAmount: number; // الإكرامية المعتمدة
    grandTotalWithTip: number; // المجموع الكلي مع الإكرامية
    changeToReturn: number; // المتبقي للرد
    cashAmountMixed?: any; // المبلغ المدفوع كاش في الدفع المختلط
    creditAmountMixed?: any; // المبلغ المدفوع فيزا في الدفع المختلط
    additionalPaymentRequired?: number; // ✅ جديد
    originalPaymentAmount?: number;     // ✅ جديد
    tips_aption?: string;
  } | null = null;
  // متغيرات لإدارة خيارات الإكرامية داخل المودال
  selectedTipType: 'tip_the_change' | 'tip_specific_amount' | 'no_tip' = 'no_tip';
  specificTipAmount: number = 0; // المبلغ الذي يتم إدخاله يدوياً كإكرامية
  selectedSuggestionType: 'billAmount' | 'amount50' | 'amount100' | null = null; // متغير جديد لتخزين نوع الاقتراح

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
    // start hanan
    private dbService: IndexeddbService,
    private syncService: SyncService
    // end hanan
  ) {
    this.cashier_machine_id = this.getCashierMachineId();
  }
  // رسالة خطأ إدخال الدفع (تُستخدم في القالب)
  paymentError: string = '';

  // تقريب قيمة الدفع النقدي لعدد عشريين وتحديث الحالة
  roundCashPayment(): void {
    // try {
    //   // إذا كان لديك حقل إدخال للنقد في القالب يربط بـ cashPaymentInput
    //   if (typeof this.cashPaymentInput === 'number') {
    //     this.cashPaymentInput = Number((this.cashPaymentInput || 0).toFixed(2));
    //     // ✅ إضافة تحقق إضافي هنا
    //     if (this.cashPaymentInput < 0) {
    //       this.paymentError = 'لا يمكن إدخال مبلغ سالب';
    //     } else if (this.cashPaymentInput === 0) {
    //       this.paymentError = 'يرجى إدخال مبلغ أكبر من الصفر';
    //     } else {
    //       this.paymentError = ''; // مسح الخطأ إذا كان المبلغ صحيحاً
    //     }
    //   }
    //   // لو كان هناك استخدام مباشر لمبلغ الكاش الرئيسي
    //   if (typeof this.cash_amountt === 'number') {
    //     this.cash_amountt = Number((this.cash_amountt || 0).toFixed(2));
    //   }
    //   // مسح رسالة الخطأ عند أي تغير صحيح
    //   this.paymentError = '';
    //   this.cdr.markForCheck();
    // } catch (_) {
    //   // تجاهل الخطأ، فقط تأكد من عدم كسر القالب
    //   this.paymentError = 'حدث خطأ في معالجة المبلغ المدخل';
    // }
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

  // ngOnInit(): void {
  //   // start hanan
  //   this.setupNetworkListeners();
  //   this.checkPendingOrders();

  //   // Sync pending orders if online on init
  //   if (this.isOnline) {
  //     this.syncPendingOrders();
  //   }

  //   this.syncService.retryOrders$.subscribe(() => {
  //     // this.retryPendingOrders(); // 👈 دي الفانكشن اللي عندك
  //     // Also sync raw orderData
  //     this.syncPendingOrders();
  //   });

  //   // Load client info from IndexedDB
  //   this.loadClientInfoFromIndexedDB();
  //   // end hanan
  //   // Subscribe to cart changes
  //   this.productsService.cart$.subscribe(cart => {
  //     this.cartItems = cart;
  //     this.updateTotalPrice();

  //     // ✅ If coupon is applied → recheck automatically
  //     if (this.appliedCoupon) {
  //       this.applyCoupon();
  //     }
  //   });

  //   this.loadBranchData();
  //   this.restoreCoupon();
  //   // this.loadSelectedCourier();
  //   // this.applyAdditionalNote();
  //   // this.loadCouponFromLocalStorage();
  //   this.loadFormData();
  //   this.loadOrderType();
  //   this.loadTableNumber();
  //   this.fetchCountries();
  //   this.loadAdditionalNote();
  //   // this.route.paramMap.subscribe((params) => {
  //   //   this.pillId = params.get('id');

  //   //   if (this.pillId) {
  //   //     this.fetchPillsDetails(this.pillId);
  //   //   }
  //   // });

  //   // this.fetchTrackingStatus();
  //   // this.getNoteFromLocalStorage();
  //   this.cashier_machine_id = Number(
  //     localStorage.getItem('cashier_machine_id')
  //   );
  //   const storedData: string | null = localStorage.getItem('balanceData');

  //   if (storedData !== null) {
  //     // Safe to parse since storedData is guaranteed to be a string
  //     const transactionDataFromLocalStorage = JSON.parse(storedData);

  //     // Access the cashier_machine_id
  //     this.cashier_machine_id =
  //       transactionDataFromLocalStorage.cashier_machine_id;

  //     console.log(this.cashier_machine_id, 'one'); // Output: 1
  //   } else {
  //     console.log('No data found in localStorage.');
  //   }
  //   const storedItems = localStorage.getItem('cart');
  //   if (storedItems) {
  //     this.cartItems = JSON.parse(storedItems);
  //   }

  //   const storedFormData = localStorage.getItem('FormDataDetails');
  //   if (storedFormData) {
  //     this.FormDataDetails = JSON.parse(storedFormData);
  //   }

  //   this.selectedOrderType = localStorage.getItem('selectedOrderType');
  //   this.finalOrderId = localStorage.getItem('finalOrderId');
  //   const savedOrder = localStorage.getItem('savedOrders');
  //   if (savedOrder) {
  //     const parsedOrders = JSON.parse(savedOrder);
  //     const targetOrder = parsedOrders.find((order: any) => order.orderId === this.finalOrderId);
  //     this.onholdOrdernote = targetOrder?.note || '';
  //     this.table_number = targetOrder?.tableNumber || '';
  //     this.table_id = targetOrder?.table_id || '';
  //     this.payment_status = targetOrder?.payment_status || '';
  //     this.credit_amount = targetOrder?.credit_amount || '';
  //     this.cash_amount = targetOrder?.cash_amount || '';
  //     this.coupon_Code = this.validCoupon ? targetOrder?.coupon_code || '' : '';
  //     this.coupon_value = this.validCoupon ? targetOrder?.invoiceSummary?.coupon_value || '' : null;

  //     console.log(this.onholdOrdernote);

  //   }


  //   this.couponCode = localStorage.getItem('couponCode') || this.coupon_Code || '';
  //   if (this.couponCode) {
  //     this.applyCoupon()
  //   }
  //   this.selectedPaymentStatus =
  //     localStorage.getItem('selectedPaymentStatus') || this.payment_status || '';

  //   const savedCash = localStorage.getItem('cash_amountt');
  //   const savedCredit = localStorage.getItem('credit_amountt');
  //   const delivery_fees = localStorage.getItem('delivery_fees');

  //   this.cash_amountt = Number(savedCash) || this.cash_amount;
  //   this.credit_amountt = Number(savedCredit) || this.credit_amount;
  //   this.selectedPaymentStatus = "unpaid"

  //   const savedCode = localStorage.getItem('selectedCountryCode');
  //   if (savedCode) {
  //     this.selectedCountry.code = savedCode;
  //     this.selectedCountryCode = savedCode; // If you use a separate property
  //   }


  //   // Load initial cart from localStorage
  //   // const storedCart = localStorage.getItem('cart');
  //   // this.cartItems = storedCart ? JSON.parse(storedCart) : [];
  //   const saved = localStorage.getItem('currentOrderData');
  //   if (saved) {
  //     this.currentOrderData = JSON.parse(saved);
  //     console.log("✅ الطلب الجاري:", this.currentOrderData);
  //   }
  //   const orderId = localStorage.getItem('currentOrderId');
  //   if (orderId) {
  //     this.currentOrderId = +orderId; // خزناه عشان نستخدمه مع API
  //     console.log("🔄 نستكمل الطلب برقم:", this.currentOrderId);
  //   }
  //   // const storedCart = localStorage.getItem('cart');
  //   // this.cartItems = storedCart ? JSON.parse(storedCart) : [];

  //   // const holdCart = localStorage.getItem('holdCart');
  //   // if (holdCart) {
  //   //   const holdItems = JSON.parse(holdCart);

  //   //   this.cartItems = [...this.cartItems, ...holdItems];
  //   // }

  //   // localStorage.setItem('cart', JSON.stringify(this.cartItems));
  //   this.loadCart();

  //   this.updateTotalPrice();
  //   this.cdr.detectChanges();
  //   // ✅ الشرط المطلوب: إذا كان الطلب من طلبات وغير مدفوع
  //   if (this.selectedOrderType === 'talabat' && this.selectedPaymentStatus === 'unpaid') {
  //     this.selectedPaymentMethod = 'deferred';
  //     console.log('✅ تم تعيين طريقة الدفع تلقائياً إلى "آجل"');
  //   }

  //   // إذا كان الطلب من طلبات ومدفوع
  //   if (this.selectedOrderType === 'talabat' && this.selectedPaymentStatus === 'paid') {
  //     this.selectedPaymentMethod = 'cash';
  //   }
  //   this.initializePaymentAmount();
  // }

  ngOnInit(): void {

    // console.log(navigator.onLine);
    // Subscribe to cart changes
    this.productsService.cart$.subscribe(cart => {
      this.cartItems = cart;
      this.updateTotalPrice();
      // ✅ إذا كان الكوبون مطبق، أعد التحقق تلقائياً
      if (this.appliedCoupon && this.validCoupon) {
        setTimeout(() => {
          this.applyCoupon();
        }, 300);
      }
      // ✅ If coupon is applied → recheck automatically
      if (this.appliedCoupon) {
        this.applyCoupon();
      }
    });

    this.loadBranchData();
    this.restoreCoupon();
    // this.loadSelectedCourier();
    // this.applyAdditionalNote();
    // this.loadCouponFromLocalStorage();
    this.loadFormData();
    this.loadOrderType();
    this.loadTableNumber();
    this.fetchCountries();
    this.loadAdditionalNote();
    // this.route.paramMap.subscribe((params) => {
    //   this.pillId = params.get('id');

    //   if (this.pillId) {
    //     this.fetchPillsDetails(this.pillId);
    //   }
    // });

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
      this.coupon_Code = this.validCoupon ? targetOrder?.coupon_code || '' : '';
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

    // Setup network listeners to sync pending orders when connection is restored
    this.setupNetworkListeners();

    // If already online, try to sync any pending orders
    // if (this.isOnline) {
    //   this.syncPendingOrders();

    // }
    // this.syncService.retryOrders$.subscribe(() => {
    //   // this.retryPendingOrders(); // 👈 دي الفانكشن اللي عندك
    //   // Also sync raw orderData
    //   this.syncPendingOrders();
    // });


    // Load initial cart from localStorage
    // const storedCart = localStorage.getItem('cart');
    // this.cartItems = storedCart ? JSON.parse(storedCart) : [];
    const saved = localStorage.getItem('currentOrderData');
    if (saved) {
      this.currentOrderData = JSON.parse(saved);
      console.log("✅ الطلب الجاري:", this.currentOrderData);
    }
    const orderId = localStorage.getItem('currentOrderId');
    if (orderId) {
      this.currentOrderId = +orderId; // خزناه عشان نستخدمه مع API
      console.log("🔄 نستكمل الطلب برقم:", this.currentOrderId);
    }
    // const storedCart = localStorage.getItem('cart');
    // this.cartItems = storedCart ? JSON.parse(storedCart) : [];

    // const holdCart = localStorage.getItem('holdCart');
    // if (holdCart) {
    //   const holdItems = JSON.parse(holdCart);

    //   this.cartItems = [...this.cartItems, ...holdItems];
    // }

    // localStorage.setItem('cart', JSON.stringify(this.cartItems));
    this.loadCart();

    this.updateTotalPrice();
    this.cdr.detectChanges();
    // this.loadSavedCoupon();

  }

  // private loadSavedCoupon(): void {
  //   const hasAppliedCoupon = localStorage.getItem('appliedCoupon') === 'true';
  //   const couponCode = localStorage.getItem('couponCode');
  //   const discountAmount = localStorage.getItem('discountAmount');
  //   const couponType = localStorage.getItem('couponType');
  //   const couponApplyType = localStorage.getItem('couponApplyType'); // 🔥 جديد

  //   console.log('🔄 Loading saved coupon:', {
  //     hasAppliedCoupon,
  //     couponCode,
  //     discountAmount,
  //     couponType,
  //     couponApplyType // 🔥 جديد
  //   });

  //   if (hasAppliedCoupon && couponCode) {
  //     this.validCoupon = true;
  //     this.couponCode = couponCode;
  //     this.discountAmount = parseFloat(discountAmount || '0');
  //     this.couponType = couponType || '';
  //     this.appliedCoupon = {
  //       code: couponCode,
  //       coupon_title: localStorage.getItem('couponTitle') || couponCode,
  //       coupon_value: localStorage.getItem('couponValue') || discountAmount,
  //       value_type: couponType,
  //       coupon_apply_type: couponApplyType || 'order',
  //       amount_after_coupon: this.getTotal() - this.discountAmount,
  //       total_discount: this.discountAmount,
  //       currency_symbol: this.currencySymbol
  //     };

  //     console.log('✅ Restored coupon from localStorage:', {
  //       code: this.couponCode,
  //       discount: this.discountAmount,
  //       type: this.couponType,
  //       applyType: couponApplyType // 🔥 جديد
  //     });

  //     // 🔥 التعديل المهم: تطبيق الكوبون فوراً بعد تحميل الكارت
  //     setTimeout(() => {
  //       console.log('🔄 Applying restored coupon...');
  //       this.applyCoupon();
  //     }, 1000);
  //   }
  // }
  // start hanan
  private initializePaymentAmount(): void {
    const cartTotal = this.getCartTotal();

    // تعيين قيمة المجموع الكلي في حقل الدفع اليدوي
    this.cashPaymentInput = " ";

    // إذا كان هناك finalTipSummary، تحديثه أيضاً
    if (this.finalTipSummary) {
      this.finalTipSummary = {
        ...this.finalTipSummary,
        paymentAmount: cartTotal,
        billAmount: cartTotal,
        tips_aption: this.selectedTipType // ⬅️ تحديث tips_aption
      };
    }
    const billAmount = this.finalTipSummary?.billAmount ?? this.getCartTotal();

    if (this.selectedPaymentMethod === 'cash') {
      this.cash_amountt = billAmount;
      this.cash_amount = billAmount;
    }

    // ✅ تعيين credit_amount تلقائياً إذا كانت طريقة الدفع فيزا
    if (this.selectedPaymentMethod === 'credit') {
      this.credit_amountt = billAmount;
      this.credit_amount = billAmount;
    }

    console.log('💰 تم تعيين مبلغ الدفع تلقائياً:', cartTotal);
  }

  private setupNetworkListeners(): void {
    // Store handler references for cleanup
    this.onlineHandler = () => {
      this.isOnline = true;
      console.log('🌐 Online - attempting to sync pending orders');
      // this.syncPendingOrders();
    };

    this.offlineHandler = () => {
      this.isOnline = false;
      console.log('📴 Offline - orders will be saved locally');
    };

    window.addEventListener('online', this.onlineHandler);
    window.addEventListener('offline', this.offlineHandler);
  }

  // Sync pending orders using raw orderData saved for API
  async syncPendingOrders(): Promise<void> {
    if (!this.isOnline) {
      console.log('📴 Offline - skipping sync');
      return;
    }

    try {
      const pendingOrders = await this.dbService.getPendingOrders();
      if (pendingOrders.length === 0) {
        console.log('✅ No pending orders to sync');
        return;
      }

      console.log(`🔄 Syncing ${pendingOrders.length} pending order(s)...`);

      for (const pendingOrder of pendingOrders) {
        try {

          // localStorage.setItem('form_data', pendingOrder.formData);
          // let addressId = await this.getAddressId();
          // if (addressId) {
          //   localStorage.setItem('address_id', addressId.toString());
          // }

          console.log('pendingOrder', pendingOrder);
          // Remove metadata fields before sending to API
          const orderDataForAPI = { ...pendingOrder };
          delete orderDataForAPI.type_operation;
          delete orderDataForAPI.savedAt;
          delete orderDataForAPI.isSynced;
          delete orderDataForAPI.id;

          await new Promise<void>((resolve, reject) => {
            const timeoutPromise = new Promise((_, timeoutReject) =>
              setTimeout(() => timeoutReject(new Error('Request timeout')), 30000)
            );

            console.log('orderDataForAPI', orderDataForAPI);

            Promise.race([
              firstValueFrom(this.plaseOrderService.placeOrder_offline(orderDataForAPI)),
              timeoutPromise
            ]).then((response: any) => {
              if (response.status !== false && !response.errorData) {
                // Mark as synced and delete
                this.dbService.markPendingOrderAsSynced(pendingOrder.id)
                  .then(() => this.dbService.deleteSyncedPendingOrder(pendingOrder.id))
                  .then(() => {
                    console.log(`✅ Successfully synced order ${pendingOrder.orderId || 'N/A'}`);
                    resolve();
                  })
                  .catch(reject);
              } else {
                console.error(`❌ API returned error for order:`, response);
                resolve(); // Continue with next order even if this one failed
              }
            }).catch((err) => {
              console.error(`❌ Error syncing order:`, err);
              resolve(); // Continue with next order even if this one failed
            });
          });
        } catch (err) {
          console.error(`❌ Error processing pending order ${pendingOrder.id}:`, err);
          // Continue with next order
        }
      }

      console.log('✅ Finished syncing all pending orders');
    } catch (err) {
      console.error('❌ Error in syncPendingOrders:', err);
    }
  }
  // // Check for pending orders in IndexedDB
  private async checkPendingOrders(): Promise<void> {
    try {
      // const allOrders = await this.dbService.getOrders();
      // this.pendingOrdersCount = allOrders.filter(order =>
      //   order.isOffline && order.status === 'pending'
      // ).length;
    } catch (error) {
      console.error('Error checking pending orders:', error);
    }
  }

  // // Retry pending orders when online
  async retryPendingOrders(): Promise<void> {
    // try {
    // Get all offline orders from IndexedDB
    // const allOrders = await this.dbService.getOrders();

    // const allOrders = await this.dbService.getOrders();

    // const pendingOrders1 = (allOrders || []).filter(order => order.isOffline);
    // console.log("Pending:", pendingOrders1);
    // const pendingOrders = allOrders.filter(
    //   order => order.isOffline == true && order.status === 'pending'
    // );

    //   console.log(`Retrying ${pendingOrders.length} offline orders`);

    //   for (const order of pendingOrders) {
    //     try {
    //       console.log("order:", order);
    //       // Increment attempt count
    //       const attempts = (order.attempts || 0) + 1;

    //       // ✅ Ensure address_id exists
    //       this.addressIdformData = null;
    //       let addressId = null;
    //       if (order.order_details.order_type === 'توصيل' || order.order_details.order_type === 'Delivery') {
    //         this.addressIdformData = order.formdata_delivery;
    //         if (this.addressIdformData) {
    //           console.log("ℹ️ No address_id in order, trying to fetch...");
    //           console.log("Fetched addressId:", this.addressIdformData);
    //           addressId = await this.getAddressId();
    //         }

    //         if (!addressId) {
    //           console.warn(`⚠️ Skipping order ${order.orderId}, missing address_id`);
    //           await this.dbService.savePendingOrder({
    //             ...order,
    //             status: 'pending',
    //             lastError: 'Missing address_id',
    //             attempts,
    //             updatedAt: new Date().toISOString()
    //           });
    //           continue; // skip sending this order until address is available
    //         }
    //       }
    //       // if (attempts > 3) {
    //       //   // Mark as failed if too many attempts
    //       //   await this.dbService.savePendingOrder({
    //       //     ...order,
    //       //     status: 'failed',
    //       //     attempts,
    //       //     updatedAt: new Date().toISOString()
    //       //   });
    //       //   console.warn(`Offline order ${order.orderId} marked as failed after 3 attempts`);
    //       //   continue;
    //       // }

    //       // Update order status to 'processing'
    //       // await this.dbService.savePendingOrder({
    //       //   ...order,
    //       //   status: 'processing',
    //       //   attempts,
    //       //   updatedAt: new Date().toISOString()
    //       // });

    //       // Submit the order to API
    //       const timeoutPromise = new Promise((_, reject) =>
    //         setTimeout(() => reject(new Error('Request timeout')), 30000)
    //       );


    //       const payload = {
    //         isOnline: false,
    //         order_id: order.order_id == order.order_number ? null : order.order_id,
    //         // table_id: order.table_number || null,
    //         type: order.order_details.order_type,
    //         client_name: order.order_details.client_name || null,
    //         client_phone: order.order_details.client_phone || null,
    //         address_id: addressId || order.order_details.address_id,
    //         cashier_machine_id: order.order_details.cashier_machine_id || localStorage.getItem('cashier_machine_id'),
    //         branch_id: order.order_details.branch_id,
    //         table_id: order.order_details.table_id || null,
    //         payment_method: order.order_details.payment_method == "deferred" ? "credit" : order.order_details.payment_method,
    //         payment_status: order.order_details.payment_status,
    //         cash_amount: order.order_details.cash_amount,
    //         credit_amount: order.order_details.credit_amount,
    //         coupon_code: order.order_details.coupon_code || null,
    //         reference_number: order.order_details.reference_number || null,
    //         items: order.order_items.map((i: { dish_id: any; dish_name: any; dish_price: any; quantity: any; final_price: any; note: any; addon_categories: any; sizeId: any; size_name: any; }) => ({
    //           dish_id: i.dish_id,
    //           dish_name: i.dish_name,
    //           dish_price: i.dish_price,
    //           quantity: i.quantity,
    //           final_price: i.final_price,
    //           note: i.note,
    //           addon_categories: i.addon_categories || null,
    //           sizeId: i.sizeId || null,
    //           size_name: i.size_name
    //         })),

    //         // dalia start tips
    //         // tip_amount: this.tipAmount || 0,
    //         change_amount: order.change_amount || 0,
    //         // tips_aption : this.selectedTipType ?? "tip_the_change" ,                  //'tip_the_change', 'tip_specific_amount','no_tip'
    //         tips_aption: order.tips_aption ?? "no_tip",                  //'tip_the_change', 'tip_specific_amount','no_tip'

    //         tip_amount: order.tip_amount ?? 0,
    //         tip_specific_amount: order.tip_specific_amount ?? 0,
    //         payment_amount: order.payment_amount ?? 0,
    //         bill_amount: order.bill_amount ?? 0,
    //         total_with_tip: order.total_with_tip ?? 0,
    //         returned_amount: order.returned_amount ?? 0,
    //         menu_integration: order.menu_integration === 'talabat' ? true : false,
    //         payment_status_menu_integration: order.payment_status_menu_integration,
    //         payment_method_menu_integration: order.payment_method_menu_integration,
    //         edit_invoice: order.edit_invoice,


    //         // dalia end tips


    //       };
    //       console.log('Submitting offline order payload:', payload);
    //       try {
    //         const response: any = await Promise.race([
    //           firstValueFrom(this.plaseOrderService.placeOrder(payload)),
    //           timeoutPromise
    //         ]);

    //         if (response.status) {
    //           console.log("order.order_details.orderId:", order.order_details.order_id);
    //           await this.dbService.deleteOrder(order.order_details.order_id);
    //           console.log(`Offline order ${order.orderId} submitted successfully`);
    //           this.dbService.deleteFromIndexedDB('formData');
    //         } else {

    //           // await this.dbService.deleteOrder(order.order_details.order_id);
    //           await this.dbService.savePendingOrder({ ...order, status: 'pending', lastError: response.errorData || response.message, attempts, updatedAt: new Date().toISOString() });
    //           console.warn(`Order ${order.orderId} submission failed:`, response.errorData);
    //         }

    //       } catch (error: any) {
    //         console.error(`Error submitting offline order ${order.orderId}:`, error);
    //         await this.dbService.savePendingOrder({ ...order, status: 'pending', lastError: error.message, attempts, updatedAt: new Date().toISOString() });
    //       }


    //       // const response: any = await Promise.race([
    //       //   this.plaseOrderService.placeOrder(payload).toPromise(),
    //       //   timeoutPromise
    //       // ]);

    //       // if (response.status) {
    //       //   // Successfully submitted: remove from IndexedDB
    //       //   await this.dbService.deleteOrder(order.orderId);
    //       //   console.log(`Offline order ${order.orderId} submitted successfully`);
    //       // } else {
    //       //   // Validation errors: mark back as pending and save errors
    //       //   await this.dbService.savePendingOrder({
    //       //     ...order,
    //       //     status: 'pending',
    //       //     lastError: response.errorData || response.message || 'Unknown error',
    //       //     attempts,
    //       //     updatedAt: new Date().toISOString()
    //       //   });
    //       //   console.warn(`Offline order ${order.orderId} submission failed:`, response.errorData);
    //       // }

    //     } catch (error: any) {
    //       console.error(`Error submitting offline order ${order.orderId}:`, error);

    //       // Update order back to pending for retry later
    //       await this.dbService.savePendingOrder({
    //         ...order,
    //         status: 'pending',
    //         lastError: error.message || 'Unknown error',
    //         attempts: (order.attempts || 0) + 1,
    //         updatedAt: new Date().toISOString()
    //       });
    //     }
    //   }
    // } catch (error) {
    //   console.error('Error retrieving offline orders:', error);
    // }
  }

  private loadClientInfoFromIndexedDB() {
    // this.dbService.getLatestClientInfo().then(clientInfo => {
    //   if (clientInfo) {
    //     console.log('Client info loaded from IndexedDB:', clientInfo);

    //     // Set the component properties with the loaded data
    //     this.clientStoredInLocal = clientInfo.client || '';
    //     this.clientPhoneStoredInLocal = clientInfo.clientPhone || '';
    //     this.client = clientInfo.client || '';
    //     this.clientPhone = clientInfo.clientPhone || '';
    //     // Find and set the country code if available
    //     if (clientInfo.selectedCountryCode && this.countryList.length > 0) {
    //       const country = this.countryList.find(c => c.code === clientInfo.selectedCountryCode);
    //       if (country) {
    //         this.selectedCountry = country;
    //       }
    //     }

    //     this.clientStoredInLocal = this.client;
    //     this.clientPhoneStoredInLocal = this.clientPhone;
    //   }
    // }).catch(err => {
    //   console.error('Error loading client info from IndexedDB:', err);
    // });
  }

  // end hanan
  finalOrderId: any;
  currentOrderId: any;
  currentOrderData: any;
  // toqa
  selectedCountryCode: any;
  trackByCartItem(index: number, item: any): any {
    return item.dish?.id || index;
  }

  trackByOrderDetail(index: number, item: any): any {
    return item.order_detail_id || item.dish_name + index || index;
  }
  ngOnDestroy(): void {
    // Remove event listeners to prevent memory leaks
    if (this.onlineHandler) {
      window.removeEventListener('online', this.onlineHandler);
    }
    if (this.offlineHandler) {
      window.removeEventListener('offline', this.offlineHandler);
    }

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
  //start hanan

  // async loadTableNumber(): Promise<void> {
  //   try {
  //     // Try IndexedDB first for instant local loading
  //     const tables = await this.dbService.getAll('selectedTable');
  //     if (tables.length > 0) {
  //       const lastTable = tables[tables.length - 1];
  //       const tableNumber = lastTable.table_number;
  //       const table_id = lastTable.id;
  //       console.log('👉 Selected table number:', tableNumber);

  //       if (tableNumber) {
  //         this.tableNumber = tableNumber;
  //         this.table_id = table_id;
  //         this.selectedOrderType = 'dine-in';
  //         localStorage.setItem('selectedOrderType', 'dine-in');
  //         this.cdr.markForCheck(); // Trigger immediate UI update

  //         // If online, also mark table as busy on server
  //         if (navigator.onLine && table_id) {
  //           this.dbService.updateTableStatus(table_id, 2).catch(e =>
  //             console.warn('Failed to mark table busy on server:', e)
  //           );
  //         }
  //       }
  //     }

  //     // Also check localStorage for fallback (supports immediate online-only scenario)
  //     const fallbackTableNumber = localStorage.getItem('table_number');
  //     const fallbackTableId = localStorage.getItem('table_id');
  //     if (fallbackTableNumber && !this.tableNumber) {
  //       this.tableNumber = fallbackTableNumber;
  //       this.table_id = fallbackTableId ? Number(fallbackTableId) : null;
  //       this.selectedOrderType = 'dine-in';
  //       this.cdr.markForCheck();
  //     }
  //   } catch (error) {
  //     console.error('❌ Error loading table from IndexedDB:', error);
  //   }
  // }
  // end hanan
  loadAdditionalNote(): void {
    const note = localStorage.getItem('additionalNote');
    if (note) {
      this.additionalNote = note;
      this.savedNote = note;
    }
  }

  loadBranchData() {
    const branchDataString = localStorage.getItem('branchData');
    if (branchDataString) {
      this.branchData = JSON.parse(branchDataString);
    }
  }

  // loadCart() {
  //   const storedCart = localStorage.getItem('cart');
  //   this.cartItems = storedCart ? JSON.parse(storedCart) : [];

  //   this.updateTotalPrice();
  // }

  loadCart() {
    // مسح الكارت الحالي أولاً
    this.cartItems = [];
    // localStorage.removeItem('table_id');
    // localStorage.removeItem('table_number');
    // localStorage.removeItem('appliedCoupon');
    // localStorage.removeItem('validCoupon');
    // localStorage.removeItem('couponTitle');
    // localStorage.removeItem('couponCode');
    // localStorage.removeItem('discountAmount');
    localStorage.removeItem('client');
    localStorage.removeItem('clientPhone');
    localStorage.removeItem('country_code');

    // ✅ إعادة تعيين متغيرات الكوبون في الكومبوننت
    // this.appliedCoupon = null;
    // this.couponCode = '';
    // this.discountAmount = 0;
    // this.validCoupon = false;
    // this.couponTitle = '';

    const holdCart = localStorage.getItem('holdCart');

    if (holdCart) {
      try {
        const holdItems = JSON.parse(holdCart);
        if (Array.isArray(holdItems) && holdItems.length > 0) {
          this.cartItems = [...holdItems];
          console.log('✅ Loaded from holdCart:', this.cartItems.length, 'items');

          // 🔥 حفظ في localStorage للكارت العادي
          localStorage.setItem('cart', JSON.stringify(this.cartItems));
          this.updateTotalPrice();
          return; // 🔥 نخرج من الدالة هنا - لا ندمج مع الكارت العادي
        }
      } catch (error) {
        console.error('❌ Error parsing holdCart:', error);
      }
    }
    // جلب الكارت من localStorage أولاً
    const storedCart = localStorage.getItem('cart');
    if (storedCart) {
      try {
        const parsedCart = JSON.parse(storedCart);
        if (Array.isArray(parsedCart) && parsedCart.length > 0) {
          this.cartItems = [...parsedCart];
        }
      } catch (error) {
        console.error('❌ Error parsing cart from localStorage:', error);
        this.cartItems = [];
      }
    }

    // جلب العناصر من holdCart مع منع التكرار
    // if (holdCart) {
    //   try {
    //     const holdItems = JSON.parse(holdCart);
    //     if (Array.isArray(holdItems) && holdItems.length > 0) {

    //       // منع التكرار بناءً على uniqueId أو dish.id + sizeId
    //       holdItems.forEach(holdItem => {
    //         const isDuplicate = this.cartItems.some(cartItem =>
    //           this.isSameCartItem(cartItem, holdItem)
    //         );

    //         if (!isDuplicate) {
    //           this.cartItems.push(holdItem);
    //         }
    //       });
    //     }
    //   } catch (error) {
    //     console.error('❌ Error parsing holdCart:', error);
    //   }
    // }

    // حفظ الكارت المدمج في localStorage
    localStorage.setItem('cart', JSON.stringify(this.cartItems));
    this.updateTotalPrice();
  }
  // دالة مساعدة للتحقق من تكرار العناصر
  private isSameCartItem(item1: any, item2: any): boolean {
    // إذا كان لديك uniqueId
    if (item1.uniqueId && item2.uniqueId) {
      return item1.uniqueId === item2.uniqueId;
    }

    // التحقق بناءً على dish.id و sizeId
    const sameDish = item1.dish?.id === item2.dish?.id;
    const sameSize = item1.selectedSize?.id === item2.selectedSize?.id;

    // التحقق من الإضافات إذا كانت موجودة
    const sameAddons = JSON.stringify(item1.selectedAddons || []) ===
      JSON.stringify(item2.selectedAddons || []);

    return sameDish && sameSize && sameAddons;
  }
  // start hanan

  // loadCart() {
  //   this.dbService.getCartItems()
  //     .then((cartItems: any[]) => {
  //       if (cartItems && cartItems.length > 0) {
  //         this.cartItems = cartItems;
  //         console.log('✅ Cart loaded from IndexedDB:', this.cartItems.length, 'items');
  //       } else {
  //         // Fallback إلى localStorage إذا لزم الأمر
  //         // أولاً: جرب holdCart (لطلبات معلقة)
  //         const holdCart = localStorage.getItem('holdCart');
  //         if (holdCart) {
  //           try {
  //             const holdItems = JSON.parse(holdCart);
  //             if (holdItems && holdItems.length > 0) {
  //               this.cartItems = holdItems;
  //               // حفظ في IndexedDB للاستخدام المستقبلي
  //               // this.saveHoldCartToIndexedDB(holdItems);
  //               console.log('✅ Cart loaded from holdCart (localStorage):', this.cartItems.length, 'items');
  //             } else {
  //               // جرب cart
  //               const storedCart = localStorage.getItem('cart');
  //               this.cartItems = storedCart ? JSON.parse(storedCart) : [];
  //               console.log('✅ Cart loaded from cart (localStorage):', this.cartItems.length, 'items');
  //             }
  //           } catch (error) {
  //             console.error('❌ Error parsing holdCart:', error);
  //             const storedCart = localStorage.getItem('cart');
  //             this.cartItems = storedCart ? JSON.parse(storedCart) : [];
  //           }
  //         } else {
  //           // جرب cart العادي
  //           const storedCart = localStorage.getItem('cart');
  //           this.cartItems = storedCart ? JSON.parse(storedCart) : [];
  //           console.log('✅ Cart loaded from cart (localStorage):', this.cartItems.length, 'items');
  //         }
  //       }
  //       this.updateTotalPrice();
  //       this.cdr.detectChanges();
  //     })
  //     .catch((error: any) => {
  //       console.error('❌ Error loading cart from IndexedDB:', error);
  //       // Fallback إلى localStorage
  //       const holdCart = localStorage.getItem('holdCart');
  //       if (holdCart) {
  //         try {
  //           const holdItems = JSON.parse(holdCart);
  //           if (holdItems && holdItems.length > 0) {
  //             this.cartItems = holdItems;
  //             console.log('✅ Cart loaded from holdCart (fallback):', this.cartItems.length, 'items');
  //           } else {
  //             const storedCart = localStorage.getItem('cart');
  //             this.cartItems = storedCart ? JSON.parse(storedCart) : [];
  //           }
  //         } catch (parseError) {
  //           console.error('❌ Error parsing holdCart in fallback:', parseError);
  //           const storedCart = localStorage.getItem('cart');
  //           this.cartItems = storedCart ? JSON.parse(storedCart) : [];
  //         }
  //       } else {
  //         const storedCart = localStorage.getItem('cart');
  //         this.cartItems = storedCart ? JSON.parse(storedCart) : [];
  //       }
  //       this.updateTotalPrice();
  //       this.cdr.detectChanges();
  //     });

  // }

  // حفظ holdCart في IndexedDB
  private async saveHoldCartToIndexedDB(items: any[]): Promise<void> {
    // try {
    //   await this.dbService.init();
    //   await this.dbService.clearCart();
    //   for (const item of items) {
    //     await this.dbService.addToCart(item);
    //   }
    //   console.log('✅ holdCart saved to IndexedDB:', items.length, 'items');
    // } catch (error) {
    //   console.warn('⚠️ Error saving holdCart to IndexedDB:', error);
    // }
  }
  // end hanan
  // start hanan
  // async loadFormData() {
  //   try {
  //     // getFormData() returns a Promise, so we need to await it
  //     const formData = await this.dbService.getFormData();
  //     console.log("FormData", formData);

  //     if (formData && formData.length > 0) {
  //       // Since getFormData() returns an array, get the first (or most recent) item
  //       const latestFormData = formData[formData.length - 1]; // Get the most recent
  //       // OR: const latestFormData = formData.find(item => item.isLatest); // If you have a flag

  //       this.FormDataDetails = latestFormData;
  //       this.clientName = this.FormDataDetails.client_name || 'لم يتم تحديد الإسم';

  //       if (this.FormDataDetails.address) {
  //         this.address = this.FormDataDetails.address || 'لم يتم تحديد العنوان';
  //       }

  //       this.addressPhone = this.FormDataDetails.address_phone || 'لم يتم تحديد رقم الهاتف';
  //     } else {
  //       console.log('No form data found in IndexedDB');

  //       // Fallback to localStorage if no data in IndexedDB
  //       const localStorageFormData = localStorage.getItem('form_data');
  //       if (localStorageFormData) {
  //         this.FormDataDetails = JSON.parse(localStorageFormData);
  //         this.clientName = this.FormDataDetails.client_name || 'لم يتم تحديد الإسم';
  //         this.address = this.FormDataDetails.address || 'لم يتم تحديد العنوان';
  //         this.addressPhone = this.FormDataDetails.address_phone || 'لم يتم تحديد رقم الهاتف';
  //       }
  //     }
  //   } catch (error) {
  //     console.error('Error loading form data:', error);

  //     // Fallback to localStorage on error
  //     const localStorageFormData = localStorage.getItem('form_data');
  //     if (localStorageFormData) {
  //       this.FormDataDetails = JSON.parse(localStorageFormData);
  //       this.clientName = this.FormDataDetails.client_name || 'لم يتم تحديد الإسم';
  //       this.address = this.FormDataDetails.address || 'لم يتم تحديد العنوان';
  //       this.addressPhone = this.FormDataDetails.address_phone || 'لم يتم تحديد رقم الهاتف';
  //     }
  //   }
  // }

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
  // end hanan

  updateTotalPrice() {
    this.totalPrice = this.cartItems.reduce(
      (total, item) => total + item.quantity * item.price,
      0
    );
    localStorage.setItem('cart', JSON.stringify(this.cartItems)); // Update local storage
  }
  saveCart() {
    localStorage.setItem('cart', JSON.stringify(this.cartItems));
  }
  // start hanan
  // saveCart() {
  //   // localStorage.setItem('cart', JSON.stringify(this.cartItems));
  //   return this.dbService.clearCart()
  //     .then(() => {
  //       const savePromises = this.cartItems.map(item =>
  //         this.dbService.addToCart(item)
  //       );
  //       return Promise.all(savePromises);
  //     })
  //     .then(() => {
  //       console.log('✅ Cart saved to IndexedDB');
  //     })
  //     .catch(error => {
  //       console.error('❌ Error saving cart to IndexedDB:', error);
  //       throw error;
  //     });
  // }
  updateTotalPrices() {
    this.cartItems.forEach((item) => {
      // const price = parseFloat(item.dish.price) || 0;
      // const quantity = parseFloat(item.quantity) || 0;

      item.totalPrice = this.getItemTotal(item);
      item.final_Price = this.getItemTotal(item);
      item.finalPrice = this.getItemTotal(item);
    });
    this.totalPrice = this.cartItems.reduce(
      (total, item) => total + item.totalPrice,
      0
    );
    // this.loadCouponFromLocalStorage()
    if (this.appliedCoupon && this.validCoupon && localStorage.getItem('selectedOrderType') !== 'talabat') {
      setTimeout(() => {
        this.applyCoupon();
      }, 100);
    }

    this.getTax();
    this.initializePaymentAmount();
    this.cdr.detectChanges();

  }
  increaseQuantity(index: number) {
    this.cartItems[index].quantity++;
    this.updateTotalPrices();
    this.saveCart();

    // ✅ إعادة تطبيق الكوبون تلقائياً
    if (this.appliedCoupon && this.validCoupon) {
      setTimeout(() => {
        this.applyCoupon();
        this.cdr.detectChanges(); // ✅ إضافة اكتشاف التغييرات
      }, 100);
    } else {
      this.cdr.detectChanges(); // ✅ تحديث الواجهة حتى بدون كوبون
    }
  }
  decreaseQuantity(index: number) {
    if (this.cartItems[index].quantity > 1) {
      this.cartItems[index].quantity--;
    } else {
      this.cartItems.splice(index, 1);
      localStorage.setItem('cart', JSON.stringify(this.cartItems));

      // إذا أصبح الكارت فارغاً
      if (this.cartItems.length === 0) {
        this.appliedCoupon = null;
        this.couponCode = '';
        this.discountAmount = 0;
        this.additionalNote = '';
        this.savedNote = '';
        this.successMessage = '';
        this.errorMessage = '';
        this.removeCouponFromLocalStorage();
      }
    }

    this.updateTotalPrices();
    this.saveCart();

    // ✅ إعادة تطبيق الكوبون تلقائياً
    if (this.appliedCoupon && this.validCoupon && this.cartItems.length > 0) {
      setTimeout(() => {
        this.applyCoupon();
        this.cdr.detectChanges(); // ✅ إضافة اكتشاف التغييرات
      }, 100);
    } else {
      this.cdr.detectChanges(); // ✅ تحديث الواجهة حتى بدون كوبون
    }
  }
  removeItem(productId: number, sizeId?: number): void {
    this.productsService.removeFromCart(productId, sizeId);
    this.loadCart();
  }
  cancelMessagefn() {
    //  this.finalOrderId = localStorage.getItem('finalOrderId')
    if (this.finalOrderId) {
      this.cancelMessage = "هل تريد غلق الطلب المعلق"
    } else if (this.currentOrderData) {
      this.cancelMessage = "هل تريد الرجوع للصفحة الرئيسية"
    } else {
      this.cancelMessage = "هل تريد الغاء الطلب"

    }
  }
  cancelOrder(): void {
    // مسح البيانات من localStorage
    localStorage.removeItem('cart');
    localStorage.removeItem('holdCart');
    localStorage.removeItem('savedOrders');
    this.clearCart();
    localStorage.removeItem('finalOrderId');
    this.finalOrderId = '';
    localStorage.removeItem('currentOrderData');
    this.currentOrderData = null;
    localStorage.removeItem('currentOrderId');
    this.currentOrderId = null;
    localStorage.removeItem('additionalNote');
    localStorage.removeItem('notes');
    this.additionalNote = '';
    this.savedNote = '';
    this.onholdOrdernote = '';
      this.clearCouponData();

    this.clearOrderType();
    this.selectedPaymentMethod = null;
    this.selectedPaymentStatus = 'unpaid';
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
    localStorage.removeItem('selectedHotel');
    localStorage.removeItem('hotel_id');
    localStorage.removeItem('selected_address');
    this.tableNumber = null;
    this.FormDataDetails = null;
  }
  private clearCouponData(): void {
  // ⭐️ مسح كل بيانات الكوبون من localStorage
  const couponKeys = [
    'couponCode',
    'discountAmount',
    'appliedCoupon',
    'validCoupon',
    'couponTitle',
    'couponType',
    'couponValue',
    'couponApplyType',
    'discountDetails',
    'coupon_Code',
    'coupon_value'
  ];

  couponKeys.forEach(key => localStorage.removeItem(key));

  // ⭐️ إعادة تعيين متغيرات الكوبون في الكومبوننت
  this.appliedCoupon = null;
  this.couponCode = '';
  this.discountAmount = 0;
  this.validCoupon = false;
  this.couponTitle = '';
  this.couponType = '';
  this.coupon_Code = '';
  this.coupon_value = null;

  // ⭐️ إعادة تعيين delivery_fees إذا تم تطبيق كوبون 100%
  this.delivery_fees = Number(localStorage.getItem('original_delivery_fees')) || this.delivery_fees;

  console.log('✅ تم مسح جميع بيانات الكوبون');
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
    const couponApplyType = this.appliedCoupon?.coupon_apply_type;

    // Step 1: Calculate subtotal from cart items
    const rawSubtotal = this.getTotal();

    let subtotal;
    if (!couponEnabled && !taxEnabled && this.appliedCoupon) {
      subtotal = this.appliedCoupon?.amount_after_coupon;
    } else {
      subtotal = rawSubtotal;
    }

    let taxAmount = 0;

    // Step 2: Calculate tax if tax is disabled
    if (!taxEnabled) {
      taxAmount = this.getTax();
    }

    // Step 3: Apply coupon - 🔥 تحديث بناءً على نوع التطبيق
    if (this.appliedCoupon && this.validCoupon && localStorage.getItem('selectedOrderType') !== 'talabat') {
      if (couponApplyType === 'dish') {
        // 🔥 كوبون على أطباق محددة - استخدم المبلغ بعد الخصم مباشرة
        subtotal = this.appliedCoupon.amount_after_coupon;
      } else {
        // كوبون على الطلب كامل
        if (taxEnabled && !couponEnabled && couponPercentage === 'percentage') {
          subtotal = this.appliedCoupon.amount_after_coupon + this.getTax();
        } else {
          subtotal = this.appliedCoupon.amount_after_coupon;
        }
      }
    } else {
      this.getTax();
      subtotal = this.getTotal();
    }

    subtotal = parseFloat(subtotal.toFixed(2));

    // Step 4: Ensure subtotal is not negative
    subtotal = Math.max(subtotal, 0);

    // Step 5: Calculate service fee (based on raw subtotal only)
    let serviceFee = 0;
    if (
      this.selectedOrderType === 'في المطعم' ||
      this.selectedOrderType === 'dine-in' ||
      this.currentOrderData?.order_details?.order_type === 'dine-in'
    ) {
      if (!couponEnabled && !taxEnabled && this.appliedCoupon) {
        serviceFee = this.getServiceOnAmountAfterCoupon();
      } else {
        serviceFee = this.getServiceFeeAmount();
      }
    }
    serviceFee = parseFloat(serviceFee.toFixed(2));

    // Step 6: Delivery fee - 🔥 معالجة خاصية delivery_fees من الكوبون
    let deliveryFee = 0;
    if (
      this.selectedOrderType === 'توصيل' ||
      this.selectedOrderType === 'Delivery' ||
      this.currentOrderData?.order_details?.order_type === 'Delivery'
    ) {
      // 🔥 إذا كان الكوبون يلغي delivery_fees (مثل كوبون 100%)
      if (this.appliedCoupon && this.appliedCoupon.delivery_fees === 0) {
        deliveryFee = 0;
      } else {
        deliveryFee = this.delivery_fees;
      }
    }
    deliveryFee = parseFloat(deliveryFee.toFixed(2));

    // Step 7: Final total calculation
    let total = 0;

    if (!taxEnabled && !this.appliedCoupon) {
      total = subtotal + taxAmount + serviceFee + deliveryFee;
    } else if (!taxEnabled && couponEnabled) {
      total = subtotal + serviceFee + deliveryFee;
    } else {
      total = subtotal + taxAmount + serviceFee + deliveryFee;
    }

    if ((this.selectedOrderType === 'talabat' || this.selectedOrderType === 'طلبات')) {
      total = subtotal;
    }

    const finalTotal = total > 0 ? parseFloat(total.toFixed(2)) : 0;
    return finalTotal;
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
    // ✅ إضافة الـ Round هنا أيضاً
    serviceFee = Math.round(serviceFee * 100) / 100;

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
    serviceFee = Math.round(serviceFee * 100) / 100;
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
      this.selectedOrderType === 'dine-in' ||
      this.currentOrderData?.order_details?.order_type === 'dine-in';
    const isDeliveryOrder =
      this.selectedOrderType === 'توصيل' ||
      this.selectedOrderType === 'Delivery' ||
      this.currentOrderData?.order_details?.order_type === 'Delivery';

    let subtotal;
    if (this.appliedCoupon && this.validCoupon) {
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
      if (isDineIn && this.appliedCoupon && this.validCoupon) {
        subtotal =
          this.appliedCoupon?.amount_after_coupon +
          this.getServiceOnAmountAfterCoupon();
      }
      if (isDeliveryOrder && this.appliedCoupon && this.validCoupon) {
        const deliveryFees = this.delivery_fees;
        subtotal = this.appliedCoupon?.amount_after_coupon;
      }

      taxAmount = (subtotal * taxPercentage) / 100;
      /*       console.log(taxAmount, 'here');
       */
    }
    return parseFloat(taxAmount.toFixed(2));
  }

  getTotalItemCount(): number {
    return this.cartItems.reduce(
      (total: any, item: { quantity: any }) => total + item.quantity,
      0
    );
  }
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

    // ✅ Track start time for minimum 1 second duration
    const startTime = Date.now();
    const minDuration = 1000; // 1 second
    this.errorMessage = '';
    this.successMessage = '';
    this.isLoading = true;
    this.cdr.markForCheck(); // Trigger change detection immediately

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
            // if(response.data.value_type == "percentage" && response.data.coupon_value == "100.00"){
            //   localStorage.setItem("delivery_fees" , "0")
            //   this.deliveryFeesWithFullCoupon = 0
            //   console.log(this.delivery_fees ,"88")
            // }
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
            this.initializePaymentAmount();

          } else {

            baseAmount = baseAmount
            this.validCoupon = false;
            this.removeCouponFromLocalStorage()
            this.couponCode = null;
            if (response.errorData?.error) {
              this.errorMessage = response.errorData.error;
            }

            this.getTax();
            this.initializePaymentAmount();

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
    // setTimeout(() => {
    //  const modalEl = document.getElementById('couponModal');
    // if (modalEl) {
    //     let bsModal = bootstrap.Modal.getInstance(modalEl);
    //     if (!bsModal) {
    //     bsModal = new bootstrap.Modal(modalEl);
    //     }
    //    bsModal.hide();
    //      const backdrops = document.querySelectorAll('.modal-backdrop');
    //     backdrops.forEach((el) => el.remove());
    //    document.body.classList.remove('modal-open');
    //   document.body.style.removeProperty('padding-right');
    //}
    // }, 1000);
    this.initializePaymentAmount();
  }

  removeCouponFromLocalStorage() {
    const couponKeys = [
      'couponCode', 'discountAmount', 'appliedCoupon',
      'validCoupon', 'couponTitle', 'couponType', 'couponValue',
      'couponApplyType', 'discountDetails' // 🔥 جديد
    ];

    couponKeys.forEach(key => localStorage.removeItem(key));

    // 🔥 إعادة تعيين delivery_fees إذا تم تطبيق كوبون 100%
    this.delivery_fees = Number(localStorage.getItem('original_delivery_fees')) || this.delivery_fees;
  }

  getTotal(): number {
    const itemsHash = JSON.stringify(this.cartItems);
    if (this._cachedTotal !== null && this._cachedCartItemsHash === itemsHash) {
      return this._cachedTotal;
    }
    const total = this.cartItems.reduce(
      (sum, item) => sum + this.getItemTotal(item),
      0
    );
    this._cachedCartItemsHash = itemsHash;
    this._cachedTotal = total;

    // ✅ تقريب المجموع الكلي
    return parseFloat(total.toFixed(2));
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
      const itemTotal = (basePrice + addonsTotal) * (Number(item.quantity) || 1);

      // ✅ تقريب سعر كل عنصر
      return parseFloat(itemTotal.toFixed(2));
    }

    // Case 2: older/test item structure (with final_price or dish_price)
    const fallbackPrice = Number(item.final_price ?? item.dish_price ?? 0);
    const fallbackTotal = fallbackPrice * (Number(item.quantity) || 1);

    // ✅ تقريب السعر البديل أيضاً
    return parseFloat(fallbackTotal.toFixed(2));
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
  // deliveryFeesWithFullCoupon:any
  async getAddressId(): Promise<number | null> {
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
  // start hanan

  private prepareOrderData(): any {
    // This should contain all the order data preparation logic
    // that was previously in your submitOrder method
    console.log("prepareOrderData called");

    const branchId = Number(localStorage.getItem('branch_id')) || null;
    const tableId = Number(localStorage.getItem('table_id')) || this.table_id || null;
    const formData = JSON.parse(localStorage.getItem('form_data') || '{}');
    // continued order from orders list
    let continuedOrderId: number | null = null;
    let table_number: any;
    if(localStorage.getItem('table_number'))
    {
      table_number = Number(localStorage.getItem('table_number'));
    }


    // ✅ استخدام finalTipSummary إذا موجود، وإلا استخدام القيم الأساسية
    const billAmount = this.finalTipSummary?.billAmount ?? this.getCartTotal();
    const paymentAmount = this.finalTipSummary?.paymentAmount ?? billAmount;
    const tipAmount = this.finalTipSummary?.tipAmount ?? 0;
    const changeAmount = this.finalTipSummary?.changeToReturn ?? 0;
    const tipsAption = this.finalTipSummary?.tips_aption || this.selectedTipType || 'no_tip';

    try {
      const currentOrderDataRaw = localStorage.getItem('currentOrderData');
      if (currentOrderDataRaw) {
        const parsed = JSON.parse(currentOrderDataRaw);
        continuedOrderId = parsed?.order_details?.order_id ?? null;
        table_number = parsed?.order_details?.table_number ?? null;
      }
    } catch (_) {
      continuedOrderId = null;
    }

    // ... rest of your order data preparation

    // else {
    //   this.selectedPaymentMethod = "cash"
    //   console.log(this.selectedPaymentMethod, "2");
    // }
    // let formdata_delivery = null;
    // if(this.selectedOrderType === "Delivery" || this.selectedOrderType === "توصيل") {
    //   formdata_delivery = localStorage.getItem('form_delivery');
    // }


    return {
      isOnline: navigator.onLine,
      orderId: this.finalOrderId || Date.now(),
      ...(continuedOrderId ? { order_id: continuedOrderId } : {}),
      order_id: continuedOrderId ?? null,
      table_number: table_number ?? null,
      table_id: tableId ?? null,
      type: this.selectedOrderType,
      branch_id: branchId,
      payment_method: this.selectedPaymentMethod ?? 'cash',
      payment_status: this.selectedPaymentStatus,
      // cash_amount: this.selectedPaymentMethod === "cash" ? this.finalTipSummary?.billAmount ?? 0 : 0,
      // credit_amount: this.selectedPaymentMethod === "credit" ? this.finalTipSummary?.billAmount ?? 0 : 0,
      cash_amount: this.finalTipSummary ?
        (this.selectedPaymentMethod === 'cash' ? this.finalTipSummary.billAmount : 0) :
        (this.selectedPaymentMethod === 'cash' ? this.cash_amountt : 0),

      credit_amount: this.finalTipSummary ?
        (this.selectedPaymentMethod === 'credit' ? this.finalTipSummary.paymentAmount : 0) :
        (this.selectedPaymentMethod === 'credit' ? this.credit_amountt : 0),
      cashier_machine_id: localStorage.getItem('cashier_machine_id'),
      ...(this.clientPhoneStoredInLocal ? { client_country_code: this.selectedCountry.code || "+20" } : {}),
      ...(this.clientPhoneStoredInLocal ? { client_phone: this.clientPhoneStoredInLocal } : {}),
      ...(this.clientStoredInLocal ? { client_name: this.clientStoredInLocal } : {}),
      note: this.additionalNote || this.savedNote || this.applyAdditionalNote() || this.onholdOrdernote || '',
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

      // dalia start tips
      // tip_amount: this.tipAmount || 0,
      change_amount: this.tempChangeAmount || 0,
      // tips_aption : this.selectedTipType ?? "tip_the_change" ,                  //'tip_the_change', 'tip_specific_amount','no_tip'
      tips_aption: tipsAption,                  //'tip_the_change', 'tip_specific_amount','no_tip'

      tip_amount: this.finalTipSummary?.tipAmount ?? 0,
      // tip_specific_amount:this.finalTipSummary?.tipAmount ?? 0,
      tip_specific_amount: this.specificTipAmount ? this.finalTipSummary?.tipAmount : 0,
      payment_amount: this.finalTipSummary?.paymentAmount ?? 0,
      bill_amount: this.finalTipSummary?.billAmount ?? this.getCartTotal(),
      total_with_tip: this.finalTipSummary ? (this.finalTipSummary.tipAmount ?? 0) + (this.finalTipSummary.billAmount ?? 0) : this.getCartTotal(),
      returned_amount: this.finalTipSummary?.changeToReturn ?? 0,
      menu_integration: this.selectedOrderType === 'talabat' ? true : false,
      payment_status_menu_integration: this.selectedPaymentStatus,
      payment_method_menu_integration: this.selectedPaymentMethod,

      // formdata_delivery: formdata_delivery ?? null,

      // dalia end tips
    };

  }
  private resetLocalStorage(): void {
    localStorage.removeItem('table_number');
    localStorage.removeItem('table_id');
    localStorage.removeItem('selectedOrderType');
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
    localStorage.removeItem('currentOrderData');
    localStorage.removeItem('currentOrderId');

    const tableIdToRelease = this.table_id;

    this.client = '';
    this.clientPhone = '';
    this.finalOrderId = '';
    this.currentOrderData = null;
    this.currentOrderId = null;
    this.cash_amountt = 0;
    this.credit_amountt = 0;
    this.selectedPaymentStatus = 'unpaid';
    this.selectedPaymentMethod = null;
    this.tableNumber = null;
    this.table_id = null;
    this.FormDataDetails = null;
    this.onholdOrdernote = '';
    this.referenceNumber = '';
    this.referenceNumberTouched = false;
    this.cashPaymentInput = '';
    this.cashAmountMixed = '';
    this.creditAmountMixed = '';
    this.finalTipSummary = null;
    this.clearOrderType();
    this.cdr.detectChanges();


    // this.dbService.deleteFromIndexedDB('clientInfo');
    // this.dbService.deleteFromIndexedDB('formData');
    // this.dbService.deleteFromIndexedDB('selectedOrderType');
    // this.dbService.deleteFromIndexedDB('selectedTable');
    // this.dbService.deleteFromIndexedDB('form_delivery');

    // ✅ Release table locally (mark available) if exists
    if (tableIdToRelease) {
      // this.dbService.updateTableStatus(tableIdToRelease, 1);
    }
  }

  private async releaseTableAndOrderType(): Promise<void> {
    try {
      const tableId = this.table_id || Number(localStorage.getItem('table_id')) || null;
      if (tableId) {
        // await this.dbService.updateTableStatus(tableId, 1);
      }
    } catch (e) {
      console.warn('Failed to update table status to available:', e);
    }
    // Clear selection in-memory and storage
    this.table_id = null;
    this.tableNumber = null;
    this.selectedOrderType = null;
    localStorage.removeItem('table_id');
    localStorage.removeItem('table_number');
    localStorage.removeItem('selectedOrderType');
  }

  async submitOrder() {
    console.log('🔍 قبل تعيين credit_amount:', {
      credit_amountt: this.credit_amountt,
      cashPaymentInput: this.cashPaymentInput,
      selectedPaymentMethod: this.selectedPaymentMethod
    });
    if (this.currentOrderData) {
      this.selectedOrderType = this.currentOrderData?.order_details?.order_type;
      localStorage.setItem('selectedOrderType', this.selectedOrderType);
      if (this.selectedOrderType === 'Delivery' && this.selectedOrderType === 'توصيل') {
        console.log("ss");
        localStorage.removeItem('delivery_fees');
      }

    }

    console.log(this.currentOrderData?.order_details?.order_type, "alaaaaaaaaaaaaaaaa");
    if (this.isLoading) {
      console.warn("🚫 Request already in progress, ignoring duplicate submit.");
      return;
    }

    this.isLoading = true;
    this.loading = true;

    // التحقق الأساسي
    if (!this.cartItems.length) {
      this.isLoading = false;
      this.falseMessage = 'العربة فارغة، أضف بعض العناصر قبل تنفيذ الطلب.';
      setTimeout(() => { this.falseMessage = ''; }, 1500);
      return;
    }
    if (!this.selectedOrderType) {
      this.isLoading = false;
      this.falseMessage = 'يرجى تحديد نوع الطلب قبل المتابعة.';
      setTimeout(() => { this.falseMessage = ''; }, 1500);
      return;
    }

    // جلب البيانات الأساسية
    const branchId = Number(localStorage.getItem('branch_id')) || null;
    const tableId = Number(localStorage.getItem('table_id')) || this.table_id || this.currentOrderData?.order_details?.table_number || null;
    const formData = JSON.parse(localStorage.getItem('form_data') || '{}');

    let addressId = null;
    console.log(this.selectedOrderType, 'gggggggggggg');
    // if (this.selectedOrderType === 'Delivery') {
    //   addressId = localStorage.getItem('address_id');
    //   if (!localStorage.getItem('address_id')) {
    //     addressId = await this.getAddressId();
    //   }
    // }
    // if (this.selectedOrderType === 'Delivery' && !this.currentOrderData) {
    //   addressId = localStorage.getItem('address_id');

    //   if (!addressId && !this.addressRequestInProgress) {
    //     this.addressRequestInProgress = true;
    //     try {
    //       addressId = await this.getAddressId();
    //       if (addressId) {
    //         localStorage.setItem('address_id', addressId.toString());
    //       }
    //     } finally {
    //       this.addressRequestInProgress = false;
    //     }
    //   }
    // }
    if (navigator.onLine) {
      if (this.selectedOrderType === 'Delivery' && !this.currentOrderData) {
        addressId = localStorage.getItem('address_id');

        if (!addressId && !this.addressRequestInProgress) {
          this.addressRequestInProgress = true;
          try {

            addressId = await this.getAddressId();
            if (addressId) {
              localStorage.setItem('address_id', addressId.toString());
            } else {
              this.isLoading = false;
              this.loading = false;

              this.falseMessage = 'يرجى اختيار عنوان التوصيل';
              setTimeout(() => {
                this.falseMessage = '';
              }, 1500);
              return;
            }

          } catch (error) {
            // ❌ API failed → show message and stop
            this.isLoading = false;
            this.loading = false;

            this.falseMessage = 'يرجى اختيار عنوان التوصيل';
            setTimeout(() => {
              this.falseMessage = '';
            }, 1500);

            return;
          } finally {
            this.addressRequestInProgress = false;
          }
        }
      }
    }
    // ✅ التحقق من معلومات التوصيل في وضع Offline
    if (!navigator.onLine && this.selectedOrderType === 'Delivery' && !this.currentOrderData) {
      const validation = this.validateOfflineDeliveryInfo();
      if (!validation.isValid) {
        this.isLoading = false;
        this.loading = false;
        this.falseMessage = validation.message;
        setTimeout(() => { this.falseMessage = ''; }, 3000);
        return;
      }
    }
    const authToken = localStorage.getItem('authToken');
    const cashier_machine_id = localStorage.getItem('cashier_machine_id');
    const orderId = this.currentOrderId ?? 0;

    // التحقق من البيانات الأساسية
    if (!branchId) {
      this.showError('فشل تحديد الفرع. الرجاء إعادة تسجيل الدخول.');
      return;
    }
    if (!authToken) {
      this.showError('فشل التحقق من الهوية. الرجاء تسجيل الدخول مجددًا.');
      return;
    }

    this.formSubmitted = true;
    this.amountError = false;

    if (!this.selectedPaymentStatus) {
      setTimeout(() => {
        this.isLoading = false;
        this.formSubmitted = false;
      }, 2500);
      return;
    }

    // التحقق من المبالغ المدفوعة - التصحيح الرئيسي هنا
    if (this.selectedPaymentStatus === 'paid') {
      const isDelivery = this.selectedOrderType === 'Delivery' || this.selectedOrderType === 'توصيل';
      const isTalabat = this.selectedOrderType === 'talabat';

      if (!isDelivery) {
        // استخدام النظام الجديد أولاً
        let totalEntered = 0;
        const cartTotal = Number(this.getCartTotal().toFixed(2));
        // ✅ حالة خاصة لطلبات + مدفوع + كاش - استخدام الإجمالي مباشرة
        if (isTalabat && this.selectedPaymentMethod === 'cash') {
          totalEntered = cartTotal;
          console.log('💰 Talabat + Paid + Cash: Using cart total directly', totalEntered);
          // تعيين القيم مباشرة
          this.cashPaymentInput = cartTotal;
          this.finalTipSummary = {
            total: cartTotal,
            serviceFee: 0,
            billAmount: cartTotal,
            paymentAmount: cartTotal,
            paymentMethod: 'كاش',
            tipAmount: 0,
            grandTotalWithTip: cartTotal,
            changeToReturn: 0
          };
        }

        // ✅ النظام الجديد مع الإكرامية
        if (this.finalTipSummary && this.finalTipSummary.paymentAmount > 0) {
          totalEntered = Number(this.finalTipSummary.paymentAmount);
        }
        // ✅ النظام الجديد - كاش فقط
        else if (this.selectedPaymentMethod === 'cash' && this.cashPaymentInput > 0) {
          totalEntered = Number(this.cashPaymentInput);
        }
        // ✅ النظام الجديد - دفع مختلط
        else if (this.selectedPaymentMethod === 'cash + credit') {
          if (this.selectedPaymentStatus === 'paid' && this.credit_amountt > 0 && (!this.referenceNumber || !this.referenceNumber.trim())) {
            this.referenceNumberTouched = true;
            this.showError('❌ رقم المرجع مطلوب عند الدفع بالفيزا.');
            return;
          }

          totalEntered = Number(((this.cashAmountMixed || 0) + (this.creditAmountMixed || 0)));
        }
        // ✅ النظام القديم
        else {
          totalEntered = Number((((Number(this.cash_amountt) || 0) + (Number(this.credit_amountt) || 0))));
        }

        console.log('💰 Payment validation - Fixed:', {
          totalEntered,
          cartTotal,
          paymentMethod: this.selectedPaymentMethod,
          hasFinalTipSummary: !!this.finalTipSummary,
          finalTipAmount: this.finalTipSummary?.paymentAmount,
          cashPaymentInput: this.cashPaymentInput,
          cashAmountMixed: this.cashAmountMixed,
          creditAmountMixed: this.creditAmountMixed
        });

        if (totalEntered < cartTotal) {
          this.amountError = true;
          this.falseMessage = `يجب أدخال الإجمالي. ${cartTotal.toFixed(2)} ${this.currencySymbol}`;
          console.log('❌ Entered amount less than total:', totalEntered, cartTotal);
          this.isLoading = false;

          setTimeout(() => {
            this.amountError = false;
            this.falseMessage = '';
          }, 3500);
          return;
        }

        console.log('✅ Valid payment amount:', totalEntered, cartTotal);
      }
    }

    // التحقق من رقم المرجع للفيزا
    if (this.selectedPaymentStatus === 'paid' && (this.credit_amountt > 0 || (this.creditAmountMixed > 0)) && !this.referenceNumber) {
      this.showError('❌ رقم المرجع مطلوب عند الدفع بالفيزا.');
      return;
    }

    this.isLoading = true;
    this.loading = true;
    this.falseMessage = '';
    this.tableError = '';
    this.couponError = '';

    const paymentStatus = this.selectedPaymentMethod === 'cash' ? this.selectedPaymentStatus : 'paid';
    console.log(this.selectedPaymentMethod, 'selectedPaymentMethod');

    const orderData: any = this.prepareOrderData();

    // معالجة عنوان التوصيل
    // let addressId = null;
    // if (navigator.onLine) {
    if (this.selectedOrderType === 'Delivery' && !this.currentOrderData) {
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
    // }

    if (!this.currentOrderData) {
      console.log("no current order data");

      // إضافة الكوبون
      if (this.appliedCoupon && this.couponCode?.trim() && this.validCoupon && localStorage.getItem('selectedOrderType') !== 'talabat') {
        orderData.coupon_code = this.couponCode.trim();
        orderData.discount_amount = this.discountAmount;
        orderData.coupon_type = this.appliedCoupon.value_type;
      } else {
        orderData.coupon_code = ' ';
      }

      // إضافة رقم المرجع
      if (this.credit_amountt > 0) {
        orderData.reference_number = this.referenceNumber;
      }

      // التحقق من العنوان للتوصيل
      if (this.selectedOrderType === 'Delivery' && addressId) {
        orderData.address_id = addressId;
      }
      if (this.selectedOrderType === 'Delivery' && !formData) {
        this.showError('يرجى اختيار عنوان التوصيل ');
        return;
      }

      if (this.selectedPaymentStatus == "unpaid") {
        orderData.credit_amount = null;
        orderData.cash_amount = null;
      } else if (this.selectedPaymentStatus == "paid") {
        const billAmount = this.finalTipSummary?.billAmount ?? this.getCartTotal();

        // ✅ التعديل المطلوب: إذا كاش يحط في cash_amount، إذا فيزا يحط في credit_amount
        if (this.selectedPaymentMethod === 'cash') {
          orderData.cash_amount = billAmount;
          orderData.credit_amount = 0;
        } else if (this.selectedPaymentMethod === 'cash + credit') {

          // في حالة الدفع المختلط، استخدم القيم المدخلة
          // أخذ القيم من cashAmountMixed و creditAmountMixed
          const cashAmount = this.cashAmountMixed || 0;
          const creditAmount = this.creditAmountMixed || 0;

          console.log('💰 الدفع المختلط:', {
            cashAmount: cashAmount,
            creditAmount: creditAmount,
            total: cashAmount + creditAmount
          });

          orderData.cash_amount = cashAmount;
          orderData.credit_amount = creditAmount;

          const totalPaid = cashAmount + creditAmount;
          const billAmount = this.finalTipSummary?.billAmount ?? this.getCartTotal();

          if (totalPaid < billAmount) {
            this.amountError = true;
            this.falseMessage = `المبلغ المدفوع غير كافي. المطلوب: ${billAmount} ${this.currencySymbol}`;
            return;
          }
          orderData.payment_method = "cash";

          // const cashAmount = Number(this.cashAmountMixed) || 0;

          // const creditAmount = Number(this.creditAmountMixed) || 0;
        }
        else if (this.selectedPaymentMethod === 'credit') {
          orderData.credit_amount = this.credit_amountt > 0 ? this.credit_amountt : billAmount;
          orderData.cash_amount = 0;

          console.log('💳 تم تعيين مبالغ الدفع بالفيزا:', {
            method: this.selectedPaymentMethod,
            credit_amount: orderData.credit_amount,
            cash_amount: orderData.cash_amount,
            billAmount: billAmount,
            credit_amountt: this.credit_amountt,
            cashPaymentInput: this.cashPaymentInput
          });
        } else if (this.selectedPaymentMethod === 'deferred') {
          orderData.cash_amount = 0;
          orderData.credit_amount = 0;
        }

        console.log('💰 تم تعيين مبالغ الدفع:', {
          method: this.selectedPaymentMethod,
          cash_amount: orderData.cash_amount,
          credit_amount: orderData.credit_amount,
          billAmount: billAmount
        });
      }
      // التحقق من العناصر
      if (!orderData.items.length) {
        this.showError('لا يمكن تقديم الطلب بدون عناصر صالحة.');
        return;
      }

      // إعداد الطاولة للجلوس في المطعم
      if (this.selectedOrderType === 'dine-in' || this.selectedOrderType === 'في المطعم') {
        if (!tableId) {
          this.showError('يرجى اختيار طاولة.');
          return;
        }
        orderData.table_id = tableId;
      }

      // إعداد بيانات التوصيل
      if (navigator.onLine && (this.selectedOrderType === 'Delivery' || this.selectedOrderType === 'توصيل')) {
        if (!addressId) {
          this.showError('يرجى اختيار عنوان التوصيل ');
          return;
        }
        orderData.address_id = addressId;
        orderData.client_country_code = formData.country_code?.code || "+20";
        orderData.client_phone = formData.address_phone;
        orderData.client_name = formData.client_name;
      }
    }

    // معالجة حالة عدم الاتصال
    // if (!navigator.onLine) {
    //   // ✅ التحقق من معلومات التوصيل في وضع Offline - بدون دالة مساعدة
    //   if (this.selectedOrderType === 'Delivery' && !this.currentOrderData) {
    //     const hasFormData = localStorage.getItem('form_data');
    //     const hasClientInfo = this.clientName && this.clientPhone && this.address;

    //     console.log('📋 Offline delivery info check:', {
    //       hasFormData: !!hasFormData,
    //       clientName: this.clientName,
    //       clientPhone: this.clientPhone,
    //       address: this.address
    //     });

    //     if (!hasFormData && !hasClientInfo) {
    //       this.isLoading = false;
    //       this.loading = false;
    //       this.falseMessage = 'يرجى إدخال معلومات التوصيل أولاً';
    //       setTimeout(() => { this.falseMessage = ''; }, 3000);
    //       return;
    //     }
    //   }

    //   try {
    //     console.log('📴 Offline mode: Saving order to IndexedDB', orderData);

    //     // ✅ إضافة بيانات التوصيل للطلب في وضع Offline
    //     if (this.selectedOrderType === 'Delivery') {
    //       const formData = JSON.parse(localStorage.getItem('form_data') || '{}');

    //       // إضافة معلومات التوصيل للطلب
    //       orderData.delivery_info = {
    //         client_name: formData.client_name || this.clientName,
    //         client_phone: formData.address_phone || this.clientPhone,
    //         address: formData.address || this.address,
    //         country_code: formData.country_code?.code || formData.country_code || this.selectedCountry.code,
    //         apartment_number: formData.apartment_number || '',
    //         building: formData.building || '',
    //         address_type: formData.address_type || '',
    //         propertyType: formData.propertyType || '',
    //         buildingName: formData.buildingName || '',
    //         note: formData.note || '',
    //         floor_number: formData.floor_number || '',
    //         landmark: formData.landmark || '',
    //         villaName: formData.villaName || '',
    //         villaNumber: formData.villaNumber || '',
    //         companyName: formData.companyName || '',
    //         buildingNumber: formData.buildingNumber || ''
    //       };

    //       // استخدام عنوان مؤقت للـ Offline
    //       orderData.address_id = 9999; // أو أي قيمة مؤقتة

    //       // أيضاً إضافة معلومات العميل للطلب
    //       orderData.client_name = formData.client_name || this.clientName;
    //       orderData.client_phone = formData.address_phone || this.clientPhone;
    //       orderData.client_country_code = formData.country_code?.code || formData.country_code || this.selectedCountry.code;
    //     }

    //     // Save to orders/pills stores (existing functionality) - preserves full structure
    //     await this.dbService.savePendingOrder(orderData);
    //     console.log("✅ Order saved to IndexedDB with full structure");

    //     // Save raw orderData for API sync (exact data that will be sent to API)
    //     // const formData = localStorage.getItem('form_data');
    //     const orderDataForSync = { ...orderData };

    //     await this.dbService.savePendingOrderForSync(orderDataForSync);
    //     console.log("✅ Raw orderData saved for API sync");

    //     // Clean up localStorage
    //     const savedOrders = JSON.parse(localStorage.getItem('savedOrders') || '[]');
    //     const orderIdToRemove = orderData.orderId;
    //     const updatedOrders = savedOrders.filter((savedOrder: any) => savedOrder.orderId !== orderIdToRemove);
    //     localStorage.setItem('savedOrders', JSON.stringify(updatedOrders));

    //     // Clear cart and reset
    //     this.clearCart();
    //     this.resetLocalStorage();
    //     this.resetAddress();
    //     this.loadCart();

    //     // Show success message
    //     this.successMessage = 'تم حفظ الطلب وسيتم إرساله عند عودة الاتصال';
    //     this.falseMessage = '';
    //     this.tableError = '';
    //     this.couponError = '';
    //     this.cashiermachine = '';

    //     if (this.successModal) {
    //       this.successModal.show();
    //     }

    //     this.cdr.detectChanges();
    //   } catch (error) {
    //     console.error('❌ Error saving order to IndexedDB:', error);
    //     this.showError('فشل حفظ الطلب في وضع عدم الاتصال. يرجى المحاولة مرة أخرى.');
    //   } finally {
    //     this.isLoading = false;
    //     this.loading = false;
    //   }
    //   return;
    // }

    // إرسال الطلب إلى API
    console.log('Submitting order online:', orderData);

    const timeoutPromise = new Promise((_, reject) => {
      setTimeout(() => reject(new Error('Request timeout')), 30000);
    });
    localStorage.removeItem('cart');
    localStorage.removeItem('holdCart');
    localStorage.removeItem('savedOrders');
    try {
      const response = await Promise.race([
        this.plaseOrderService.placeOrder(orderData).toPromise(),
        timeoutPromise
      ]);

      console.log('API Response:', response);
      this.falseMessage = '';
      this.tableError = '';
      this.couponError = '';
      this.cashiermachine = '';

      this.pillId = (response as any).data?.invoice_id;
      this.orderedId = (response as any).data?.order_id;

      if (!(response as any).status) {
        this.handleAPIError(response);
        return;
      }

      // await this.releaseTableAndOrderType();

      // معالجة أنواع الطلبات المختلفة
      if (this.selectedOrderType === 'Takeaway') {
        const dataOrderId = (response as any).data.order_id;
        this.createdOrderId = dataOrderId;
        await this.fetchPillsDetails(this.pillId);
        setTimeout(() => {
          this.printInvoice();
        }, 200);
        this.removeCouponFromLocalStorage();
      }

      const orderId = (response as any).data?.order_id;
      if (!orderId) {
        this.showError('لم يتم استلام رقم الطلب من الخادم.');
        return;
      }

      // تنظيف البيانات
      const savedOrders = JSON.parse(localStorage.getItem('savedOrders') || '[]');
      const orderIdToRemove = orderData.orderId;
      const updatedOrders = savedOrders.filter((savedOrder: any) => savedOrder.orderId !== orderIdToRemove);
      localStorage.setItem('savedOrders', JSON.stringify(updatedOrders));

      this.clearCart();
      this.resetLocalStorage();
      this.resetAddress();
      this.loadCart();
      this.cdr.detectChanges();
      this.successMessage = 'تم تنفيذ طلبك بنجاح';

      if (this.successModal) {
        this.successModal.show();
      }

      setTimeout(() => {
        this.falseMessage = '';
      }, 1500);



    } catch (error: unknown) {
      this.handleSubmissionError(error, orderData);
      // alert(" حدث خطأ اثناء التنفيذ");
    } finally {
      this.isLoading = false;
      this.loading = false;
    }
  }
  validateOfflineDeliveryInfo(): { isValid: boolean; message: string } {
    if (this.selectedOrderType !== 'Delivery') {
      return { isValid: true, message: '' };
    }

    console.log('📋 التحقق من معلومات التوصيل في وضع Offline');

    // التحقق من وجود بيانات العميل الأساسية
    const hasClientInfo = this.clientName && this.clientPhone && this.address;
    const hasFormData = localStorage.getItem('form_data');

    console.log('📊 حالة البيانات:', {
      hasClientInfo,
      hasFormData,
      clientName: this.clientName,
      clientPhone: this.clientPhone,
      address: this.address,
      selectedOrderType: this.selectedOrderType
    });

    // إذا لا توجد أي بيانات
    if (!hasClientInfo && !hasFormData) {
      return {
        isValid: false,
        message: '❌ يرجى إدخال معلومات التوصيل (الاسم، رقم الهاتف، العنوان) قبل حفظ الطلب في وضع عدم الاتصال'
      };
    }

    // إذا كانت البيانات من الفورم
    if (hasFormData) {
      try {
        const formData = JSON.parse(localStorage.getItem('form_data') || '{}');

        console.log('📝 بيانات الفورم:', {
          client_name: formData.client_name,
          address_phone: formData.address_phone,
          address: formData.address
        });

        if (!formData.client_name || formData.client_name.trim().length < 2) {
          return { isValid: false, message: '❌ يرجى إدخال اسم العميل بشكل صحيح (أكثر من حرفين)' };
        }

        if (!formData.address_phone || !this.isValidPhoneNumber(formData.address_phone)) {
          return { isValid: false, message: '❌ يرجى إدخال رقم هاتف صحيح (من 8 إلى 15 رقم)' };
        }

        if (!formData.address || formData.address.trim().length < 5) {
          return { isValid: false, message: '❌ يرجى إدخال العنوان بالكامل (أكثر من 5 أحرف)' };
        }

      } catch (error) {
        console.error('❌ خطأ في تحليل بيانات الفورم:', error);
        return { isValid: false, message: '❌ بيانات التوصيل غير صالحة' };
      }
    }

    // إذا كانت البيانات من معلومات العميل
    if (hasClientInfo) {
      console.log('👤 بيانات العميل المباشرة:', {
        clientName: this.clientName,
        clientPhone: this.clientPhone,
        address: this.address
      });

      if (!this.clientName.trim() || this.clientName.trim().length < 2) {
        return { isValid: false, message: '❌ يرجى إدخال اسم العميل بشكل صحيح (أكثر من حرفين)' };
      }

      if (!this.clientPhone || !this.isValidPhoneNumber(this.clientPhone)) {
        return { isValid: false, message: '❌ يرجى إدخال رقم هاتف صحيح (من 8 إلى 15 رقم)' };
      }

      // في حالة معلومات العميل، نتحقق من العنوان أيضاً
      if (!this.address || this.address.trim().length < 5) {
        return { isValid: false, message: '❌ يرجى إدخال العنوان بالكامل (أكثر من 5 أحرف)' };
      }
    }

    console.log('✅ معلومات التوصيل صالحة للطلب في وضع Offline');
    return { isValid: true, message: '' };
  }
  // دالة مساعدة بسيطة لعرض الأخطاء
  private showError(message: string): void {
    this.falseMessage = message;
    this.isLoading = false;
    this.loading = false;
    setTimeout(() => { this.falseMessage = ''; }, 1500);
  }

  // معالجة أخطاء API
  private handleAPIError(response: any): void {
    if (response.errorData?.error?.cashier_machine_id) {
      this.cashiermachine = response.errorData.error.cashier_machine_id[0];
    } else if (response.errorData?.coupon_code) {
      this.couponError = response.errorData.coupon_code;
    } else if (response.errorData?.table_id) {
      this.tableError = response.errorData.table_id;
    } else {
      this.falseMessage = response.errorData?.error ? `${response.errorData.error}` : `${response.message || 'حدث خطأ أثناء تقديم الطلب'}`;
    }

    setTimeout(() => {
      this.falseMessage = '';
      this.tableError = '';
      this.couponError = '';
      this.cashiermachine = '';
    }, 3500);
    this.isLoading = false;
    this.loading = false;
  }

  // معالجة أخطاء الإرسال
  private async handleSubmissionError(error: unknown, orderData: any): Promise<void> {
    console.error('API Error:', error);

    console.log(navigator.onLine);
    // ✅ التحقق أولاً إذا كان هناك اتصال بالإنترنت
    if (!navigator.onLine) {
      console.log('📴 No internet connection detected');

      // ✅ التحقق من معلومات التوصيل قبل محاولة الحفظ في وضع Offline
      if (this.selectedOrderType === 'Delivery' && !this.currentOrderData) {
        const validation = this.validateOfflineDeliveryInfo();
        if (!validation.isValid) {
          this.falseMessage = validation.message;
          setTimeout(() => { this.falseMessage = ''; }, 3500);
          this.isLoading = false;
          this.loading = false;
          return;
        }
      }

      try {
        console.log('📴 Offline mode: Attempting to save order to IndexedDB');

        // ✅ إضافة بيانات التوصيل للطلب في وضع Offline
        if (this.selectedOrderType === 'Delivery') {
          const formData = JSON.parse(localStorage.getItem('form_data') || '{}');

          // إضافة معلومات التوصيل للطلب
          orderData.delivery_info = {
            client_name: formData.client_name || this.clientName,
            client_phone: formData.address_phone || this.clientPhone,
            address: formData.address || this.address,
            country_code: formData.country_code?.code || formData.country_code || this.selectedCountry.code,
            apartment_number: formData.apartment_number || '',
            building: formData.building || '',
            address_type: formData.address_type || '',
            propertyType: formData.propertyType || '',
            buildingName: formData.buildingName || '',
            note: formData.note || '',
            floor_number: formData.floor_number || '',
            landmark: formData.landmark || '',
            villaName: formData.villaName || '',
            villaNumber: formData.villaNumber || '',
            companyName: formData.companyName || '',
            buildingNumber: formData.buildingNumber || ''
          };

          // استخدام عنوان مؤقت للـ Offline
          orderData.address_id = 9999;

          // أيضاً إضافة معلومات العميل للطلب
          orderData.client_name = formData.client_name || this.clientName;
          orderData.client_phone = formData.address_phone || this.clientPhone;
          orderData.client_country_code = formData.country_code?.code || formData.country_code || this.selectedCountry.code;
        }

        // حفظ الطلب في IndexedDB
        await this.dbService.savePendingOrder(orderData);
        console.log("✅ Order saved to IndexedDB with delivery info");

        // حفظ raw orderData للـ API sync
        const orderDataForSync = { ...orderData };
        await this.dbService.savePendingOrderForSync(orderDataForSync);
        console.log("✅ Raw orderData saved for API sync");

        // تنظيف localStorage
        const savedOrders = JSON.parse(localStorage.getItem('savedOrders') || '[]');
        const orderIdToRemove = orderData.orderId;
        const updatedOrders = savedOrders.filter((savedOrder: any) => savedOrder.orderId !== orderIdToRemove);
        localStorage.setItem('savedOrders', JSON.stringify(updatedOrders));

        // تنظيف البيانات
        this.clearCart();
        this.resetLocalStorage();
        this.resetAddress();
        this.loadCart();

        // عرض رسالة النجاح
        this.successMessage = '✅ تم حفظ الطلب وسيتم إرساله عند عودة الاتصال';
        this.falseMessage = '';
        this.tableError = '';
        this.couponError = '';
        this.cashiermachine = '';

        if (this.successModal) {
          this.successModal.show();
        }

        this.cdr.detectChanges();

      } catch (dbError) {
        console.error('❌ Error saving order to IndexedDB:', dbError);
        this.falseMessage = '❌ فشل حفظ الطلب في وضع عدم الاتصال. يرجى المحاولة مرة أخرى.';
      } finally {
        this.isLoading = false;
        this.loading = false;
      }
      return;
    }

    if (error instanceof HttpErrorResponse && error.status === 0) {
      this.falseMessage = 'يوجد خطأ فى الاتصال يرجى المحاولة مره اخرى';
      setTimeout(() => {
        this.falseMessage = '';
      }, 3500);
      return;
    }

    if ((error instanceof Error && error.message === 'Request timeout') ||
      (typeof error === 'object' && error !== null && 'status' in error && (error as any).status === 504)) {

      try {
        // orderData.offlineTimestamp = new Date().toISOString();
        // orderData.status = 'pending_sync';
        // orderData.errorReason = (error instanceof Error ? error.message : 'Gateway Timeout');

        // const orderId = await this.dbService.savePendingOrder(orderData);
        // console.log("Order saved to IndexedDB due to timeout/error:", orderId);

        this.falseMessage = 'تم حفظ الطلب بسبب مشكلة في الاتصال وسيتم إرساله لاحقًا';
        this.clearCart();
        this.resetLocalStorage();



      } catch (dbError) {
        console.error('Error saving to IndexedDB:', dbError);
        this.falseMessage = 'فشل في إرسال الطلب وحفظه محليًا. يرجى المحاولة مرة أخرى.';
      }
    } else {
      const err = error as any;
      if (err?.error?.errorData?.coupon_code) {
        this.couponError = err.error.errorData.coupon_code[0];
      } else if (err?.error?.errorData?.error?.client_phone) {
        this.falseMessage = err.error?.errorData?.error?.client_phone[0];
      } else if (err?.error?.errorData?.table_id) {
        this.tableError = err.error?.errorData?.table_id[0];
      } else if (err?.error?.errorData?.error) {
        this.falseMessage = `${err.error.errorData.error}`;
      } else if (err?.error?.message) {
        this.falseMessage = `${err.error.message}`;
      } else {
        this.falseMessage = 'حدث خطأ غير متوقع، يرجى المحاولة لاحقًا.';
      }
    }

    setTimeout(() => {
      this.falseMessage = '';
    }, 2000);
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
    // this.cash_amountt = Number((value ?? 0).toFixed(2)) || this.cash_amount;
    this.cash_amountt = value || this.cash_amount;

    localStorage.setItem('cash_amountt', JSON.stringify(this.cash_amountt));
  }

  setCreditAmount(value: number) {
    console.log('💰 setCreditAmount called with:', value);
    this.credit_amountt = value;
    localStorage.setItem('credit_amountt', JSON.stringify(this.credit_amountt));

    // تحديث الـ UI فوراً
    this.cdr.detectChanges();

    console.log('✅ credit_amountt after setting:', this.credit_amountt);

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
      // ✅ تحميل بيانات الإكرامية من localStorage
      const savedTipSummary = localStorage.getItem('finalTipSummary');
      if (savedTipSummary) {
        this.finalTipSummary = JSON.parse(savedTipSummary);
      }

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
      // ✅ تأكد من تحميل بيانات الإكرامية أولاً
      const savedTipSummary = localStorage.getItem('finalTipSummary');
      if (savedTipSummary) {
        this.finalTipSummary = JSON.parse(savedTipSummary);
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
          }, 400)
        );
      }

      document.body.innerHTML = originalHTML;
      /*       location.reload();
       */
      setTimeout(() => {
        localStorage.removeItem('finalTipSummary');
        this.finalTipSummary = null;
      }, 1000);
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
  async selectOrderType(type: string) {

    let previousOrderType = localStorage.getItem('selectedOrderType');
    localStorage.removeItem('selectedOrderType');
    const currentCart = [...this.cartItems];
    this.clearOrderTypeData();

    // ✅ Clear selectedOrderType from localStorage first to ensure correct pricing


    const typeMapping: { [key: string]: string } = {
      'في المطعم': 'dine-in',
      'خارج المطعم': 'Takeaway',
      توصيل: 'Delivery',
      طلبات: 'talabat'
    };

    // استعادة العربة إذا تم مسحها بالخطأ
    if (this.cartItems.length === 0 && currentCart.length > 0) {
      this.cartItems = currentCart;
      this.saveCart();
    }

    this.selectedOrderType = typeMapping[type] || type;



    localStorage.setItem('selectedOrderType', this.selectedOrderType);

    if (localStorage.getItem('selectedOrderType') === "talabat") {
      localStorage.removeItem('appliedCoupon');
      localStorage.removeItem('discountAmount');
      localStorage.removeItem('couponCode');
      localStorage.removeItem('couponTitle');
    }

    this.isOrderTypeSelected = true; // ✅ تم اختيار نوع الطلب
    this.initializePaymentAmount();

    // ✅ عرض رسالة تحديث الأسعار
    this.isUpdatingPrices = true;
    this.cdr.detectChanges();

    // ✅ تحديث الأسعار عند التبديل إلى talabat أو إلى نوع آخر

    for (let i = 0; i < this.cartItems.length; i++) {
      console.log('🔄 selectedOrderType:', this.selectedOrderType);
      await this.findCategoryByDishId(this.cartItems[i]);

    }

    // إعادة حساب الإجماليات بعد تحديث الأسعار
    this.updateTotalPrices();


    // ✅ إخفاء رسالة تحديث الأسعار
    this.isUpdatingPrices = false;



    // ✅ الشرط الجديد: إذا كان الطلب من طلبات وغير مدفوع، اختيار آجل تلقائياً
    if (this.selectedOrderType === 'talabat' && this.selectedPaymentStatus === 'unpaid') {
      this.selectedPaymentMethod = 'deferred';
    }

    // Store in IndexedDB instead of localStorage
    try {
      // this.dbService.saveData('selectedOrderType', {
      //   id: new Date().getTime(), // or use UUID
      //   value: this.selectedOrderType,
      //   timestamp: new Date().toISOString()
      // });

      this.cdr.markForCheck(); // تحديث العرض

      if (previousOrderType === 'talabat' || this.selectedOrderType === 'talabat') {
        setTimeout(() => {
          window.location.reload();
        }, 300);
      }

    } catch (error) {
      console.error('❌ Failed to save order type to IndexedDB:', error);
      // Fallback to localStorage if IndexedDB fails
      localStorage.setItem('selectedOrderType', this.selectedOrderType);

      // ✅ إعادة تحميل الصفحة حتى لو فشلت حفظ IndexedDB
      // setTimeout(() => {
      //   window.location.reload();
      // }, 300);
    }
  }
  async findCategoryByDishId(cartItem: any): Promise<void> {
    try {
      console.log('🔍 Searching for category by dish ID:', cartItem);
      // ✅ فقط إذا كان نوع الطلب "طلبات" نبحث في talabat
      if (this.selectedOrderType !== 'talabat') {
        console.log('✅ Not talabat order type, keeping all items');
        // return; // لا تفعل شيئاً لأنواع الطلبات الأخرى
      }
      const allCategories = await this.dbService.getAll('categories');
      let found = false; // لتتبع إذا تم العثور على الطبق

      for (const category of allCategories) {
        if (category.dishes && Array.isArray(category.dishes)) {
          for (const dishItem of category.dishes) {
            if (
              dishItem.dish?.id === cartItem.dish.id &&
              dishItem.dish.Id_menus_integrations?.[0]?.name_en?.toLowerCase()?.includes('talabat')
            ) {
              found = true;

              const converted = this.convertDish(dishItem);
              const product = this.getProduct(converted);

              console.log('✅ Found dish for update:', product);
              this.updateCartPricesFromDish(cartItem, product);
              break; // خلاص وجدناه، نخرج من اللوب
            }
          }
        }

        if (found) break; // نوقف لو وجدناه في كاتيجوري
      }

      // ❌ لو مفيش dish مطابق
      if (!found && this.selectedOrderType === 'talabat') {
        console.warn('❌ No matching dish found for cart item, removing it...');
        this.removeCartItem(cartItem);
      }

    } catch (error) {
      console.error('❌ Error finding category by dish ID:', error);
    }
  }

  removeCartItem(cartItem: any) {
    console.log('🗑️ Removing cart item:', cartItem);
    // 🧩 قراءة الكارت الحالي من localStorage
    const storedItems = localStorage.getItem('cart');
    if (!storedItems) return;

    let cart = JSON.parse(storedItems);

    // 🗑️ حذف العنصر المطلوب بناءً على uniqueId (أو id لو ده اللي بتستخدميه)
    cart = cart.filter((item: any) => item.uniqueId !== cartItem.uniqueId);

    // 💾 تحديث localStorage بالكارت الجديد
    localStorage.setItem('cart', JSON.stringify(cart));
    // this.dbService.removeFromCart(cartItem.cartItemId); // تحديث IndexedDB لو بتستخدميها للكارت
    this.cartItems = cart; // تحديث المتغير المحلي لو عندك واحد

    // ✅ تحديث الأسعار الإجمالية بعد الحذف (يفعل detectChanges تلقائياً)
    this.updateTotalPrices();

    console.log(`🗑️ Removed item from cart:`, cartItem.dish?.name);

    // ✅ إعادة تحميل الصفحة لتحديث الكارت في الـ UI
    // setTimeout(() => {
    //   window.location.reload();
    // }, 100);
  }
  updateCartPricesFromDish(cartItem: any, dishData: any) {
    // 1. تحديث سعر الطبق الرئيسي
    cartItem.dish.price = dishData.price;

    // 2. تحديث سعر الحجم (لو موجود)
    if (cartItem.selectedSize) {
      const updatedSize = dishData.sizes?.find(
        (s: any) => s.id === cartItem.selectedSize.id
      );
      if (updatedSize) {
        cartItem.selectedSize.price = updatedSize.price;
        cartItem.selectedSize.currency_symbol = updatedSize.currency_symbol || dishData.currency_symbol;
      }
    }

    // 3. تحديث أسعار الإضافات (addons)
    if (cartItem.selectedAddons?.length) {
      cartItem.selectedAddons = cartItem.selectedAddons.map((addon: any) => {
        // دور على الاضافة نفسها داخل dishData
        const updatedAddon = dishData.addon_categories
          ?.flatMap((cat: any) => cat.addons)
          ?.find((a: any) => a.id === addon.id);

        if (updatedAddon) {
          addon.price = updatedAddon.price;
          addon.currency_symbol = updatedAddon.currency_symbol || dishData.currency_symbol;
        }

        return addon;
      });
    }

    console.log('Updated cart item after price sync:', cartItem);
    localStorage.setItem('cart', JSON.stringify(this.cartItems));

    // 4. إعادة حساب المجموع النهائي بعد التحديث
    this.recalculateTotal(cartItem);
  }
  recalculateTotal(cartItem: any) {
    const base = cartItem.dish.price || 0;
    const size = cartItem.selectedSize?.price || 0;
    const addons = cartItem.selectedAddons?.reduce((sum: number, a: any) => sum + (a.price || 0), 0) || 0;
    const qty = cartItem.quantity || 1;

    cartItem.totalPrice = (base + size + addons) * qty;
    cartItem.finalPrice = cartItem.totalPrice;
  }
  convertDish(original: any) {
    const dish = original.dish;

    return {
      id: dish.id,
      name: dish.name,
      description: dish.description,
      price: dish.price,
      currency_symbol: dish.currency_symbol,
      image: dish.image,
      share_link: dish.share_link,
      has_addon: dish.addon_categories?.length > 0,
      has_size: dish.sizes?.length > 0,
      is_integration: true,
      addon_categories: original.addon_categories || [],
      sizes: original.sizes || [],
      Id_menus_integrations: dish.Id_menus_integrations || []
    };
  }


  getProduct(product: any): any {
    console.log('Original Product:', product);
    if (localStorage.getItem('selectedOrderType') === 'talabat') {
      if (Array.isArray(product.Id_menus_integrations) && product.Id_menus_integrations.length > 0) {
        for (let integration of product.Id_menus_integrations) {
          if (integration.name_en?.toLowerCase().includes('talabat')) {
            console.log('✅ Talabat integration found:', integration);

            // تحديث السعر الأساسي للطبق
            const newPrice = integration.menus_integration_dishs?.[0]?.price || product.price;
            product.price = parseFloat(newPrice);

            // تحديث الأسعار داخل الـ sizes
            if (Array.isArray(product.sizes) && Array.isArray(integration.menus_integration_dish_sizes)) {
              product.sizes = product.sizes.map((size: any) => {
                const matchedSize = integration.menus_integration_dish_sizes.find(
                  (s: any) => s.branch_menu_size_id === size.id
                );
                if (matchedSize) {
                  return { ...size, price: parseFloat(matchedSize.price) };
                }
                return size;
              });
            }

            // تحديث الأسعار داخل الـ addons
            if (Array.isArray(product.addon_categories) && Array.isArray(integration.menus_integration_dish_addons)) {
              product.addon_categories = product.addon_categories.map((category: any) => ({
                ...category,
                addons: category.addons.map((addon: any) => {
                  const matchedAddon = integration.menus_integration_dish_addons.find(
                    (a: any) => a.branch_menu_addon_id === addon.id
                  );
                  if (matchedAddon) {
                    return { ...addon, price: parseFloat(matchedAddon.price) };
                  }
                  return addon;
                }),
              }));
            }
          }
        }
      }
    }

    return product;
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
      case 'talabat':
        // No specific data to clear for talabat
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
  // start hanan
  // loadOrderType() {
  //   try {
  //     this.dbService.getAll('selectedOrderType').then((savedOrderTypes) => {
  //       console.log('✅ Order selectedOrderType:', savedOrderTypes);

  //       if (savedOrderTypes.length > 0) {
  //         // Sort by ID to get the latest one
  //         const sorted = savedOrderTypes.sort((a, b) => b.id - a.id);
  //         const last = sorted[0];
  //         this.selectedOrderType = last.value;
  //         this.isOrderTypeSelected = true; // ✅ تم تحميل نوع الطلب

  //         console.log('Last ID:', last.id); // This is the last ID
  //       } else {
  //         // Fallback to localStorage
  //         const fallbackOrderType = localStorage.getItem('selectedOrderType');
  //         if (fallbackOrderType) {
  //           this.selectedOrderType = fallbackOrderType;
  //           this.isOrderTypeSelected = true; // ✅ تم تحميل نوع الطلب

  //           // Migrate to IndexedDB with ID
  //           this.dbService.saveData('selectedOrderType', {
  //             id: new Date().getTime(),
  //             value: this.selectedOrderType
  //           });
  //           localStorage.removeItem('selectedOrderType');
  //         }
  //       }
  //     });
  //   } catch (error) {
  //     console.error('❌ Error loading order type from IndexedDB:', error);
  //     const fallbackOrderType = localStorage.getItem('selectedOrderType');
  //     if (fallbackOrderType) {
  //       this.selectedOrderType = fallbackOrderType;
  //       this.isOrderTypeSelected = true; // ✅ تم تحميل نوع الطلب

  //     }
  //   }
  // }


  // end hanan
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
    // ✅ الشرط الجديد: إذا كان نوع الطلب "طلبات" وغير مدفوع، اختيار آجل تلقائياً
    if (this.selectedOrderType === 'talabat' && this.selectedPaymentStatus === 'unpaid') {
      this.selectedPaymentMethod = 'deferred';
      return;
    }
    // إذا كان نوع الطلب "طلبات"، عيّن طريقة الدفع المناسبة تلقائياً
    if (this.selectedOrderType === 'talabat') {
      if (this.selectedPaymentStatus === 'paid') {
        this.selectedPaymentMethod = 'cash'; // مدفوع → كاش
      } else if (this.selectedPaymentStatus === 'unpaid') {
        this.selectedPaymentMethod = 'deferred'; // غير مدفوع → آجل
      }
      return;
    }
    // If Visa is selected, force "paid" as payment status
    if (this.selectedPaymentMethod === 'credit') {
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
    // if (_removeCoupon == true) {
    //   this.removeCoupon()
    // }
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
    // ✅ الشرط الجديد: إذا كان الطلب من طلبات وغير مدفوع، اختيار آجل تلقائياً
    if (this.selectedOrderType === 'talabat' && this.selectedPaymentStatus === 'unpaid') {
      this.selectedPaymentMethod = 'deferred';
    }

    // إذا كان نوع الطلب "طلبات"، عيّن طريقة الدفع المناسبة
    if (this.selectedOrderType === 'talabat') {
      if (this.selectedPaymentStatus === 'paid') {
        this.selectedPaymentMethod = 'cash'; // مدفوع → كاش
      } else if (this.selectedPaymentStatus === 'unpaid') {
        this.selectedPaymentMethod = 'deferred'; // غير مدفوع → آجل
      }
    }
    console.log('Payment Status:', this.selectedPaymentStatus); // paid or unpaid
    if (this.selectedPaymentStatus === 'unpaid') {
      this.cash_amountt = 0;
      this.credit_amountt = 0;
      this.referenceNumber = '';
      this.referenceNumberTouched = false;
      // this.selectedPaymentMethod = '';
      localStorage.removeItem('cash_amountt');
      localStorage.removeItem('credit_amountt');
      localStorage.removeItem('referenceNumber');
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
    // ✅ التحقق من معلومات التوصيل في وضع Offline
    if (this.selectedOrderType === 'Delivery') {
      const validation = this.validateOfflineDeliveryInfo();
      if (!validation.isValid) {
        this.falseMessage = validation.message;
        setTimeout(() => { this.falseMessage = ''; }, 3000);
        return;
      }
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
    if (this.selectedOrderType.toLowerCase() === 'talabat') {
      const formData = JSON.parse(localStorage.getItem('form_data') || '{}');
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
    localStorage.removeItem('holdCart');
    localStorage.removeItem('cart');
    this.client = '';
    this.clientPhone = '';
    this.cash_amountt = 0;
    this.credit_amountt = 0;
    this.selectedPaymentStatus = 'unpaid';
    this.resetAddress()
    this.tableNumber = null;
    this.FormDataDetails = null;
    this.successMessage = 'تم حفظ طلبك بنجاح';
    this.successModal.show();
    localStorage.removeItem('finalOrderId');
    this.finalOrderId = '';
    this.currentOrderData = null;
    this.currentOrderId = null;
    this.selectedPaymentMethod = null;
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
    // التحقق من صحة البيانات
    if (!this.client || !this.clientPhone) {
      this.clientError = 'يرجى إدخال جميع البيانات المطلوبة';
      return;
    }

    this.isLoading = true;
    this.clientError = '';

    try {
      // حفظ البيانات في localStorage
      localStorage.setItem('client', this.client);
      localStorage.setItem('clientPhone', this.clientPhone);
      localStorage.setItem('selectedCountryCode', this.selectedCountry.code);

      // تحديث المتغيرات المحلية
      this.clientStoredInLocal = this.client;
      this.clientPhoneStoredInLocal = this.clientPhone;
      this.clientInfoApplied = true;

      // إغلاق المودال مباشرة
      this.closeClientModal();

    } catch (error) {
      console.error('Error saving client info:', error);
      this.clientError = 'حدث خطأ في حفظ البيانات';
    } finally {
      this.isLoading = false;
    }
  }

  clearClientInfo() {
    // إعادة تعيين القيم
    this.client = '';
    this.clientPhone = '';
    this.clientStoredInLocal = null;
    this.clientPhoneStoredInLocal = null;
    this.clientInfoApplied = false;

    // إزالة من localStorage
    localStorage.removeItem('client');
    localStorage.removeItem('clientPhone');
    localStorage.removeItem('selectedCountryCode');

    // إغلاق المودال
    this.closeClientModal();
  }

  closeClientModal() {
    // إغلاق المودال باستخدام Bootstrap
    const clientModal = document.getElementById('clientModal');
    if (clientModal) {
      const modalInstance = bootstrap.Modal.getInstance(clientModal);
      if (modalInstance) {
        modalInstance.hide();
      }
    }

    // تنظيف الـ DOM
    this.cleanupModalBackdrop();
  }

  cleanupModalBackdrop() {
    // إزالة backdrop يدويًا إذا بقي
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());

    // إزالة classes من body
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
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

  // fetchCountries() {
  //   try {
  //     // First, check if we already have countries stored in DB
  //     this.dbService.getAll('countries').then((storedCountries) => {
  //       if (storedCountries && storedCountries.length > 0) {
  //         console.log('✅ Loaded countries from DB:', storedCountries);
  //         this.countryList = storedCountries;
  //         this.filterAllowedCountries();
  //         return;
  //       }

  //       // If no countries in DB, fetch from API
  //       this.authService.getCountries().subscribe({
  //         next: async (response) => { // ✅ أضف async هنا
  //           if (response.data && Array.isArray(response.data)) {
  //             this.countryList = response.data.map(
  //               (country: { phone_code: string; image: string }) => ({
  //                 code: country.phone_code,
  //                 flag: country.image,
  //               })
  //             );

  //             // ✅ الآن يمكن استخدام await داخل دالة async
  //             try {
  //               this.dbService.deleteFromIndexedDB('countries');
  //               for (const country of this.countryList) {
  //                 await this.dbService.saveData('countries', country);
  //               }
  //               console.log('✅ Countries saved to DB');
  //             } catch (dbError) {
  //               console.error('❌ Error saving countries to DB:', dbError);
  //             }

  //             this.filterAllowedCountries();
  //           } else {
  //             this.errorMessage = 'No country data found in the response.';
  //           }
  //         },
  //         error: (error) => {
  //           console.error('❌ API Error:', error);
  //           this.errorMessage = 'Failed to load country data.';
  //         },
  //       });
  //     }).catch((error) => {
  //       console.error('❌ Error loading countries from DB:', error);
  //     });
  //   } catch (error) {
  //     console.error('❌ Error handling countries:', error);
  //     this.errorMessage = 'Something went wrong while fetching countries.';
  //   }
  // }
  filterAllowedCountries() {
    const allowedCountryCodes: string[] = ['+20', '+962', '+964', '+212', '+963', '+965', '+966'];

    this.filteredCountries = this.countryList.filter((country: any) =>
      allowedCountryCodes.includes(
        country.code.replace(/\s+/g, '').replace('ـ', '').replace('–', '')
      )
    );
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

  // hanan
  selectPaymentMethod(method: 'cash' | 'credit' | 'cash + credit' | 'deferred'): void {
    this.selectedPaymentMethod = method;
    const billAmount = this.getCartTotal();
    console.log('Selected Payment Method:', this.selectedPaymentMethod);
    if (method === 'cash') {
      // ✅ تعيين cash_amount تلقائياً بقيمة المبلغ المستحق
      this.cash_amountt = billAmount;
      this.cash_amount = billAmount;
      this.cashPaymentInput = " ";

      this.credit_amountt = 0; // إعادة تعيين الفيزا
      localStorage.setItem('cash_amountt', JSON.stringify(this.cash_amountt));
      localStorage.setItem('credit_amountt', JSON.stringify(this.credit_amountt));

      console.log('💰 تم تعيين cash_amount تلقائياً:', {
        method: 'cash',
        cash_amountt: this.cash_amountt,
        billAmount: billAmount
      });
    }
    //

    if (method === 'credit') {
      // ✅ تعيين credit_amount تلقائياً بقيمة المبلغ المستحق
      this.credit_amountt = billAmount;
      this.credit_amount = billAmount;

      this.cash_amountt = 0;
      this.cashPaymentInput = 0;
      localStorage.setItem('cash_amountt', JSON.stringify(this.cash_amountt));
      localStorage.setItem('credit_amountt', JSON.stringify(this.credit_amountt));

      console.log('💳 تم تعيين credit_amount تلقائياً:', {
        method: 'credit',
        credit_amountt: this.credit_amountt,
        billAmount: billAmount
      });
    }
    //  //////
    if (method === 'cash + credit') {
      if (this.cashPaymentInput === 0) {
        this.cashAmountMixed = " ";
        this.creditAmountMixed = " ";
      }

    }

    // إذا كان نوع الطلب "طلبات" ومدفوع، تأكدي أن الطريقة هي "كاش"
    if (this.selectedOrderType === 'talabat' && this.selectedPaymentStatus === 'paid') {
      this.selectedPaymentMethod = 'cash';
      // return;
    }
    // إعادة تعيين القيم عند تغيير طريقة الدفع
    if (method === 'cash') {
      this.cashAmountMixed = 0;
      this.creditAmountMixed = 0;
    } else if (method === 'credit') {
      this.cashAmountMixed = 0;
      this.creditAmountMixed = 0;
      this.cashPaymentInput = " ";
      this.cash_amountt = 0;
      // this.credit_amountt = this.getCartTotal();
      // فتح مودال الإكرامية مباشرة للفيزا
      // const billAmount = this.getCartTotal();
      // this.openTipModal(this.tipModalContent, billAmount, billAmount);
    } else if (method === 'cash + credit') {
      this.cashPaymentInput = 0;
      // تعيين القيم الافتراضية للدفع المختلط
      const billAmount = this.getCartTotal();
      this.cashAmountMixed = " ";
      this.creditAmountMixed = " ";
      this.cash_amountt = this.cashAmountMixed;
      this.credit_amountt = this.creditAmountMixed;
    }
    else if (method === 'deferred') {
      // إعادة تعيين القيم للدفع الآجل
      this.cashAmountMixed = 0;
      this.creditAmountMixed = 0;
      this.cashPaymentInput = 0;
      this.cash_amountt = 0;
      this.credit_amountt = 0;
    }
  }

  getNearestAmount(amount: number, base: number): number {
    if (amount <= 0) return base;

    // التقريب للأعلى لأقرب مضاعف للقاعدة (base)
    const roundedAmount = Math.ceil(amount / base) * base;
    return roundedAmount;
  }
  // تحديث دالة فتح مودال الإكرامية
  openTipModal(content: any, billAmount: number, paymentAmount: number, paymentMethod?: string): void {
    this.tempBillAmount = billAmount;
    this.tempPaymentAmount = paymentAmount;
    this.tempChangeAmount = paymentAmount - billAmount;

    // تعيين طريقة الدفع إذا تم تمريرها
    if (paymentMethod) {
      this.selectedPaymentMethod = paymentMethod;
    }

    if (!this.selectedTipType) {
      this.selectedTipType = 'no_tip';
    }

    if (!this.specificTipAmount) {
      this.specificTipAmount = 0;
    }

    this.modalService.open(content, {
      centered: true,
      size: 'md'
    }).result.then((result) => {
      console.log('Tip Modal Closed with final result:', result);
    }, (reason) => {
      console.log('Tip Modal Dismissed:', reason);
    });
  }

  /**
   * لتحديد نوع الإكرامية المُختار وتحديث قيمة الإكرامية النهائية.
   * @param type نوع الإكرامية المُختار
   */
  selectTipOption(type: 'tip_the_change' | 'tip_specific_amount' | 'no_tip'): void {
    this.selectedTipType = type;
    this.tip_aption = type; // حفظ الخيار المحدد

    switch (type) {
      case 'tip_the_change':
        this.specificTipAmount = this.tempChangeAmount;
        break;
      case 'no_tip':
        this.specificTipAmount = 0;
        break;
      case 'tip_specific_amount':
        let initialTipAmount = this.tempChangeAmount > 0 ? this.tempChangeAmount : 0;
        // this.specificTipAmount = parseFloat(initialTipAmount.toFixed(2));
        this.specificTipAmount = 0;
        break;
    }
    console.log('✅ تم اختيار نوع الإكرامية:', {
      selectedTipType: this.selectedTipType,
      tip_aption: this.tip_aption,
      specificTipAmount: this.specificTipAmount
    });
  }

  // تحديث دالة تأكيد الإكرامية
  confirmTipAndClose(modal: any): void {
    let finalTipAmount: number = 0;
    let additionalPaymentRequired: number = 0;
    let originalPaymentAmount: number = this.tempPaymentAmount;

    if (this.selectedTipType === 'tip_the_change') {
      finalTipAmount = this.tempChangeAmount;
      additionalPaymentRequired = 0;
    } else if (this.selectedTipType === 'tip_specific_amount') {
      finalTipAmount = Math.max(0, this.specificTipAmount);

      // ✅ حساب المبلغ الإضافي المطلوب
      if (finalTipAmount > this.tempChangeAmount) {
        additionalPaymentRequired = finalTipAmount - this.tempChangeAmount;
        // تحديث المبلغ المدفوع الإجمالي
        this.tempPaymentAmount = this.tempPaymentAmount + additionalPaymentRequired;
      }
    }
    else if (this.selectedTipType === 'no_tip') {
      finalTipAmount = 0; // ⬅️ التأكد من أن الإكرامية = 0
      additionalPaymentRequired = 0;
    }
    const changeToReturn = Math.max(0, this.tempPaymentAmount - (this.tempBillAmount + finalTipAmount));

    // حساب المبالغ النهائية بناءً على طريقة الدفع
    let cashFinal = 0;
    let creditFinal = 0;

    if (this.selectedPaymentMethod === 'cash') {
      cashFinal = this.tempPaymentAmount;
      creditFinal = 0;
    } else if (this.selectedPaymentMethod === 'credit') {
      cashFinal = 0;
      creditFinal = this.tempPaymentAmount;
    } else if (this.selectedPaymentMethod === 'cash + credit') {
      cashFinal = this.cashAmountMixed || 0;
      creditFinal = this.creditAmountMixed || 0;
      // const totalPaid = this.cashAmountMixed + this.creditAmountMixed + additionalPaymentRequired;

      // ✅ إذا كان هناك مبلغ إضافي مطلوب للإكرامية، نضيفه بنفس النسب
      if (additionalPaymentRequired > 0) {
        const totalOriginal = cashFinal + creditFinal;
        if (totalOriginal > 0) {
          const cashRatio = cashFinal / totalOriginal;
          const creditRatio = creditFinal / totalOriginal;

          cashFinal += additionalPaymentRequired * cashRatio;
          creditFinal += additionalPaymentRequired * creditRatio;
        }
      }
    }

    // إنشاء الكائن مع جميع الخصائص
    this.finalTipSummary = {
      total: this.tempBillAmount,
      serviceFee: 0,
      billAmount: this.tempBillAmount,
      paymentAmount: this.tempPaymentAmount,
      paymentMethod: this.selectedPaymentMethod === 'cash' ? 'كاش' :
        this.selectedPaymentMethod === 'credit' ? 'فيزا' : 'كاش + فيزا',
      tipAmount: finalTipAmount,
      grandTotalWithTip: this.tempBillAmount + finalTipAmount,
      changeToReturn: changeToReturn,
      cashAmountMixed: cashFinal,
      creditAmountMixed: creditFinal,
      additionalPaymentRequired: additionalPaymentRequired,
      originalPaymentAmount: originalPaymentAmount,
      tips_aption: this.selectedTipType
    };
    // ✅ حفظ في localStorage للطباعة
    localStorage.setItem('finalTipSummary', JSON.stringify(this.finalTipSummary));
    // ✅ إذا كان هناك مبلغ إضافي مطلوب، نعرض تأكيد للمستخدم
    if (additionalPaymentRequired > 0) {
      this.showAdditionalPaymentConfirmation(additionalPaymentRequired, modal);
    } else {
      modal.close(this.finalTipSummary);
    }

    // إعادة تعيين المتغيرات
    // this.selectedTipType = 'no_tip';
    // this.specificTipAmount = 0;
  }
  showAdditionalPaymentConfirmation(additionalAmount: number, modal: any) {
    const confirmed = confirm(
      `لتحقيق الإكرامية المطلوبة (${this.specificTipAmount} ج.م)، تحتاج لدفع ${additionalAmount} ج.م إضافية.\n\nهل تريد المتابعة؟`
    );

    if (confirmed) {
      modal.close(this.finalTipSummary);
    } else {
      // إلغاء وتراجع عن الحسابات
      this.tempPaymentAmount = this.finalTipSummary!.originalPaymentAmount!;
      this.finalTipSummary = null;
      this.specificTipAmount = 0;
    }
  }

  getChangeToReturn(changeAmount: number, tipAmount: number): number {
    return Math.max(0, changeAmount - tipAmount);
  }

  selectPaymentSuggestionAndOpenModal(type: 'billAmount' | 'amount50' | 'amount100', billAmount: number, paymentAmount: number, modalContent: any): void {
    this.selectedSuggestionType = type; // هنا يتم حفظ النوع الذي تم الضغط عليه
    this.selectedPaymentSuggestion = paymentAmount;
    // ✅ التحقق من أن المبلغ غير صفر أو سالب
    if (paymentAmount <= 0) {
      this.paymentError = 'المبلغ المقترح غير صالح';
      return;
    }
    if (paymentAmount >= billAmount) {
      this.cashPaymentInput = paymentAmount;
      this.paymentError = ''; // مسح أي أخطاء
      this.openTipModal(modalContent, billAmount, paymentAmount);
    }
    else {
      this.paymentError = `المبلغ المقترح (${paymentAmount}) أقل من المبلغ المستحق (${billAmount})`;
    }
  }

  handleManualPaymentBlur(billAmount: number, modalContent: any): void {
    this.selectedPaymentSuggestion = null; // إعادة تعيين عند الإدخال اليدوي

    console.log('Bill Amount:', billAmount, 'Entered:', this.cashPaymentInput);
    const currentPaymentInput = this.cashPaymentInput;
    if (!currentPaymentInput || currentPaymentInput <= 0) {
      // this.cashPaymentInput = billAmount;
      // this.cash_amountt = billAmount;
      this.cash_amount = billAmount;
      console.log('💰 تم تعيين المبلغ المستحق تلقائياً:', billAmount);
    }
    // ✅ إضافة تحقق صريح للمبلغ المدخل
    if (currentPaymentInput <= 0) {
      this.paymentError = 'يرجى إدخال مبلغ صحيح أكبر من الصفر';
      return;
    }
    if (currentPaymentInput < billAmount) {
      this.paymentError = `المبلغ المدخل (${currentPaymentInput}) أقل من المبلغ المستحق (${billAmount})`;
      return;
    }
    // مسح أي أخطاء سابقة إذا كان المبلغ صحيحاً
    this.paymentError = '';
    // ✅ التصحيح: حفظ في المتغير الصحيح بناءً على طريقة الدفع
    if (this.selectedPaymentMethod === 'cash') {
      this.cash_amountt = currentPaymentInput;
      this.cash_amount = currentPaymentInput;
      console.log('💰 تم حفظ المبلغ النقدي:', this.cash_amountt);
    } else if (this.selectedPaymentMethod === 'credit') {
      this.credit_amountt = currentPaymentInput;
      this.credit_amount = currentPaymentInput;
      console.log('💳 تم حفظ المبلغ بالفيزا:', this.credit_amountt);
    }
    // this.cash_amount = currentPaymentInput;
    console.log('💰 تم حفظ المبلغ المدخل:', {
      مدخل: currentPaymentInput,
      محفوظ: this.cash_amountt,
      المستحق: billAmount
    });
    // if (currentPaymentInput > 0 && currentPaymentInput >= billAmount) {
    //   this.openTipModal(modalContent, billAmount, currentPaymentInput);
    // }
  }

  // حساب مبلغ الفيزا بناءً على الكاش
  calculateCreditAmount(billAmount: number): void {
    const remaining = billAmount - this.cashAmountMixed;
    this.creditAmountMixed = Math.max(0, remaining);
  }
  // حساب مبلغ الكاش بناءً على الفيزا
  calculateCashAmount(billAmount: number): void {
    const remaining = billAmount - this.creditAmountMixed;
    this.cashAmountMixed = Math.max(0, remaining);
  }
  // حساب المبلغ المتبقي
  getRemainingAmount(billAmount: number): number {
    const totalPaid = this.cashAmountMixed + this.creditAmountMixed;
    return billAmount - totalPaid;
  }


  // فتح مودال الإكرامية للدفع المختلط
  openMixedPaymentTipModal(billAmount: number, modalContent: any): void {
    const totalPaid = this.cashAmountMixed + this.creditAmountMixed;

    // التحقق من أن المبلغ المدفوع كافي
    if (totalPaid >= billAmount) {
      this.tempBillAmount = billAmount;
      this.tempPaymentAmount = totalPaid;
      this.tempChangeAmount = totalPaid - billAmount;

      this.openTipModal(modalContent, billAmount, totalPaid);
    } else {
      // يمكن إضافة رسالة تنبيه هنا إذا أردت
      // console.warn('المبلغ المدفوع غير كافي لفتح مودال الإكرامية');
    }
  }

  // التحقق إذا كان المبلغ المدفوع كافي
  isPaymentSufficient(billAmount: number): boolean {
    return this.getRemainingAmount(billAmount) <= 0;
  }
  // دالة للتحقق من وجود معلومات التوصيل
  hasDeliveryInfo(): boolean {
    if (this.selectedOrderType !== 'Delivery') {
      return true; // ليس طلب توصيل، لا داعي للتحقق
    }
    // ✅ في حالة عدم وجود اتصال، نعتبر المعلومات متوفرة
    if (!this.isOnline) {
      console.log('📴 Offline mode - delivery info considered available');
      return true;
    }
    // التحقق من وجود البيانات الأساسية للتوصيل
    const hasBasicInfo = this.clientName && this.address && this.addressPhone;
    const hasFormData = this.FormDataDetails &&
      this.FormDataDetails.client_name &&
      this.FormDataDetails.address &&
      this.FormDataDetails.address_phone;
    const hasClientInfo = this.clientName &&
      this.clientPhone &&
      this.address;
    return hasBasicInfo || hasFormData || hasClientInfo;
  }

  // دالة للتحقق من اكتمال معلومات العميل للتوصيل
  isDeliveryInfoComplete(): boolean {
    if (this.selectedOrderType !== 'Delivery') {
      return true;
    }

    return this.hasDeliveryInfo();
  }
  // دالة للتحقق من صحة رقم الهاتف
  isValidPhoneNumber(phone: string): boolean {
    const phoneRegex = /^[0-9]{10,15}$/;
    return phoneRegex.test(phone.replace(/\D/g, ''));
  }

  // دالة شاملة للتحقق من بيانات التوصيل
  validateDeliveryInfo(): { isValid: boolean; message: string } {
    if (this.selectedOrderType !== 'Delivery') {
      return { isValid: true, message: '' };
    }

    // ✅ في حالة عدم وجود اتصال، لا نطلب معلومات التوصيل
    if (!this.isOnline) {
      console.log('📴 Offline mode - delivery info considered available');
      return { isValid: true, message: '' };
    }

    // التحقق من وجود البيانات الأساسية للتوصيل
    const hasBasicInfo = this.clientName && this.address && this.addressPhone;
    const hasFormData = this.FormDataDetails &&
      this.FormDataDetails.client_name &&
      this.FormDataDetails.address &&
      this.FormDataDetails.address_phone;

    if (!hasBasicInfo && !hasFormData) {
      return { isValid: false, message: 'يرجى إدخال معلومات التوصيل' };
    }

    if (!this.clientName || this.clientName.trim().length < 2) {
      return { isValid: false, message: 'يرجى إدخال اسم العميل' };
    }

    if (!this.address || this.address.trim().length < 5) {
      return { isValid: false, message: 'يرجى إدخال العنوان بالكامل' };
    }

    if (!this.addressPhone || !this.isValidPhoneNumber(this.addressPhone)) {
      return { isValid: false, message: 'يرجى إدخال رقم هاتف صحيح' };
    }

    return { isValid: true, message: '' };
  }

  filterCountries() {
    this.filteredCountries = this.countryList.filter((country) =>
      country.code.toLowerCase().includes(this.searchTerm.toLowerCase())
    );
  }
  getOrderTypeLabel(type: string): string {
    const map: any = {
      'dine-in': 'في المطعم',
      'Takeaway': 'استلام',
      'talabat': 'طلبات',
      'Delivery': 'توصيل'
    };

    return map[type] || type;
  }

}
