import {
  Component,
  OnInit,
  ViewChild,
  ElementRef,
  ChangeDetectorRef,
  TemplateRef,
} from '@angular/core';
import { PillDetailsService } from '../services/pill-details.service';
import { OrderListDetailsService } from '../services/order-list-details.service';
import { CommonModule, DecimalPipe } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { DatePipe } from '@angular/common';
import { PrintedInvoiceService } from '../services/printed-invoice.service';
import { Router } from '@angular/router';
declare var bootstrap: any;
import { FormsModule } from '@angular/forms';
import { ConfirmDialogComponent } from "../shared/ui/component/confirm-dialog/confirm-dialog.component";
import { finalize, take } from 'rxjs';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { baseUrl, baseUrl2 } from '../environment';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { PrintTimeService } from '../services/print-time.service';
import { ReceiptComponent } from '../receipt/receipt.component';
import { SilentPrintService } from '../services/silent-print.service';


@Component({
  selector: 'app-pill-edit',
  imports: [CommonModule, DecimalPipe, FormsModule, ConfirmDialogComponent, ReceiptComponent],
  templateUrl: './pill-edit.component.html',
  styleUrl: './pill-edit.component.css',
  providers: [DatePipe],
})
export class PillEditComponent {
  paymentDevices: Array<{
    id: number;
    name: string;
    ip: string;
    status: 'active' | 'inactive';
    lastUsedAt?: string | null;
    isRecommended?: boolean;
  }> = [];
  selectedPaymentDeviceId: number | null = null;
  paymentDeviceError: string = '';
  readonly LAST_USED_PAYMENT_DEVICE_STORAGE_KEY = 'last_used_payment_device_id';
  @ViewChild('printedPill') printedPill!: ElementRef;
  @ViewChild('printDialog') confirmationDialog!: ConfirmDialogComponent;
  @ViewChild('tipModalContent') tipModalContent!: TemplateRef<any>;

  loading: boolean = false;
  /** تفاصيل الطلب — طي/توسيع مثل order-details */
  isOrderDetailsOpen = true;
  receiptData: any;
  isPrinting = false;
  // @ViewChild('deliveredButton', { static: false }) deliveredButton!: ElementRef;
  invoices: any[] = [];
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
  trackingStatus: any = '';
  orderNumber: any;
  addresDetails: any;
  isShow: boolean = true;
  note: string = 'لا يوجد';
  cuponValue: any;
  cashier_machine_id!: number;
  showPrices?: boolean;
  test?: boolean;
  paymentMethod: any;
  selectedPaymentMethod: 'cash' | 'credit' | 'cash + credit' = 'cash';
  cash_value: number | null = null;
  credit_value: number | null = null;
  amountError: boolean = false;
  Delivery_show_delivered_only: boolean = false;
  referenceNumber: any;
  referenceNumberTouched: boolean = false;
  referenceNumberError: string = '';
  paymentAmountError: string = '';
  deliveryStatusError: string = '';
  formSubmitted: boolean = false;
  // Coupon / Discount
  couponCode: string = '';
  appliedCoupon: any = null;
  discountAmount: number = 0;
  couponTitle: string = '';
  couponType: 'percentage' | 'fixed' | '' = '';
  couponMessage: string = '';
  couponError: string = '';
  manualDiscountType: 'percentage' | 'fixed' | null = null;
  manualDiscountValue: number | null = null;
  isApplyingCoupon: boolean = false;

  // متغيرات الإكرامية
  selectedTipType: 'tip_the_change' | 'tip_specific_amount' | 'no_tip' = 'no_tip';
  specificTipAmount: any = " ";
  tempBillAmount: number = 0;
  tempPaymentAmount: number = 0;
  tempChangeAmount: number = 0;
  finalTipSummary: any = null;
  paymentError: string = '';
  selectedSuggestionType: 'billAmount' | 'amount50' | 'amount100' | null = null;
  cashPaymentInput: any = " ";
  creditPaymentInput: any = " ";
  cashAmountMixed: any = " ";
  creditAmountMixed: any = " ";
  currencySymbol: string = '';

  // متغيرات timeout المودال
  tipModalTimeoutRef: any = null;
  tipModalCountdownRef: any = null;
  tipModalTimeRemaining: number = 0;
  tipModalTimeoutDuration: number = 30;
  tipModalWarningTime: number = 10;
  tipModalWarningShown: boolean = false;

  // Additional Payment Modal variables
  additionalPaymentRequiredAmount: number = 0;
  requiredTipAmount: number = 0;
  currentPaymentAmount: number = 0;
  additionalPaymentModalRef: any = null;
  pendingTipModal: any = null; // لحفظ الـ modal الأصلي للإكرامية

  constructor(
    private pillDetailsService: PillDetailsService,
    private orderListDetailsService: OrderListDetailsService,
    private route: ActivatedRoute,
    private orderService: PillDetailsService,
    private cdr: ChangeDetectorRef,
    private datePipe: DatePipe,
    private printedInvoiceService: PrintedInvoiceService,
    private router: Router,
    private http: HttpClient,
    private modalService: NgbModal,
    private printTime: PrintTimeService,
    private silentPrint: SilentPrintService
  ) {
    this.currencySymbol = localStorage.getItem('currency_symbol') || 'ج.م';
  }

  private extractDateAndTime(branch: any): void {
    const { created_at } = branch;

    if (created_at) {
      const { dateStr, timeStr } = this.printTime.formatForPrint(created_at);
      this.date = dateStr;
      this.time = timeStr;
    }
  }
  order_id: any;

  ngOnInit(): void {
    this.route.paramMap.subscribe((params) => {
      this.pillId = params.get('id');

      if (this.pillId) {
        this.fetchPillsDetails(this.pillId);
      }
    });
    this.fetchPillsDetails(this.pillId);

    this.fetchTrackingStatus();
    this.loadPaymentDevices();
    // this.getNoteFromLocalStorage();
    this.referenceNumber = '';
    this.referenceNumberTouched = false;
    this.formSubmitted = false;
    this.cashier_machine_id = Number(
      localStorage.getItem('cashier_machine_id')
    );
    const storedData: string | null =
      localStorage.getItem('cashier_machine_id');

    if (storedData !== null) {
      // Safe to parse since storedData is guaranteed to be a string
      const transactionDataFromLocalStorage = JSON.parse(storedData);

      // Access the cashier_machine_id
      this.cashier_machine_id = transactionDataFromLocalStorage;

      console.log(this.cashier_machine_id, 'one'); // Output: 1
    } else {
      console.log('No data found in localStorage.');
    }
  }
  getNoteFromLocalStorage() {
    throw new Error('Method not implemented.');
  }

  // Print Invoice method
  // printInvoice() {
  //   let printContent = document.getElementById('printSection')!.innerHTML;
  //   let originalContent = document.body.innerHTML;

  //   document.body.innerHTML = printContent;
  //   window.print();
  //   document.body.innerHTML = originalContent;
  //   location.reload();
  // }

  fetchTrackingStatus() {
    this.pillDetailsService
      .getPillsDetailsById(this.pillId)
      .subscribe((response) => {
        if (response.status && response.data.invoices.length > 0) {
          this.trackingStatus =
            response.data.invoices[0]['tracking-status'] || '';
          console.log('Tracking Status Retrieved:', this.trackingStatus);
        }
      });
  }
  orderType: string = '';
  totalll: any;

  fetchPillsDetails(pillId: string): void {
    console.log('aaaaaaaaaaaaa');
    this.pillDetailsService.getPillsDetailsById(pillId).subscribe({
      next: (response: any) => {
        this.order_id = response.data.order_id
        this.invoices = response.data.invoices;
        // الحصول على tracking-status
        const trackingKey = this.invoices[0]?.['tracking-status']
          || this.invoices[0]?.['Tracking_status']
          || this.invoices[0]?.['order_status'];

        this.trackingStatus = trackingKey || '';
        console.log(this.invoices[0]?.order_type);
        this.orderType = this.invoices[0]?.order_type || '';

        // restore coupon data if exists on invoice (بعد التجزئة لا يكون هناك كوبون على الطلب الأصلي)
        const summary = this.invoices?.[0]?.invoice_summary;
        if (summary) {
          const hasCoupon = summary.coupon_id != null && summary.coupon_id !== '';
          this.couponType = hasCoupon ? (summary.coupon_type || '') : '';
          this.couponTitle = hasCoupon ? (summary.coupon_title || '') : '';
          this.discountAmount = hasCoupon ? (Number(summary.coupon_value) || 0) : 0;
          this.couponCode = hasCoupon ? (summary.coupon_code || '') : '';

          // التأكد من وجود subtotal_price_before_coupon، وإلا استخدام subtotal_price أو total_price
          if (!summary.subtotal_price_before_coupon) {
            summary.subtotal_price_before_coupon = summary.subtotal_price || summary.total_price || 0;
          }

          // تحديث totalll من invoice_summary.total_price (يجب أن يكون محدثاً من الـ backend)
          // إذا كان هناك كوبون مطبق، يجب أن يكون total_price محدثاً بالفعل
          this.totalll = Number(summary.total_price) || 0;
        } else {
          this.totalll = this.invoices[0].invoice_summary.total_price;
        }

        // const trackingKey = this.invoices[0]?.['tracking-status'];
        // this.trackingStatus = trackingKey || '';
        this.orderNumber = response.data?.order_id != null ? String(response.data.order_id) : '';
        this.couponType = this.invoices[0]?.invoice_summary?.coupon_type;

        this.addresDetails = this.invoices[0]?.address_details || {};

        this.paymentStatus = this.invoices[0]?.transactions[0]?.['payment_status'];
        this.paymentMethod = this.invoices[0]?.transactions[0].payment_method;
        //  if (this.trackingStatus === 'completed' ) {
        //   this.deliveredButton?.nativeElement.click();
        //   }
        this.isDeliveryOrder = this.invoices?.some(
          (invoice: any) => invoice.order_type === 'Delivery'
        );
        // console.log( this.invoices[0].order_type,'this.invoices[0].order_type')

        this.branchDetails = this.invoices?.map(
          (e: { branch_details: any }) => e.branch_details
        );
        this.orderDetails = this.invoices?.map((e: any) => e.orderDetails);

        // this.invoiceSummary = this.invoices?.map((e: any) => e.invoice_summary );
        // this.invoiceSummary = this.invoices?.map((e: any) => ({
        //   ...e.invoice_summary,
        //   currency_symbol: e.currency_symbol,
        // }));
        this.invoiceSummary = this.invoices?.map((e: any) => {
          let summary = {
            ...e.invoice_summary,
            currency_symbol: e.currency_symbol,
          };
          // بعد الدمج لا يكون هناك كوبون على الفاتورة: إخفاء الكوبون من العرض
          if (summary.coupon_id == null || summary.coupon_id === '') {
            summary.coupon_value = 0;
            summary.coupon_title = null;
            summary.coupon_code = null;
            summary.coupon_type = null;
          }
          return summary;
        });

        this.addressDetails = this.invoices?.map((e: any) => e.address_details);

        if (this.branchDetails?.length) {
          this.extractDateAndTime(this.branchDetails[0]);
        }

        const creatorName = response.data.created_by_username || response.data.invoices?.[0]?.created_by_username || '---';
        const closerName = response.data.closed_by_username || response.data.invoices?.[0]?.closed_by_username || '---';

        this.receiptData = {
          branchDetails: Array.isArray(this.branchDetails) ? this.branchDetails : (this.branchDetails ? [this.branchDetails] : []),
          invoices: response.data.invoices,
          order_id: response.data.order_id,
          invoice_summary: this.invoiceSummary || [],
          orderDetails: this.getFilteredOrderDetailsFlat(),
          date: this.date,
          time: this.time,
          showPrices: true,
          paymentStatus: this.paymentStatus,
          invoice_id: response.data.invoices[0]?.id,
          order_type: response.data.invoices[0]?.order_type,
          table_number: this.branchDetails[0]?.table_number,
          transactions: this.invoices[0]?.transactions,
          isFinal: this.isFinal || false,
          cashier: response.data.cashier,
          waiter: response.data.waiter,
          make_type: response.data.make_type
        };
        if (this.receiptData?.invoices?.[0]) {
          this.receiptData.invoices[0].orderDetails = this.getFilteredOrderDetailsFlat();
        }

        // After split, coupon must not apply to primary order; clear it from display if this is the split primary
        this.applyClearCouponForSplitPrimaryOrder();
        // After merge: ensure invoice_summary in memory has no coupon so الفاتورة section hides coupon row
        if (this.invoices?.[0]?.invoice_summary && (this.invoices[0].invoice_summary.coupon_id == null || this.invoices[0].invoice_summary.coupon_id === '')) {
          this.invoices[0].invoice_summary.coupon_value = 0;
          this.invoices[0].invoice_summary.coupon_title = null;
          this.invoices[0].invoice_summary.coupon_code = null;
          this.discountAmount = 0;
          this.couponTitle = '';
          this.couponCode = '';
        }

        // Merged order fix: invoice API may return only primary order items. Fetch full order and use merged items if more.
        const orderId = response.data.order_id;
        const invoiceItemsCount = (this.orderDetails?.flat() || []).length;
        // if (orderId != null && orderId !== '') {
        //   this.orderListDetailsService.getOrderById(String(orderId)).subscribe({
        //     next: (orderRes: any) => {
        //       const order = orderRes?.data?.orderDetails?.[0];
        //       const rawItems = order?.order_details || [];
        //       const orderItems = Array.isArray(rawItems) ? rawItems.filter((it: any) => (Number(it.quantity) || 0) > 0) : [];
        //       if (orderItems.length > invoiceItemsCount && this.invoices?.[0]) {
        //         this.invoices[0].orderDetails = orderItems;
        //         this.orderDetails = this.invoices.map((e: any) => e.orderDetails || []);
        //         const summary = order?.order_summary || this.invoices[0]?.invoice_summary || {};
        //         const subtotal = orderItems.reduce((s: number, it: any) => s + (Number(it.total_dish_price) || 0), 0);
        //         const total = Number(summary.total ?? summary.total_price ?? subtotal);
        //         if (this.invoiceSummary?.[0]) {
        //           this.invoiceSummary[0] = { ...this.invoiceSummary[0], subtotal_price_before_coupon: subtotal, subtotal_price: subtotal, total_price: total, total };
        //         }
        //         if (this.invoices[0].invoice_summary) {
        //           this.invoices[0].invoice_summary = { ...this.invoices[0].invoice_summary, subtotal_price_before_coupon: subtotal, subtotal_price: subtotal, total_price: total, total };
        //         }
        //         this.totalll = total;
        //         this.receiptData = {
        //           ...this.receiptData,
        //           orderDetails: this.getFilteredOrderDetailsFlat(),
        //           invoice_summary: this.invoiceSummary || [],
        //         };
        //         if (this.receiptData?.invoices?.[0]) {
        //           this.receiptData.invoices[0].orderDetails = this.getFilteredOrderDetailsFlat();
        //         }
        //         this.applyClearCouponForSplitPrimaryOrder();
        //         this.cdr.detectChanges();
        //       }
        //     },
        //     error: () => {},
        //   });
        // }
      },
      error: (error: any) => {
        console.error(' Error fetching pill details:', error);
      },
    });
  }
  private safeNum(v: any): number {
    const n = Number(v);
    return v != null && !isNaN(n) ? n : 0;
  }

  /** Whether an order detail item is cancelled (show in different color, exclude from totals). */
  isItemCancelled(item: any): boolean {
    const status = (item?.dish_status ?? item?.status ?? '').toString().toLowerCase();
    return status === 'cancel' || status === 'cancelled';
  }

  /** عناصر الطلب المعروضة: ذات كمية > 0 أو ملغاة (الملغى يظهر بلون مختلف ولا يُحسب). */
  get activeOrderDetails(): any[] {
    const details = this.orderDetails?.[0];
    if (!details || !Array.isArray(details)) return [];
    return details.filter((item: any) => {
      const qty = Number(item.quantity) || 0;
      return qty > 0 || this.isItemCancelled(item);
    });
  }

  /** ملخص الفاتورة للعرض: يستبعد العناصر الملغاة من المجموع والإجمالي (مثل تفاصيل الطلب وتفاصيل الفاتورة). */
  get displayInvoiceSummary(): any {
    const summary = this.invoices?.[0]?.invoice_summary;

    // console.log(summary,'summary_dalia');
    const items = this.activeOrderDetails || [];
    if (!summary) return summary;
    const activeItemsSubtotal = items
      .filter((item: any) => !this.isItemCancelled(item))
      .reduce((sum: number, item: any) => sum + this.safeNum(item.total_dish_price), 0);
    const coupon = this.safeNum(summary.coupon_value);
    const delivery = this.safeNum(summary.delivery_fees);
    const servicePct = this.safeNum(summary.service_percentage);
    let service = this.safeNum(summary.service_fees);
    if (servicePct > 0) service = (activeItemsSubtotal - coupon) * (servicePct / 100);
    const taxPct = this.safeNum(summary.tax_percentage);
    const taxApplication = summary.tax_application ?? false;
    let tax = this.safeNum(summary.tax_value);
    if (taxPct > 0 && !taxApplication) {
      const afterCouponAndService = activeItemsSubtotal - coupon + service;
      tax = afterCouponAndService * (taxPct / 100);
    }
    const total = activeItemsSubtotal - coupon + service + tax + delivery;
    return {
      ...summary,
      subtotal_price_before_coupon: activeItemsSubtotal,
      service_fees: service,
      tax_value: tax,
      total_price: total,
      total: total,
    };
  }

  /** نفس القائمة مصفاة للطباعة: عناصر غير ملغاة وكميتها > 0 فقط. */
  getFilteredOrderDetailsFlat(): any[] {
    return (this.orderDetails?.flat() || []).filter(
      (item: any) => (Number(item.quantity) || 0) > 0 && !this.isItemCancelled(item)
    );
  }
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
  // Helper method to extract date and time from 'created_at'
  // private extractDateAndTime(branch: any): void {
  //   const { created_at } = branch;

  //   if (created_at) {
  //     const [date, time] = created_at.split('T');
  //     this.date = date;
  //     this.time = time?.replace('Z', ''); // Remove 'Z' from the time string
  //   }
  // }
  changePaymentStatus(status: string) {
    this.paymentStatus = status;
    // مسح الأخطاء عند تغيير حالة الدفع
    this.paymentError = '';
    this.paymentAmountError = '';
    this.amountError = false;
    if (status !== 'paid') {
      this.selectedPaymentDeviceId = null;
      this.paymentDeviceError = '';
    } else {
      this.ensureSelectedPaymentDevice();
    }
    console.log(this.paymentStatus);
    this.cdr.detectChanges();
  }

  changeTrackingStatus(status: string) {
    this.trackingStatus = status.trim();
    // مسح رسالة الخطأ عند اختيار حالة التوصيل
    this.deliveryStatusError = '';
    // console.log('تم تحديث حالة التوصيل:', this.trackingStatus);
    if (status == 'delivered') {
      this.show_delivered_only('delivered');
    } else {
      this.show_delivered_only('on_way');

    }

    this.cdr.detectChanges();
  }
  apiErrors: string[] = [];
  DeliveredOrNot: any;
  errr: any;

  saveOrder() {
    console.log('pa', this.paymentStatus);

    // مسح رسائل الخطأ السابقة
    this.referenceNumberError = '';
    this.paymentAmountError = '';
    this.deliveryStatusError = '';
    this.amountError = false;

    // للطلبات غير المدفوعة: مسح أخطاء الدفع حتى لا تمنع حفظ الطلب (مثلاً بعد إضافة كوبون)
    if (this.paymentStatus === 'unpaid') {
      this.paymentError = '';
    }

    // ✅ التحقق من حالة التوصيل للطلبات التوصيل أولاً
    if (this.orderType === 'Delivery') {
      if (!this.trackingStatus || (this.trackingStatus !== 'on_way' && this.trackingStatus !== 'delivered')) {
        this.deliveryStatusError = 'الرجاء تحديد حالة التوصيل';
        this.loading = false;
        return;
      }
    }

    // التحقق من حالة الدفع
    if (!this.paymentStatus) {
      alert('يجب تحديد حالة الدفع  قبل الحفظ!');
      return;
    }
    // تحديد حالة التوصيل المناسبة
    let orderStatusToSend = '';
    if (this.orderType === 'Delivery') {
      if (this.trackingStatus === 'delivered' || this.trackingStatus === 'on_way') {
        orderStatusToSend = this.trackingStatus;
      } else if (this.trackingStatus === 'pending') {
        // يمكن تحويل pending إلى قيمة مقبولة أو تجاهلها
        orderStatusToSend = 'on_way'; // أو أي قيمة افتراضية
      }
    }
    this.amountError = false;

    // 🔒 التحقق من paymentError قبل المتابعة
    if (this.paymentError && this.paymentError.trim() !== '') {
      this.amountError = true;
      this.paymentAmountError = this.paymentError;
      return;
    }

    // ✅ التحقق من رقم المرجع أولاً (قبل التحقق من المبلغ) للدفع المختلط
    if (this.paymentStatus === 'paid' && this.selectedPaymentMethod === 'cash + credit') {
      const enteredCredit = parseFloat(this.creditAmountMixed) || 0;
      if (enteredCredit > 0 && (!this.referenceNumber || !this.referenceNumber.trim())) {
        this.referenceNumberTouched = true;
        this.referenceNumberError = 'رقم المرجع مفقود';
        this.amountError = false;
        this.paymentAmountError = '';
        this.loading = false;
        return;
      }
    }

    // ✅ التحقق من رقم المرجع للفيزا فقط
    if (this.paymentStatus === 'paid' && this.selectedPaymentMethod === 'credit') {
      const enteredCredit = parseFloat(this.creditPaymentInput) || parseFloat(this.creditAmountMixed) || 0;
      if (enteredCredit > 0 && (!this.referenceNumber || !this.referenceNumber.trim())) {
        this.referenceNumberTouched = true;
        this.referenceNumberError = 'رقم المرجع مفقود';
        this.amountError = false;
        this.paymentAmountError = '';
        this.loading = false;
        return;
      }
    }

    if (this.paymentStatus === 'paid' && !this.isPaymentAmountValid()) {
      this.amountError = true;
      return; // 🔒 منع المتابعة إذا كان المبلغ غير صحيح
    }

    if (this.shouldShowPaymentDeviceSelector() && !this.isPaymentDeviceSelectionValid()) {
      this.paymentDeviceError = 'يرجى اختيار ماكينة الدفع.';
      this.loading = false;
      return;
    }

    // التحقق من رقم المرجع للفيزا
    var creditAmount = this.credit_value != null ? this.credit_value : 0;
    var cashAmount = this.cash_value != null ? this.cash_value : 0;

    // ✅ استخدام بيانات الإكرامية إذا كانت موجودة
    if (this.finalTipSummary && this.paymentStatus === 'paid') {
      const total = Number(this.getInvoiceTotal().toFixed(2));

      if (this.finalTipSummary.cashFinal !== undefined) {
        cashAmount = this.finalTipSummary.cashFinal;
        this.cash_value = cashAmount;
      }
      if (this.finalTipSummary.creditFinal !== undefined) {
        creditAmount = this.finalTipSummary.creditFinal;
        this.credit_value = creditAmount;
      }

      // 🔒 التحقق من رقم المرجع للفيزا أولاً (قبل التحقق من المبلغ)
      if (creditAmount > 0 && (!this.referenceNumber || !this.referenceNumber.trim())) {
        this.referenceNumberTouched = true;
        this.referenceNumberError = 'رقم المرجع مفقود';
        this.amountError = false;
        this.paymentAmountError = '';
        this.loading = false;
        return;
      } else {
        this.referenceNumberError = '';
      }

      // 🔒 التحقق من أن المبلغ المدفوع >= الإجمالي عند استخدام finalTipSummary
      const totalPaid = Number((Number(cashAmount || 0) + Number(creditAmount || 0)).toFixed(2));

      // حالة الدفع بالفيزا فقط: التحقق من أن creditFinal >= الإجمالي
      if (this.selectedPaymentMethod === 'credit' && creditAmount > 0 && cashAmount === 0) {
        if (Number(creditAmount) < total) {
          this.amountError = true;
          this.paymentAmountError = `المبلغ المدفوع غير كافي. المطلوب: ${total.toFixed(2)} ${this.invoices[0]?.invoice_summary?.currency_symbol || ''}`;
          this.loading = false;
          return; // ❌ منع الحفظ إذا كان المبلغ غير كافي
        }
      }
      // حالة الدفع المختلط: التحقق من أن مجموع الكاش والفيزا >= الإجمالي
      else if (this.selectedPaymentMethod === 'cash + credit') {
        if (totalPaid < total) {
          this.amountError = true;
          this.paymentAmountError = `المبلغ المدفوع غير كافي. المطلوب: ${total.toFixed(2)} ${this.invoices[0]?.invoice_summary?.currency_symbol || ''}`;
          this.loading = false;
          return; // ❌ منع الحفظ إذا كان المبلغ غير كافي
        }
      }
      // حالة الدفع بالكاش فقط: التحقق من أن cashFinal >= الإجمالي
      else if (this.selectedPaymentMethod === 'cash' && cashAmount > 0 && creditAmount === 0) {
        if (Number(cashAmount) < total) {
          this.amountError = true;
          this.paymentAmountError = `المبلغ المدفوع غير كافي. المطلوب: ${total.toFixed(2)} ${this.invoices[0]?.invoice_summary?.currency_symbol || ''}`;
          this.loading = false;
          return; // ❌ منع الحفظ إذا كان المبلغ غير كافي
        }
      }
    } else if (this.paymentStatus === 'paid') {
      const total = Number(this.getInvoiceTotal().toFixed(2));

        // 🔒 حالة الدفع بالفيزا فقط: التحقق من أن المبلغ المدخل >= الإجمالي
        if (this.selectedPaymentMethod === 'credit' && creditAmount > 0 && cashAmount === 0) {
          const enteredCreditAmount = Number(creditAmount);
          if (enteredCreditAmount < total) {
            this.amountError = true;
            this.paymentAmountError = `المبلغ المدفوع غير كافي. المطلوب: ${total.toFixed(2)} ${this.invoices[0]?.invoice_summary?.currency_symbol || ''}`;
            this.loading = false;
            return; // ❌ منع الحفظ إذا كان المبلغ غير كافي
          } else {
            this.paymentAmountError = '';
            // ✅ استخدام المبلغ المدخل الفعلي (وليس الإجمالي فقط) إذا كان >= الإجمالي
            creditAmount = enteredCreditAmount;
            cashAmount = 0;

            // مزامنة القيم المعروضة
            this.credit_value = creditAmount;
            this.cash_value = cashAmount;
          }
        }
      // حالة الدفع المختلط أو الكاش فقط
      else {
        // ✅ للدفع المختلط: قراءة المبالغ من الحقول المختلطة
        if (this.selectedPaymentMethod === 'cash + credit') {
          const enteredCash = parseFloat(this.cashAmountMixed) || 0;
          const enteredCredit = parseFloat(this.creditAmountMixed) || 0;
          const totalEntered = Number((enteredCash + enteredCredit).toFixed(2));

          // التحقق من رقم المرجع للفيزا أولاً
          if (enteredCredit > 0 && (!this.referenceNumber || !this.referenceNumber.trim())) {
            this.referenceNumberTouched = true;
            this.referenceNumberError = 'رقم المرجع مفقود';
            this.amountError = false;
            this.paymentAmountError = '';
            this.loading = false;
            return;
          } else {
            this.referenceNumberError = '';
          }

          // التحقق من أن المبلغ المدخل >= الإجمالي (مع مراعاة دقة الأرقام العشرية)
          const tolerance = 0.01; // تسامح 0.01 للتعامل مع أخطاء التقريب
          if (totalEntered < (total - tolerance)) {
            this.amountError = true;
            this.paymentAmountError = `المبلغ المدفوع غير كافي. المطلوب: ${total.toFixed(2)} ${this.invoices[0]?.invoice_summary?.currency_symbol || ''}`;
            this.loading = false;
            return;
          }

          // ✅ مسح الأخطاء إذا كان المبلغ كافياً
          this.amountError = false;
          this.paymentAmountError = '';

          // ✅ استخدام المبالغ المدخلة كما هي (لا إعادة حساب)
          cashAmount = enteredCash;
          creditAmount = enteredCredit;

          // مزامنة القيم المعروضة
          this.cash_value = cashAmount;
          this.credit_value = creditAmount;
        } else {
          // حالة الكاش فقط: التحقق من رقم المرجع للفيزا أولاً قبل تعديل المبالغ
          const currentCreditAmount = this.creditAmountMixed ? parseFloat(this.creditAmountMixed) || 0 : creditAmount;
          if (currentCreditAmount > 0 && (!this.referenceNumber || !this.referenceNumber.trim())) {
            this.referenceNumberTouched = true;
            this.referenceNumberError = 'رقم المرجع مفقود';
            this.amountError = false;
            this.paymentAmountError = '';
            this.loading = false;
            return;
          } else {
            this.referenceNumberError = '';
          }

          // للكاش فقط: استخدام المبلغ المدخل
          if (this.selectedPaymentMethod === 'cash') {
            const enteredCashAmount = parseFloat(this.cashPaymentInput) || 0;
            if (enteredCashAmount >= total) {
              cashAmount = enteredCashAmount;
              creditAmount = 0;
              this.cash_value = cashAmount;
              this.credit_value = creditAmount;
            } else {
              this.amountError = true;
              this.paymentAmountError = `المبلغ المدفوع غير كافي. المطلوب: ${total.toFixed(2)} ${this.invoices[0]?.invoice_summary?.currency_symbol || ''}`;
              this.loading = false;
              return;
            }
          }
        }
      }

        // التحقق من رقم المرجع للفيزا بعد التأكد من المبلغ (للحالة credit فقط)
        if (this.selectedPaymentMethod === 'credit' && creditAmount > 0 && (!this.referenceNumber || !this.referenceNumber.trim())) {
          this.referenceNumberTouched = true;
          this.referenceNumberError = 'رقم المرجع مفقود';
          this.amountError = false;
          this.paymentAmountError = '';
          this.loading = false;
          return;
        } else if (creditAmount > 0) {
          this.referenceNumberError = '';
        }
    }
    if (this.orderType == 'Delivery') {
      this.DeliveredOrNot = true;
    } else {
      this.DeliveredOrNot = false;
    }
    // التأكد من أن totalll محدث بشكل صحيح
    const finalTotal = this.totalll || this.getInvoiceTotal();

    // ✅ في حالة كوبون 100% على الطلب جعل إجمالي الفاتورة = 0 نعتبر الفاتورة "مدفوعة" تلقائياً
    const isFullOrderCoupon =
      this.appliedCoupon &&
      (this.appliedCoupon.value_type === 'percentage' ||
        this.appliedCoupon.value_type === 'Percentage' ||
        this.appliedCoupon.value_type === 'percent') &&
      Number(this.appliedCoupon.coupon_value) === 100 &&
      this.appliedCoupon.coupon_apply_type === 'order' &&
      finalTotal <= 0;

    if (isFullOrderCoupon && this.paymentStatus !== 'paid') {
      this.paymentStatus = 'paid';
      cashAmount = 0;
      creditAmount = 0;
    }

    console.log('💰 Payment amounts before save:', {
      cashAmount,
      creditAmount,
      totalll: this.totalll,
      getInvoiceTotal: this.getInvoiceTotal(),
      finalTotal: finalTotal,
      paymentStatus: this.paymentStatus,
      invoiceSummary: this.invoices?.[0]?.invoice_summary
    });

    // 🔒 التحقق النهائي من جميع الأخطاء قبل المتابعة
    if (this.amountError || this.paymentError || this.paymentAmountError || this.referenceNumberError) {
      return; // منع المتابعة إذا كان هناك أي خطأ
    }

    if (this.amountError == false && this.loading == false) {
      this.loading = true

      // إعداد بيانات الكوبون إذا كان موجوداً
      const couponData = (this.discountAmount > 0 || this.couponCode) ? {
        coupon_code: this.couponCode || this.invoices?.[0]?.invoice_summary?.coupon_code || '',
        coupon_value: this.discountAmount || this.invoices?.[0]?.invoice_summary?.coupon_value || 0,
        coupon_type: this.couponType || this.invoices?.[0]?.invoice_summary?.coupon_type || '',
        coupon_title: this.couponTitle || this.invoices?.[0]?.invoice_summary?.coupon_title || '',
        delivery_fees: Number(
          this.invoices?.[0]?.invoice_summary?._original_delivery_fees ??
          this.invoices?.[0]?.invoice_summary?.delivery_fees ??
          0
        )
      } : undefined;

      // ✅ إعداد بيانات الإكرامية إذا كانت موجودة
      const tipData = this.finalTipSummary ? {
        tip_amount: this.finalTipSummary.tipAmount || 0,
        returned_amount: this.finalTipSummary.changeToReturn || 0,
        total_with_tip: this.finalTipSummary.grandTotalWithTip || finalTotal,
        payment_amount: this.finalTipSummary.paymentAmount || (cashAmount + creditAmount),
        bill_amount: this.finalTipSummary.billAmount || finalTotal ,
        tips_aption : this.selectedTipType,
        tip_specific_amount: this.specificTipAmount ? this.finalTipSummary?.tipAmount : 0,
        change_amount: this.tempChangeAmount || 0,
      } : undefined;


      if (this.paymentStatus == 'paid') {
        if (cashAmount > 0) {
          // Get existing paid_order value from localStorage
          const existingPaidOrderStrCash = localStorage.getItem('paid_order_cash');
          // Helper function to parse value (handles both JSON and plain string)
          const parseValueCash = (value: string | null): number => {
            if (!value) return 0;
            try {
              const parsedCash = JSON.parse(value);
              return parseFloat(parsedCash) || 0;
            } catch {
              return parseFloat(value) || 0;
            }
          };

          // Get existing value and add new bill_amount
          const existingPaidOrderCash = parseValueCash(existingPaidOrderStrCash);
          const newTotalCash = existingPaidOrderCash + (cashAmount || 0);

          // Store the accumulated total
          localStorage.setItem('paid_order_cash', JSON.stringify(newTotalCash));
        }
        if (creditAmount > 0) {
          // Get existing paid_order value from localStorage
          const existingPaidOrderStrCredit = localStorage.getItem('paid_order_credit');

          // Helper function to parse value (handles both JSON and plain string)
          const parseValueCredit = (value: string | null): number => {
            if (!value) return 0;
            try {
              const parsedCredit = JSON.parse(value);
              return parseFloat(parsedCredit) || 0;
            } catch {
              return parseFloat(value) || 0;
            }
          };

          // Get existing value and add new bill_amount
          const existingPaidOrderCredit = parseValueCredit(existingPaidOrderStrCredit);
          const newTotalCredit = existingPaidOrderCredit + (creditAmount || 0);

          // Store the accumulated total
          localStorage.setItem('paid_order_credit', JSON.stringify(newTotalCredit));
        }
      }
      if (this.orderType == 'talabat' && this.paymentStatus == 'unpaid') {
        this.paymentStatus = 'paid';
        cashAmount = finalTotal;
        creditAmount = 0;
      }

      if (!this.orderNumber) {
        console.error('Missing orderNumber, aborting invoice update to avoid invalid URL');
        this.loading = false;
        return;
      }

      this.orderService
        .updateInvoiceStatus(
          String(this.orderNumber),
          this.paymentStatus,
          this.trackingStatus,
          cashAmount,
          creditAmount,
          this.DeliveredOrNot,
          finalTotal, // استخدام finalTotal بدلاً من totalll || getInvoiceTotal
          tipData, // ✅ إرسال بيانات الإكرامية
          this.referenceNumber,
          couponData, // إرسال بيانات الكوبون
          this.effectivePaymentDeviceIdForOrder() ?? undefined
        ).pipe(finalize(() => this.loading = false))
        .subscribe({
          next: async (response) => {
            // 🔒 التحقق من حالة الاستجابة قبل المتابعة
            if (response.status === false && response.message) {
              this.errr = response.message;
              this.loading = false;
              return; // ❌ منع المتابعة إذا كان هناك خطأ
            }

            if (response.status === false || response.errorData) {
              // Handle validation or logical API errors
              this.apiErrors = Object.values(
                response.errorData as { [key: string]: string[] }
              ).flat();
              this.loading = false;
              return; // ❌ Do not continue
            }

            // 🔒 التحقق النهائي: التأكد من أن البيانات تم حفظها فعلياً
            if (!response.data) {
              this.apiErrors = ['فشل حفظ التعديلات. يرجى المحاولة مرة أخرى.'];
              this.loading = false;
              return; // ❌ منع إظهار رسالة النجاح إذا لم يتم الحفظ
            }

            this.markSelectedPaymentDeviceAsLastUsed();

            // ✅ Success - فقط إذا تم الحفظ بنجاح
            this.apiErrors = [];
            localStorage.removeItem('cash_value')
            localStorage.removeItem('credit_value')
            localStorage.setItem(
              'pill_detail_data',
              JSON.stringify(response.data)
            );
            // this.showSuccessPillEditModal();
            console.log(response, 'response');

            // تحضير البيانات من استجابة API
            const branchDetails = response.data.branch_details
              ? [response.data.branch_details]
              : [];

            // إنشاء invoices array لأن مكون الإيصال يحتاجها
            // نستخدم response.data مباشرة لأنه يحتوي الآن على جميع الحقول المطلوبة (orderDetails, address_details, etc.)
            const invoices = [{
              ...response.data,
              // التأكد من وجود الحقول الأساسية
              orderDetails: response.data.orderDetails || [],
              transactions: response.data.transactions || []
            }];

            // تحويل invoice_summary إلى مصفوفة
            const invoiceSummary = response.data.invoice_summary
              ? [{
                  ...response.data.invoice_summary,
                  currency_symbol: response.data.currency_symbol || ''
                }]
              : [];

            const filteredOrderDetails = (response.data.orderDetails || []).filter((item: any) => (Number(item.quantity) || 0) > 0);
            this.receiptData = {
              branchDetails: branchDetails,
              invoices: invoices,
              order_id: response.data.order.id,
              invoice_summary: invoiceSummary,
              orderDetails: filteredOrderDetails,
              date: response.data.order.date,
              time: response.data.order.time,
              showPrices: true,
              paymentStatus: response.data.order.status,
              invoice_id: response.data.order.order_transactions[0]?.invoice_id,
              order_type: response.data.order_type,
              table_number: response.data.order.table_id || null,
              transactions: response.data.transactions || [],
              isFinal: true,
              cashier: response.data.cashier,
              waiter: response.data.waiter,
              make_type: response.data.make_type
            };
            if (this.receiptData?.invoices?.[0]) {
              this.receiptData.invoices[0].orderDetails = filteredOrderDetails;
            }

            // التأكد من ظهور "مدفوع" في طباعة الفاتورة بعد الدفع
            if (this.paymentStatus === 'paid' && this.receiptData?.invoices?.[0]) {
              if (!this.receiptData.invoices[0].transactions?.length) {
                this.receiptData.invoices[0].transactions = [{ payment_status: 'paid', payment_method: this.selectedPaymentMethod || 'cash', paid: finalTotal }];
              } else {
                this.receiptData.invoices[0].transactions.forEach((t: any) => { t.payment_status = 'paid'; });
              }
            }

            // انتظار قليل لشحن البيانات في المكون
            this.cdr.detectChanges();
            await new Promise((resolve) => setTimeout(resolve, 500));

            // استدعاء وظيفة الطباعة (ستتعامل مع الطباعة الصامتة إذا كان في Electron)
            await this.printInvoice(true);
          },
          error: (err) => {
            console.error('خطأ في حفظ الطلب:', err);
            this.apiErrors = ['حدث خطأ أثناء الاتصال بالخادم.'];
          },
        });
    }
  }

  // ===== Coupon / Discount handling for existing unpaid orders =====
  private buildOrderItemsForCoupon(): any[] {
    const items = this.invoices?.[0]?.orderDetails || this.orderDetails?.[0] || [];
    return (items || []).map((item: any) => {
      const dishId = item.dish_id || item.dish?.id || item.id;
      const quantity = item.quantity || item.qty || 1;
      const sizeId = item.size_id || item.sizeId || item.selectedSize?.id;
      const dish: any = { dish_id: dishId, quantity };
      if (sizeId) dish.size_id = sizeId;
      return dish;
    }).filter((d: any) => d.dish_id);
  }

  private recalcTotalsWithDiscount(discount: number, title: string, type: 'percentage' | 'fixed' | ''): void {
    const summary = this.invoices?.[0]?.invoice_summary;
    if (!summary) return;

    // According to User Story 17: Fixed calculation order
    // Step 1: Get Product Value (BEFORE discount)
    // ✅ حفظ القيمة الأصلية قبل الخصم (إذا لم تكن محفوظة مسبقاً)
    if (!summary._original_subtotal_price_before_coupon) {
      summary._original_subtotal_price_before_coupon = Number(summary.subtotal_price_before_coupon ?? summary.total_price ?? 0);
    }
    const productValueBeforeDiscount = Number(summary._original_subtotal_price_before_coupon);

    // ✅ حفظ القيم الأصلية للخدمة والضريبة
    if (!summary._original_service_fees) {
      summary._original_service_fees = Number(summary.service_fees || 0);
    }

    const servicePerc = Number(summary.service_percentage || 0);
    const taxPerc = Number(summary.tax_percentage || 0);
    const taxApplication = summary.tax_application ?? false;
    if (summary._original_delivery_fees === undefined || summary._original_delivery_fees === null) {
      summary._original_delivery_fees = Number(summary.delivery_fees || 0);
    }
    const originalDeliveryFees = Number(summary._original_delivery_fees || 0);

    // Step 2: Apply Discount/Coupon
    const discountValue = Math.min(discount, productValueBeforeDiscount);
    const productValueAfterDiscount = Math.max(0, productValueBeforeDiscount - discountValue);

    const isFullCouponDiscount =
      type === 'percentage' &&
      productValueBeforeDiscount > 0 &&
      discountValue >= productValueBeforeDiscount;
    const deliveryFees = isFullCouponDiscount ? 0 : originalDeliveryFees;

    // Step 3: Calculate Service Charge (on product value AFTER discount)
    let serviceAmount = 0;
    // ✅ إذا كان الكوبون 100% خصم (قيمة المنتجات = 0)، يجب أن تكون رسوم الخدمة = 0
    if (productValueAfterDiscount > 0) {
      if (servicePerc > 0) {
        serviceAmount = (productValueAfterDiscount * servicePerc) / 100;
      } else {
        // ✅ للخدمة الثابتة: حساب نسبتها من القيمة الأصلية وتطبيقها على القيمة الجديدة
        const originalServiceFees = Number(summary._original_service_fees || 0);
        if (originalServiceFees > 0 && productValueBeforeDiscount > 0) {
          const serviceRatio = productValueAfterDiscount / productValueBeforeDiscount;
          serviceAmount = originalServiceFees * serviceRatio;
        }
      }
    }
    serviceAmount = Number(serviceAmount.toFixed(2));

    // Step 4: Calculate VAT (on Product Value AFTER Discount + Service Charge)
    // VAT Base = Product Value After Discount + Service Charge
    const vatBase = productValueAfterDiscount + serviceAmount;

    let taxAmount = 0;
    // ✅ إذا كان الكوبون 100% خصم (vatBase = 0)، يجب أن تكون الضريبة = 0
    if (vatBase > 0 && taxPerc > 0) {
      if (taxApplication) {
        // Tax included in price: extract tax from total
        taxAmount = vatBase - vatBase / (1 + taxPerc / 100);
      } else {
        // Tax added to price: calculate tax on base
        taxAmount = (vatBase * taxPerc) / 100;
      }
    }
    taxAmount = Number(taxAmount.toFixed(3));

    // Step 5: Calculate Final Total
    // Final Total = Product Value After Discount + Service Charge + VAT + Delivery Fee
    const finalTotal = productValueAfterDiscount + serviceAmount + taxAmount + deliveryFees;

    // تحديث بيانات الفاتورة الأصلية (invoices[0].invoice_summary)
    const ac = this.appliedCoupon;
    const apiCouponId = ac && (ac.coupon_id ?? ac.id);
    if (apiCouponId != null && apiCouponId !== '') {
      summary.coupon_id = apiCouponId;
    }
    summary.coupon_value = discountValue;
    summary.coupon_title = title;
    summary.coupon_type = type;
    summary.coupon_code = this.couponCode || title;
    summary.subtotal_price_before_coupon = productValueBeforeDiscount;
    summary.total_price = Number(finalTotal.toFixed(2));
    summary.total_after_tax = Number(finalTotal.toFixed(2));
    summary.tax_value = Number(taxAmount.toFixed(3));
    summary.tax = Number(taxAmount.toFixed(3));
    summary.service_fees = serviceAmount;
    summary.delivery_fees = deliveryFees;

    // ✅ تحديث invoiceSummary أيضاً (المستخدم في العرض)
    if (this.invoiceSummary && this.invoiceSummary[0]) {
      if (apiCouponId != null && apiCouponId !== '') {
        this.invoiceSummary[0].coupon_id = apiCouponId;
      }
      this.invoiceSummary[0].coupon_value = discountValue;
      this.invoiceSummary[0].coupon_title = title;
      this.invoiceSummary[0].coupon_type = type;
      this.invoiceSummary[0].coupon_code = this.couponCode || title;
      this.invoiceSummary[0].subtotal_price_before_coupon = productValueBeforeDiscount;
      this.invoiceSummary[0].total_price = Number(finalTotal.toFixed(2));
      this.invoiceSummary[0].total_after_tax = Number(finalTotal.toFixed(2));
      this.invoiceSummary[0].tax_value = Number(taxAmount.toFixed(3));
      this.invoiceSummary[0].tax = Number(taxAmount.toFixed(3));
      this.invoiceSummary[0].service_fees = serviceAmount;
      this.invoiceSummary[0].delivery_fees = deliveryFees;
    }

    this.discountAmount = discountValue;
    this.couponTitle = title;
    this.couponType = type;
    this.totalll = summary.total_price;
    this.cdr.detectChanges();

    console.log('✅ Discount applied - Updated totals (User Story 17):', {
      productValueBeforeDiscount,
      discountValue,
      productValueAfterDiscount,
      serviceAmount,
      vatBase,
      taxAmount,
      deliveryFees,
      finalTotal,
      totalll: this.totalll
    });
  }

  applyCouponForInvoice(): void {
    if (this.paymentStatus === 'paid' || this.invoices?.[0]?.transactions?.[0]?.payment_status === 'paid') {
      this.couponError = 'لا يمكن إضافة خصم لطلب مدفوع.';
      return;
    }
    if (!this.couponCode || !this.couponCode.trim()) {
      this.couponError = 'يرجى إدخال كود الكوبون.';
      return;
    }
    this.couponError = '';
    this.couponMessage = '';
    this.isApplyingCoupon = true;

    const token = localStorage.getItem('authToken');
    const headers = new HttpHeaders({
      Authorization: `Bearer ${token || ''}`,
      'Content-Type': 'application/json',
    });
    const branchId = localStorage.getItem('branch_id');
    const summary = this.invoices?.[0]?.invoice_summary;
    const baseAmount = Number(summary?.subtotal_price_before_coupon ?? summary?.total_price ?? 0);
    const requestData = {
      code: this.couponCode.trim(),
      amount: baseAmount,
      branch_id: branchId,
      dishes: this.buildOrderItemsForCoupon()
    };

    this.http.post(`${baseUrl}api/coupons/check-coupon`, requestData, { headers })
      .pipe(finalize(() => { this.isApplyingCoupon = false; }))
      .subscribe({
        next: (res: any) => {
          if (!res?.status) {
            this.couponError = res?.errorData?.error || 'Invalid or expired coupon.';
            this.appliedCoupon = null;
            this.discountAmount = 0;
            this.couponMessage = '';
            // لا نغلق المودال عند الخطأ
            return;
          }

          this.appliedCoupon = res.data;
          this.discountAmount = res.data.total_discount || 0;
          this.couponTitle = res.data.coupon_title || this.couponCode;
          this.couponType = res.data.value_type || '';

          // التأكد من أن هناك خصم فعلي قبل المتابعة
          if (this.discountAmount <= 0) {
            this.couponError = 'الكوبون لا يحتوي على خصم صالح.';
            this.appliedCoupon = null;
            this.discountAmount = 0;
            this.couponMessage = '';
            return;
          }

          // مسح أي أخطاء سابقة
          this.couponError = '';

          // حساب المبلغ الجديد مع الضريبة والرسوم
          this.recalcTotalsWithDiscount(
            this.discountAmount,
            this.couponTitle,
            this.couponType
          );

          this.couponMessage = `تم تطبيق الكوبون: -${this.discountAmount.toFixed(2)} ${res.data.currency_symbol || ''}`;

          console.log('✅ Coupon applied successfully:', {
            discountAmount: this.discountAmount,
            oldTotal: baseAmount,
            newTotal: this.totalll,
            couponTitle: this.couponTitle,
            couponType: this.couponType
          });

          // إغلاق المودال تلقائياً بعد تطبيق الكوبون بنجاح فقط
          setTimeout(() => {
            this.closeCouponModal();
          }, 300); // تأخير بسيط لضمان عرض رسالة النجاح
        },
        error: (err) => {
          this.couponError = err?.error?.errorData?.error || 'Cannot apply coupon. Please check coupon conditions or order eligibility.';
        }
      });
  }

  applyManualDiscount(): void {
    if (this.paymentStatus === 'paid' || this.invoices?.[0]?.transactions?.[0]?.payment_status === 'paid') {
      this.couponError = 'لا يمكن إضافة خصم لطلب مدفوع.';
      return;
    }
    if (!this.manualDiscountType || this.manualDiscountValue === null) {
      this.couponError = 'حدد نوع وقيمة الخصم.';
      return;
    }
    const summary = this.invoices?.[0]?.invoice_summary;
    const baseAmount = Number(summary?.subtotal_price_before_coupon ?? summary?.total_price ?? 0);
    if (!baseAmount) {
      this.couponError = 'فشل حساب إجمالي الطلب.';
      return;
    }

    let discountValue = 0;
    if (this.manualDiscountType === 'percentage') {
      discountValue = (baseAmount * Number(this.manualDiscountValue)) / 100;
    } else {
      discountValue = Number(this.manualDiscountValue);
    }
    discountValue = Math.max(0, Math.min(discountValue, baseAmount));
    this.couponCode = '';
    this.appliedCoupon = null;
    this.couponMessage = `تم تطبيق خصم يدوي (${this.manualDiscountType === 'percentage' ? '%' : 'مبلغ ثابت'})`;
    this.couponError = '';
    this.recalcTotalsWithDiscount(discountValue, 'خصم يدوي', this.manualDiscountType);
  }

  /**
   * When order was split, coupon must not apply to primary order. If this invoice is for that primary order,
   * zero the coupon in summary and recalc total, then remove from session list.
   */
  private applyClearCouponForSplitPrimaryOrder(): void {
    const orderId = this.order_id != null ? String(this.order_id) : '';
    if (!orderId) return;
    try {
      const raw = sessionStorage.getItem('splitPrimaryOrderIds') || '[]';
      const ids: string[] = JSON.parse(raw);
      if (!ids.includes(orderId)) return;
      ids.splice(ids.indexOf(orderId), 1);
      sessionStorage.setItem('splitPrimaryOrderIds', JSON.stringify(ids));
    } catch (_) {
      return;
    }
    const summary = this.invoices?.[0]?.invoice_summary;
    if (!summary) return;
    const hasCoupon = (this.discountAmount > 0) || (Number(summary.coupon_value) || 0) > 0;
    if (!hasCoupon) return;

    const originalSubtotal = Number(summary._original_subtotal_price_before_coupon || summary.subtotal_price_before_coupon || summary.total_price || 0);
    const originalServiceFees = Number(summary._original_service_fees || summary.service_fees || 0);
    const taxPerc = Number(summary.tax_percentage || 0);
    const servicePerc = Number(summary.service_percentage || 0);
    const taxApplication = summary.tax_application ?? false;
    const deliveryFees = Number(summary.delivery_fees || 0);

    let serviceAmount = 0;
    if (servicePerc > 0) {
      serviceAmount = (originalSubtotal * servicePerc) / 100;
    } else {
      serviceAmount = originalServiceFees;
    }
    serviceAmount = Number(serviceAmount.toFixed(2));

    const vatBase = originalSubtotal + serviceAmount;
    let taxAmount = 0;
    if (taxPerc > 0) {
      if (taxApplication) {
        taxAmount = vatBase - vatBase / (1 + taxPerc / 100);
      } else {
        taxAmount = (vatBase * taxPerc) / 100;
      }
    }
    taxAmount = Number(taxAmount.toFixed(3));

    const finalTotal = originalSubtotal + serviceAmount + taxAmount + deliveryFees;

    summary.coupon_value = 0;
    summary.coupon_title = '';
    summary.coupon_type = '';
    summary.coupon_code = '';
    summary.subtotal_price_before_coupon = originalSubtotal;
    summary.total_price = Number(finalTotal.toFixed(2));
    summary.total_after_tax = Number(finalTotal.toFixed(2));
    summary.tax_value = Number(taxAmount.toFixed(3));
    summary.tax = Number(taxAmount.toFixed(3));
    summary.service_fees = serviceAmount;

    if (this.invoiceSummary && this.invoiceSummary[0]) {
      this.invoiceSummary[0].coupon_value = 0;
      this.invoiceSummary[0].coupon_title = '';
      this.invoiceSummary[0].coupon_type = '';
      this.invoiceSummary[0].coupon_code = '';
      this.invoiceSummary[0].subtotal_price_before_coupon = originalSubtotal;
      this.invoiceSummary[0].total_price = Number(finalTotal.toFixed(2));
      this.invoiceSummary[0].total_after_tax = Number(finalTotal.toFixed(2));
      this.invoiceSummary[0].tax_value = Number(taxAmount.toFixed(3));
      this.invoiceSummary[0].tax = Number(taxAmount.toFixed(3));
      this.invoiceSummary[0].service_fees = serviceAmount;
    }

    this.discountAmount = 0;
    this.couponTitle = '';
    this.couponType = '';
    this.couponCode = '';
    this.couponMessage = '';
    this.couponError = '';
    this.appliedCoupon = null;
    this.totalll = summary.total_price;
  }

  removeDiscount(): void {
    const summary = this.invoices?.[0]?.invoice_summary;
    if (!summary) return;

    // التحقق من وجود كوبون مطبق (من البيانات الأصلية أو المطبق حديثاً)
    const hasCoupon = (this.discountAmount > 0) ||
                      (summary.coupon_value && summary.coupon_value > 0);
    if (!hasCoupon) return;

    // ✅ استخدام القيم الأصلية المحفوظة إذا كانت موجودة
    const originalSubtotal = Number(summary._original_subtotal_price_before_coupon || summary.subtotal_price_before_coupon || summary.total_price || 0);
    const originalServiceFees = Number(summary._original_service_fees || summary.service_fees || 0);
    const taxPerc = Number(summary.tax_percentage || 0);
    const servicePerc = Number(summary.service_percentage || 0);
    const taxApplication = summary.tax_application ?? false;
    const deliveryFees = Number(summary.delivery_fees || 0);

    // إعادة الحساب من الصفر
    let subtotalAfter = originalSubtotal;

    // حساب رسوم الخدمة (إعادتها للقيمة الأصلية)
    let serviceAmount = 0;
    if (servicePerc > 0) {
      serviceAmount = (subtotalAfter * servicePerc) / 100;
    } else {
      serviceAmount = originalServiceFees;
    }
    serviceAmount = Number(serviceAmount.toFixed(2));

    // حساب الضريبة
    const vatBase = subtotalAfter + serviceAmount;
    let taxAmount = 0;
    if (taxPerc > 0) {
      if (taxApplication) {
        taxAmount = vatBase - vatBase / (1 + taxPerc / 100);
      } else {
        taxAmount = (vatBase * taxPerc) / 100;
      }
    }
    taxAmount = Number(taxAmount.toFixed(3));

    // الحساب النهائي (يشمل delivery_fees)
    const finalTotal = subtotalAfter + serviceAmount + taxAmount + deliveryFees;

    // تحديث بيانات الفاتورة الأصلية
    summary.coupon_value = 0;
    summary.coupon_title = '';
    summary.coupon_type = '';
    summary.coupon_code = '';
    summary.subtotal_price_before_coupon = originalSubtotal;
    summary.total_price = Number(finalTotal.toFixed(2));
    summary.total_after_tax = Number(finalTotal.toFixed(2));
    summary.tax_value = Number(taxAmount.toFixed(3));
    summary.tax = Number(taxAmount.toFixed(3));
    summary.service_fees = serviceAmount;

    // ✅ تحديث invoiceSummary أيضاً (المستخدم في العرض)
    if (this.invoiceSummary && this.invoiceSummary[0]) {
      this.invoiceSummary[0].coupon_value = 0;
      this.invoiceSummary[0].coupon_title = '';
      this.invoiceSummary[0].coupon_type = '';
      this.invoiceSummary[0].coupon_code = '';
      this.invoiceSummary[0].subtotal_price_before_coupon = originalSubtotal;
      this.invoiceSummary[0].total_price = Number(finalTotal.toFixed(2));
      this.invoiceSummary[0].total_after_tax = Number(finalTotal.toFixed(2));
      this.invoiceSummary[0].tax_value = Number(taxAmount.toFixed(3));
      this.invoiceSummary[0].tax = Number(taxAmount.toFixed(3));
      this.invoiceSummary[0].service_fees = serviceAmount;
    }

    // ✅ مسح القيم الأصلية المحفوظة
    delete summary._original_subtotal_price_before_coupon;
    delete summary._original_service_fees;

    this.discountAmount = 0;
    this.couponTitle = '';
    this.couponType = '';
    this.couponCode = '';
    this.couponMessage = '';
    this.couponError = '';
    this.appliedCoupon = null;
    this.totalll = summary.total_price;
    this.cdr.detectChanges();

    console.log('✅ Discount removed - Reset to original:', {
      originalSubtotal,
      serviceAmount,
      taxAmount,
      deliveryFees,
      finalTotal,
      totalll: this.totalll
    });

    // إغلاق المودال بعد حذف الكوبون (فقط إذا كان مفتوحاً)
    const modalElement = document.getElementById('couponModal');
    if (modalElement) {
      const modal = bootstrap.Modal.getInstance(modalElement);
      if (modal && modal._isShown) {
        setTimeout(() => {
          this.closeCouponModal();
        }, 300);
      }
    }
  }
  isFinal: boolean = false;
  async printInvoice(isFinal: boolean) {
    this.isFinal = isFinal;

    if (this.receiptData) {
      this.receiptData.isFinal = isFinal;
    }

    if (!this.invoices?.length || !this.invoiceSummary?.length) {
      console.warn('Invoice data not ready.');
      return;
    }

    try {
      if ((window as any).deviceAPI) {
        console.log('Detected Electron environment. Attempting silent print via SilentPrintService.');

        const printerIP = this.invoices[0]?.branch_details?.printer_ip || "192.168.11.187";
        const port = this.invoices[0]?.branch_details?.printer_port || 9100;

        const result = await this.silentPrint.printElement('printSection', printerIP, port);

        if (result.success) {
          console.log("Silent print successful");
          location.reload();
        } else {
          console.error("Silent print failed:", result);
          alert(`فشلت الطباعة الصامتة: ${result.message || 'خطأ غير معروف'}`);
        }

        return;
      }

      const printContent = document.getElementById('printSection');
      if (!printContent) {
        console.error('Print section not found.');
        return;
      }

      const originalHTML = document.body.innerHTML;

      const copies = [
        { showPrices: true, test: true },
      ];

      for (let i = 0; i < copies.length; i++) {
        this.showPrices = copies[i].showPrices;
        this.test = copies[i].test;
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

      // انتظار قليل قبل إعادة التحميل للتأكد من اكتمال الطباعة
      await new Promise((resolve) => setTimeout(resolve, 500));
      location.reload();
    } catch (error) {
      console.error('Error printing invoice:', error);
    }
  }

  getDiscountAmount(): number {
    if (
      !this.couponType ||
      !this.invoices ||
      !this.invoices[0]?.invoice_summary
    ) {
      return 0;
    }

    const invoiceSummary = this.invoices[0].invoice_summary;
    const couponValue = parseFloat(invoiceSummary.coupon_value);

    if (isNaN(couponValue)) {
      return 0;
    }

    if (this.couponType === 'percentage') {
      return (invoiceSummary.subtotal_price * couponValue) / 100;
    } else if (this.couponType === 'fixed') {
      return couponValue;
    }

    return 0;
  }

  hastakeaway(): boolean {
    return this.invoices?.some(
      (invoice: { order_type: string }) => invoice.order_type === 'Takeaway'
    );
  }
  getFinalPrice(): number {
    return (
      this.invoices[0].invoice_summary.subtotal_price - this.getDiscountAmount()
    );
  }
  getTrackingStatusTranslation(status: string): string {
    const statusTranslations: { [key: string]: string } = {
      completed: 'مكتمل',
      pending: 'في انتظار الموافقة',
      cancelled: 'ملغي',
      packing: 'يتم تجهيزها',
      readyForPickup: 'جاهز للاستلام',
      on_way: 'في الطريق',
      in_progress: 'يتم تحضير الطلب',
      delivered: 'تم التوصيل',
    };

    return statusTranslations[status] || status;
  }

  showSuccessPillEditModal() {
    const modalElement = document.getElementById('successPillEdit');
    if (!modalElement) return;

    const modal = new bootstrap.Modal(modalElement);
    modal.show();

    setTimeout(() => {
      modal.hide();
    }, 1000); // بعد 1 ثانية (1000 ميلي ثانية)
  }

  closeCouponModal() {
    const modalElement = document.getElementById('couponModal');
    if (!modalElement) return;

    // الحصول على instance المودال أو إنشاء واحد جديد
    let modal = bootstrap.Modal.getInstance(modalElement);
    if (!modal) {
      modal = new bootstrap.Modal(modalElement);
    }

    // دالة التنظيف
    const cleanup = () => {
      // إزالة جميع الـ backdrops المتبقية
      const backdrops = document.querySelectorAll('.modal-backdrop');
      backdrops.forEach(backdrop => backdrop.remove());

      // تنظيف body
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
      document.body.style.paddingRight = '';
    };

    // إزالة الـ backdrop والتنظيف بعد إغلاق المودال
    modalElement.addEventListener('hidden.bs.modal', cleanup, { once: true });

    // إغلاق المودال
    modal.hide();

    // تنظيف فوري أيضاً في حالة عدم تشغيل الـ event
    setTimeout(cleanup, 300);
  }
  getInvoiceTotal(): number {
    // بعد تطبيق الكوبون نستخدم totalll؛ وإلا ملخص العرض (يستبعد الملغى) ثم invoice_summary
    if (this.totalll != null && this.totalll > 0) return this.totalll;
    const display = this.displayInvoiceSummary;
    if (display?.total_price != null && !isNaN(Number(display.total_price))) {
      return Number(display.total_price);
    }
    return this.invoices?.[0]?.invoice_summary?.total_price || 0;
  }

  isPaymentAmountValid(): boolean {
    if (this.paymentStatus !== 'paid') return true;

    const total = this.getInvoiceTotal();
    let cash = 0;
    let credit = 0;

    // ✅ للدفع المختلط: قراءة من الحقول المختلطة
    if (this.selectedPaymentMethod === 'cash + credit') {
      cash = parseFloat(this.cashAmountMixed) || 0;
      credit = parseFloat(this.creditAmountMixed) || 0;
    } else {
      // للكاش أو الفيزا: قراءة من القيم العادية
      cash = Number(this.cash_value ?? 0);
      credit = Number(this.credit_value ?? 0);

      // إذا كانت القيم 0، جرب قراءة من الحقول المدخلة
      if (cash === 0 && credit === 0) {
        if (this.selectedPaymentMethod === 'cash') {
          cash = parseFloat(this.cashPaymentInput) || 0;
        } else if (this.selectedPaymentMethod === 'credit') {
          credit = parseFloat(this.creditPaymentInput) || 0;
        }
      }
    }

    const totalPaid = Number((Number(cash || 0) + Number(credit || 0)).toFixed(2));
    return totalPaid >= total;
  }
  show_delivered_only(aa: any) {
    if (aa == 'delivered') {
      this.Delivery_show_delivered_only = true
    } else {
      this.Delivery_show_delivered_only = false
    }


  }
  setCashAmount(value: number) {
    this.cash_value = value;
    localStorage.setItem('cash_value', String(value));
  }

  setCreditAmount(value: number) {
    this.credit_value = value;
    localStorage.setItem('credit_value', String(value));

    // مسح رسالة الخطأ عند تغيير قيمة الفيزا
    if (value === 0 || value === null) {
      this.referenceNumberError = '';
      this.paymentAmountError = '';
    }
  }
  // setCashAmount(value: number | null): void {
  //   this.cash_value = Number((value ?? 0).toFixed(2));
  //   localStorage.setItem('cash_value', JSON.stringify(this.cash_value));

  //   const total = this.invoices[0].invoice_summary.total_price;
  //   const remain = total - this.cash_value;
  //   this.credit_value = Number((remain >= 0 ? remain : 0).toFixed(2));
  //   localStorage.setItem('credit_value', JSON.stringify(this.credit_value));

  //   this.amountError = false;
  // }

  // setCreditAmount(value: number | null): void {
  //   this.credit_value = Number((value ?? 0).toFixed(2));
  //   localStorage.setItem('credit_value', JSON.stringify(this.credit_value));

  //   const total = this.invoices[0].invoice_summary.total_price;
  //   const remain = total - this.credit_value;
  //   this.cash_value = Number((remain >= 0 ? remain : 0).toFixed(2));
  //   localStorage.setItem('cash_value', JSON.stringify(this.cash_value));

  //   this.amountError = false;
  // }

  onPrintButtonClick() {
    this.confirmationDialog.confirm();
  }

  // دالة للتحقق من أن رقم المرجع يحتوي على أرقام فقط
  onReferenceNumberInput(event: any): void {
    const value = event.target.value;
    // إزالة أي حرف غير رقمي
    const numericValue = value.replace(/[^0-9]/g, '');
    this.referenceNumber = numericValue;
    // تحديث قيمة الحقل
    event.target.value = numericValue;

    // ✅ مسح رسالة الخطأ عند إدخال رقم المرجع (حتى لو كان فارغاً مؤقتاً)
    if (numericValue && numericValue.trim() !== '') {
      this.referenceNumberError = '';
      // مسح الأخطاء الأخرى المرتبطة أيضاً
      this.amountError = false;
      this.paymentAmountError = '';
    }
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

  // دالة لمسح الرسائل عند تغيير القيمة (بدون تطبيق تلقائي)
  onCouponCodeChange(value: string): void {
    // مسح رسائل الخطأ والنجاح عند تغيير الكود
    if (value && value.trim() !== this.couponCode) {
      this.couponError = '';
      this.couponMessage = '';
      this.appliedCoupon = null;
      this.discountAmount = 0;
    }
  }

  // ===== دوال الإكرامية =====
  getNearestAmount(amount: number, base: number): number {
    if (amount <= 0) return base;
    const roundedAmount = Math.ceil(amount / base) * base;
    return roundedAmount;
  }

  openTipModal(content: any, billAmount: number, paymentAmount: number, paymentMethod?: string): void {
    this.tempBillAmount = billAmount;
    this.tempPaymentAmount = paymentAmount;
    this.tempChangeAmount = Math.max(0, paymentAmount - billAmount);

    // تعيين طريقة الدفع إذا تم تمريرها
    if (paymentMethod) {
      if (paymentMethod === 'cash') {
        this.selectedPaymentMethod = 'cash';
      } else if (paymentMethod === 'credit') {
        this.selectedPaymentMethod = 'credit';
      } else if (paymentMethod === 'cash + credit') {
        this.selectedPaymentMethod = 'cash + credit';
      }
    }

    if (!this.selectedTipType) {
      this.selectedTipType = 'no_tip';
    }
    if (!this.specificTipAmount) {
      this.specificTipAmount = " ";
    }

    const modalRef = this.modalService.open(content, {
      centered: true,
      size: 'md'
    });

    this.startTipModalTimeout(modalRef, billAmount, paymentAmount);

    modalRef.result.then((result) => {
      console.log('Tip Modal Closed with final result:', result);
      this.stopTipModalTimeout();
      if (result) {
        this.applyTipToMainVariables(result);
      }
    }, (reason) => {
      console.log('Tip Modal Dismissed:', reason);
      this.stopTipModalTimeout();
      this.resetTempVariables();
    });
  }

  private startTipModalTimeout(modalRef: any, billAmount: number, paymentAmount: number): void {
    this.tipModalTimeRemaining = this.tipModalTimeoutDuration;
    this.tipModalWarningShown = false;

    this.tipModalCountdownRef = setInterval(() => {
      this.tipModalTimeRemaining--;

      if (this.tipModalTimeRemaining <= this.tipModalWarningTime && !this.tipModalWarningShown) {
        this.tipModalWarningShown = true;
        console.warn(`⚠️ تحذير: سيتم اختيار "بدون إكرامية" تلقائياً خلال ${this.tipModalWarningTime} ثواني`);
      }

      if (this.tipModalTimeRemaining <= 0) {
        this.stopTipModalTimeout();
        this.autoSelectNoTip(modalRef, billAmount, paymentAmount);
      }
    }, 1000);

    this.tipModalTimeoutRef = setTimeout(() => {
      this.stopTipModalTimeout();
      this.autoSelectNoTip(modalRef, billAmount, paymentAmount);
    }, this.tipModalTimeoutDuration * 1000);
  }

  private stopTipModalTimeout(): void {
    if (this.tipModalTimeoutRef) {
      clearTimeout(this.tipModalTimeoutRef);
      this.tipModalTimeoutRef = null;
    }
    if (this.tipModalCountdownRef) {
      clearInterval(this.tipModalCountdownRef);
      this.tipModalCountdownRef = null;
    }
    this.tipModalTimeRemaining = 0;
    this.tipModalWarningShown = false;
  }

  private autoSelectNoTip(modalRef: any, billAmount: number, paymentAmount: number): void {
    console.log('⏰ Timeout: اختيار "بدون إكرامية" تلقائياً');
    this.selectedTipType = 'no_tip';
    this.specificTipAmount = 0;
    this.confirmTipAndClose(modalRef);
  }

  private applyTipToMainVariables(finalTipSummary: any): void {
    if (!finalTipSummary) return;
    this.finalTipSummary = finalTipSummary;

    // تحديث القيم بناءً على طريقة الدفع
    if (this.selectedPaymentMethod === 'cash') {
      this.cash_value = finalTipSummary.cashFinal || finalTipSummary.paymentAmount;
      this.cashPaymentInput = finalTipSummary.paymentAmount;
    } else if (this.selectedPaymentMethod === 'credit') {
      this.credit_value = finalTipSummary.creditFinal || finalTipSummary.grandTotalWithTip;
      this.creditPaymentInput = finalTipSummary.paymentAmount;
    } else if (this.selectedPaymentMethod === 'cash + credit') {
      this.cashAmountMixed = finalTipSummary.cashFinal || finalTipSummary.cashAmountMixed || 0;
      this.creditAmountMixed = finalTipSummary.creditFinal || finalTipSummary.creditAmountMixed || 0;
      this.cash_value = finalTipSummary.cashFinal || 0;
      this.credit_value = finalTipSummary.creditFinal || 0;
    }

    // ✅ مسح جميع الأخطاء عند تأكيد الإكرامية
    this.clearAllPaymentErrors();

    localStorage.setItem('finalTipSummary', JSON.stringify(this.finalTipSummary));
    console.log('✅ تم تطبيق الإكرامية على المتغيرات الرئيسية:', this.finalTipSummary);
  }

  // ✅ دالة مساعدة لمسح جميع أخطاء الدفع
  private clearAllPaymentErrors(): void {
    this.paymentError = '';
    this.paymentAmountError = '';
    this.amountError = false;
    // لا نمسح referenceNumberError هنا لأنه قد يكون مطلوباً
  }

  private resetTempVariables(): void {
    this.stopTipModalTimeout();
    this.tempBillAmount = 0;
    this.tempPaymentAmount = 0;
    this.tempChangeAmount = 0;
    console.log('🔄 تم إعادة تعيين المتغيرات المؤقتة بعد الإلغاء');
  }

  selectTipOption(type: 'tip_the_change' | 'tip_specific_amount' | 'no_tip'): void {
    this.selectedTipType = type;

    switch (type) {
      case 'tip_the_change':
        this.specificTipAmount = this.tempChangeAmount;
        break;
      case 'no_tip':
        this.specificTipAmount = 0;
        break;
      case 'tip_specific_amount':
        this.specificTipAmount = " ";
        break;
    }
  }

  confirmTipAndClose(modal: any): void {
    this.stopTipModalTimeout();

    let finalTipAmount: number = 0;
    let additionalPaymentRequired: number = 0;
    let originalPaymentAmount: number = this.tempPaymentAmount;

    if (this.selectedTipType === 'tip_the_change') {
      finalTipAmount = this.tempChangeAmount;
      additionalPaymentRequired = 0;
    } else if (this.selectedTipType === 'tip_specific_amount') {
      const tipAmountValue = typeof this.specificTipAmount === 'string'
        ? parseFloat(this.specificTipAmount) || 0
        : Number(this.specificTipAmount) || 0;
      finalTipAmount = Math.max(0, tipAmountValue);

      if (finalTipAmount > this.tempChangeAmount) {
        additionalPaymentRequired = finalTipAmount - this.tempChangeAmount;
        this.tempPaymentAmount = this.tempPaymentAmount + additionalPaymentRequired;
      } else {
        additionalPaymentRequired = 0;
      }
    } else if (this.selectedTipType === 'no_tip') {
      finalTipAmount = 0;
      additionalPaymentRequired = 0;
      this.tempPaymentAmount = originalPaymentAmount;
    }

    const changeToReturn = Math.max(0, this.tempPaymentAmount - (this.tempBillAmount + finalTipAmount));
    const grandTotalWithTip = this.selectedTipType === 'no_tip'
      ? this.tempBillAmount
      : this.tempBillAmount + finalTipAmount;

    // حساب المبالغ النهائية بناءً على طريقة الدفع
    let cashFinal = 0;
    let creditFinal = 0;
    let paymentMethodText = '';

    if (this.selectedPaymentMethod === 'cash') {
      cashFinal = this.selectedTipType === 'no_tip'
        ? this.tempBillAmount
        : (this.selectedTipType === 'tip_the_change' ? this.tempPaymentAmount : grandTotalWithTip);
      creditFinal = 0;
      paymentMethodText = 'كاش';
    } else if (this.selectedPaymentMethod === 'credit') {
      cashFinal = 0;
      if (this.selectedTipType === 'no_tip') {
        creditFinal = this.tempBillAmount;
      } else if (this.selectedTipType === 'tip_the_change') {
        creditFinal = this.tempPaymentAmount;
      } else if (this.selectedTipType === 'tip_specific_amount') {
        creditFinal = this.tempBillAmount + finalTipAmount;
      } else {
        creditFinal = grandTotalWithTip;
      }
      paymentMethodText = 'فيزا';
    } else if (this.selectedPaymentMethod === 'cash + credit') {
      cashFinal = this.cashAmountMixed || 0;
      creditFinal = this.creditAmountMixed || 0;

      if (additionalPaymentRequired > 0) {
        const totalOriginal = cashFinal + creditFinal;
        if (totalOriginal > 0) {
          const cashRatio = cashFinal / totalOriginal;
          const creditRatio = creditFinal / totalOriginal;
          cashFinal += additionalPaymentRequired * cashRatio;
          creditFinal += additionalPaymentRequired * creditRatio;
        }
      }
      paymentMethodText = 'كاش + فيزا';
    }

    this.finalTipSummary = {
      tipAmount: finalTipAmount,
      changeToReturn: changeToReturn,
      grandTotalWithTip: grandTotalWithTip,
      cashFinal: cashFinal,
      creditFinal: creditFinal,
      paymentAmount: this.tempPaymentAmount,
      billAmount: this.tempBillAmount,
      originalPaymentAmount: originalPaymentAmount,
      additionalPaymentRequired: additionalPaymentRequired,
      paymentMethod: paymentMethodText
    };

    // مسح جميع الأخطاء عند تأكيد الإكرامية بنجاح
    this.paymentError = '';
    this.paymentAmountError = '';
    this.amountError = false;

    if (additionalPaymentRequired > 0) {
      this.showAdditionalPaymentConfirmation(additionalPaymentRequired, modal);
    } else {
      modal.close(this.finalTipSummary);
    }
  }

  showAdditionalPaymentConfirmation(additionalAmount: number, modal: any): void {
    const roundedAdditionalAmount = Math.round(additionalAmount * 1000) / 1000;

    // حفظ البيانات للعرض في الـ modal
    this.additionalPaymentRequiredAmount = roundedAdditionalAmount;
    this.requiredTipAmount = this.specificTipAmount;
    this.currentPaymentAmount = this.finalTipSummary!.originalPaymentAmount!;
    this.pendingTipModal = modal; // حفظ الـ modal الأصلي

    // إغلاق modal الإكرامية أولاً
    modal.dismiss('Opening additional payment modal');

    // فتح الـ modal المخصص بعد تأخير بسيط لضمان إغلاق الأول
    setTimeout(() => {
      const modalElement = document.getElementById('additionalPaymentModal');
      if (modalElement) {
        const bsModal = new bootstrap.Modal(modalElement, {
          backdrop: 'static',
          keyboard: false
        });
        this.additionalPaymentModalRef = bsModal;
        bsModal.show();
      }
    }, 300);
  }

  confirmAdditionalPayment() {
    // إغلاق الـ modal
    if (this.additionalPaymentModalRef) {
      this.additionalPaymentModalRef.hide();
      this.additionalPaymentModalRef = null;
    }

    // إغلاق الـ modal الأصلي للإكرامية والمتابعة
    if (this.pendingTipModal) {
      this.pendingTipModal.close(this.finalTipSummary);
      this.pendingTipModal = null;
    }

    // مسح جميع الأخطاء عند تأكيد المبلغ الإضافي
    this.paymentError = '';
    this.paymentAmountError = '';
    this.amountError = false;

    // إعادة تعيين المتغيرات
    this.additionalPaymentRequiredAmount = 0;
    this.requiredTipAmount = 0;
    this.currentPaymentAmount = 0;
  }

  closeAdditionalPaymentModal() {
    // إغلاق الـ modal
    if (this.additionalPaymentModalRef) {
      this.additionalPaymentModalRef.hide();
      this.additionalPaymentModalRef = null;
    }

    // إلغاء وتراجع عن الحسابات
    if (this.finalTipSummary) {
      this.finalTipSummary = null;
      this.tempPaymentAmount = this.tempBillAmount;
    }

    // إعادة تعيين المتغيرات
    this.additionalPaymentRequiredAmount = 0;
    this.requiredTipAmount = 0;
    this.currentPaymentAmount = 0;
    this.pendingTipModal = null;
  }

  selectPaymentSuggestionAndOpenModal(type: 'billAmount' | 'amount50' | 'amount100', billAmount: number, paymentAmount: number, modalContent: any): void {
    this.selectedSuggestionType = type;
    this.paymentError = '';

    if (paymentAmount < billAmount) {
      const remaining = billAmount - paymentAmount;
      this.paymentError = `المبلغ غير كافي. المتبقي: ${remaining.toFixed(2)} ${this.currencySymbol}`;
      this.paymentAmountError = this.paymentError;
      this.amountError = true;
      return;
    }

    // ✅ تحديث القيم بناءً على طريقة الدفع المختارة - تحويل إلى رقم صريح
    const paymentAmountNum = Number(paymentAmount);
    if (this.selectedPaymentMethod === 'cash') {
      this.cash_value = paymentAmountNum;
      this.cashPaymentInput = paymentAmountNum;
      // ✅ استخدام setPaymentInputValue للتأكد من التحديث الصحيح
      this.setPaymentInputValue(paymentAmountNum);
    } else if (this.selectedPaymentMethod === 'credit') {
      this.credit_value = paymentAmountNum;
      // ✅ تحديث creditPaymentInput مباشرة أولاً
      this.creditPaymentInput = paymentAmountNum;
      // ✅ استخدام setPaymentInputValue للتأكد من التحديث الصحيح
      this.setPaymentInputValue(paymentAmountNum);
      // ✅ إجبار Angular على تحديث العرض بعد تأخير بسيط لضمان التحديث
      setTimeout(() => {
        this.cdr.detectChanges();
      }, 0);
    }

    // ✅ مسح جميع الأخطاء عند اختيار مبلغ صحيح
    this.paymentError = '';
    this.paymentAmountError = '';
    this.amountError = false;

    // ✅ فتح مودال الإكرامية تلقائياً إذا كان المبلغ كافياً (>=)
    if (paymentAmount >= billAmount) {
      this.openTipModal(modalContent, billAmount, paymentAmount, this.selectedPaymentMethod);
    }
  }

  handleManualPaymentBlur(billAmount: number, modalContent: any): void {
    this.selectedSuggestionType = null;
    this.paymentError = '';

    // قراءة القيمة الصحيحة بناءً على طريقة الدفع
    const currentPaymentInput = this.selectedPaymentMethod === 'credit'
      ? parseFloat(this.creditPaymentInput) || 0
      : parseFloat(this.cashPaymentInput) || 0;

    if (currentPaymentInput <= 0) {
      this.paymentError = 'يرجى إدخال مبلغ صحيح';
      this.paymentAmountError = this.paymentError;
      this.amountError = true;
      return;
    }

    if (Number(currentPaymentInput.toFixed(2)) < Number(billAmount.toFixed(2))) {
      const remaining = Number(billAmount.toFixed(2)) - Number(currentPaymentInput.toFixed(2));
      this.paymentError = `المبلغ غير كافي. المتبقي: ${remaining.toFixed(2)} ${this.currencySymbol}`;
      this.paymentAmountError = this.paymentError;
      this.amountError = true;
      return;
    }

    // تحديث القيم بناءً على طريقة الدفع المختارة
    if (this.selectedPaymentMethod === 'cash') {
      this.cash_value = currentPaymentInput;
      this.cashPaymentInput = currentPaymentInput;
    } else if (this.selectedPaymentMethod === 'credit') {
      this.credit_value = currentPaymentInput;
      this.creditPaymentInput = currentPaymentInput;
    }

    // ✅ مسح جميع الأخطاء عند إدخال مبلغ صحيح
    this.paymentError = '';
    this.paymentAmountError = '';
    this.amountError = false;

    // ✅ فتح مودال الإكرامية تلقائياً إذا كان المبلغ كافياً (>=)
    if (currentPaymentInput >= billAmount) {
      this.openTipModal(modalContent, billAmount, currentPaymentInput, this.selectedPaymentMethod);
    }
  }

  openMixedPaymentTipModal(billAmount: number, modalContent: any): void {
    this.paymentError = '';
    const cashAmount = parseFloat(this.cashAmountMixed) || 0;
    const creditAmount = parseFloat(this.creditAmountMixed) || 0;
    const totalPaid = cashAmount + creditAmount;

if (Number(totalPaid.toFixed(2)) < Number(billAmount.toFixed(2))) { 
      const remaining = Number(billAmount.toFixed(2)) - Number(totalPaid.toFixed(2));
      this.paymentError = `المبلغ غير كافي. المتبقي: ${remaining.toFixed(2)} ${this.currencySymbol}`;
      this.paymentAmountError = this.paymentError;
      this.amountError = true;
      return;
    }

    // مسح الأخطاء عند إدخال مبلغ صحيح
    this.paymentError = '';
    this.paymentAmountError = '';
    this.amountError = false;

    // ✅ فتح مودال الإكرامية تلقائياً إذا كان المبلغ كافياً (>=)
    if (totalPaid >= billAmount) {
      this.openTipModal(modalContent, billAmount, totalPaid, 'cash + credit');
    }
  }

  parseTipAmount(value: any): number {
    if (value === null || value === undefined || value === " " || value === "") {
      return 0;
    }
    if (typeof value === 'string') {
      const parsed = parseFloat(value);
      return isNaN(parsed) ? 0 : parsed;
    }
    return Number(value) || 0;
  }

  handleCashBlur(): void {
    if (this.cash_value && this.cash_value > 0) {
      const billAmount = this.getInvoiceTotal();
      if (this.cash_value > billAmount) {
        this.openTipModal(this.tipModalContent, billAmount, this.cash_value, 'cash');
      }
    }
  }

  handleCreditBlur(): void {
    if (this.credit_value && this.credit_value > 0) {
      const billAmount = this.getInvoiceTotal();
      if (this.credit_value > billAmount) {
        this.openTipModal(this.tipModalContent, billAmount, this.credit_value, 'credit');
      }
    }
  }

  selectPaymentMethod(method: 'cash' | 'credit' | 'cash + credit'): void {
    this.selectedPaymentMethod = method;
    this.paymentDeviceError = '';
    // إعادة تعيين القيم عند تغيير طريقة الدفع
    if (method === 'cash') {
      this.credit_value = null;
      this.cashAmountMixed = " ";
      this.creditAmountMixed = " ";
    } else if (method === 'credit') {
      this.cash_value = null;
      this.cashAmountMixed = " ";
      this.creditAmountMixed = " ";
    } else if (method === 'cash + credit') {
      this.cash_value = null;
      this.credit_value = null;
    }
    this.finalTipSummary = null;
    // ✅ مسح جميع الأخطاء عند تغيير طريقة الدفع
    this.paymentError = '';
    this.paymentAmountError = '';
    this.amountError = false;
    // مسح خطأ رقم المرجع عند التبديل من فيزا إلى طريقة أخرى
    if (method !== 'credit' && method !== 'cash + credit') {
      this.referenceNumberError = '';
    }
    this.ensureSelectedPaymentDevice();
  }

  shouldShowPaymentDeviceSelector(): boolean {
    return this.paymentStatus === 'paid' && (this.selectedPaymentMethod === 'credit' || this.selectedPaymentMethod === 'cash + credit');
  }

  private coercePositiveDeviceId(value: unknown): number | null {
    if (value == null || value === '') {
      return null;
    }
    const n = typeof value === 'number' ? value : Number(value);
    return Number.isFinite(n) && n > 0 ? n : null;
  }

  private isPaymentDeviceSelectionValid(): boolean {
    const n = this.coercePositiveDeviceId(this.selectedPaymentDeviceId);
    return n != null && this.paymentDevices.some((d) => Number(d.id) === n);
  }

  private effectivePaymentDeviceIdForOrder(): number | null {
    if (!this.shouldShowPaymentDeviceSelector()) {
      return null;
    }
    const n = this.coercePositiveDeviceId(this.selectedPaymentDeviceId);
    if (n == null) {
      return null;
    }
    return this.paymentDevices.some((d) => Number(d.id) === n) ? n : null;
  }

  onPaymentDeviceChange(value: number | string): void {
    this.selectedPaymentDeviceId = this.coercePositiveDeviceId(value);
    this.paymentDeviceError = '';
  }

  private ensureSelectedPaymentDevice(): void {
    if (!this.shouldShowPaymentDeviceSelector()) {
      return;
    }
    const n = this.coercePositiveDeviceId(this.selectedPaymentDeviceId);
    if (n != null && this.paymentDevices.some((d) => Number(d.id) === n)) {
      this.selectedPaymentDeviceId = n;
      return;
    }
    const recommended = this.paymentDevices.find((d) => d.isRecommended);
    this.selectedPaymentDeviceId = recommended?.id ?? null;
  }

  private markSelectedPaymentDeviceAsLastUsed(): void {
    const n = this.coercePositiveDeviceId(this.selectedPaymentDeviceId);
    if (!this.shouldShowPaymentDeviceSelector() || n == null) {
      return;
    }
    localStorage.setItem(this.LAST_USED_PAYMENT_DEVICE_STORAGE_KEY, String(n));
    this.paymentDevices = this.paymentDevices.map((d) => ({
      ...d,
      isRecommended: Number(d.id) === n,
    }));
  }

  private loadPaymentDevices(): void {
    this.http.get<any>(`${baseUrl2}/payment-device/`).pipe(take(1)).subscribe({
      next: (res) => {
        const rawDevices = Array.isArray(res) ? res : Array.isArray(res?.data) ? res.data : [];
        const activeDevices = rawDevices
          .map((device: any) => ({
            id: Number(device?.id),
            name: String(device?.device_name || '').trim(),
            ip: String(device?.IP || '').trim(),
            status: device?.status === 'active' ? 'active' : 'inactive',
            lastUsedAt: device?.last_used_device || null,
          }))
          .filter((device: any) => Number.isFinite(device.id) && device.id > 0 && device.status === 'active');

        const storedLastUsedId = Number(localStorage.getItem(this.LAST_USED_PAYMENT_DEVICE_STORAGE_KEY));
        const backendRecommended = [...activeDevices]
          .filter((d: any) => !!d.lastUsedAt)
          .sort((a: any, b: any) => {
            const aTime = a.lastUsedAt ? new Date(a.lastUsedAt).getTime() : 0;
            const bTime = b.lastUsedAt ? new Date(b.lastUsedAt).getTime() : 0;
            return bTime - aTime;
          })[0];

        let recommendedId: number | null = backendRecommended?.id ?? null;
        if (
          recommendedId == null &&
          Number.isFinite(storedLastUsedId) &&
          storedLastUsedId > 0 &&
          activeDevices.some((d: any) => d.id === storedLastUsedId)
        ) {
          recommendedId = storedLastUsedId;
        }

        this.paymentDevices = activeDevices.map((d: any) => ({
          ...d,
          isRecommended: recommendedId != null && d.id === recommendedId,
        }));

        this.ensureSelectedPaymentDevice();
      },
      error: () => {
        this.paymentDevices = [];
        this.selectedPaymentDeviceId = null;
      },
    });
  }

  // Helper method to ensure values are never negative (same as cart)
  getMaxZero(value: number): number {
    return Math.max(0, value);
  }

  roundUpToTwoDecimals(value: number): number {
    return Math.ceil(value * 100) / 100;
  }

  // ✅ Getter and Setter for payment input value
  getPaymentInputValue(): any {
    if (this.selectedPaymentMethod === 'credit') {
      // ✅ إرجاع القيمة مباشرة (سيتم تحويلها تلقائياً في الحقل)
      const value = this.creditPaymentInput;
      // ✅ إذا كانت القيمة مسافة أو فارغة، إرجاع null أو 0
      if (value === " " || value === "" || value === null || value === undefined) {
        return null;
      }
      // ✅ إرجاع القيمة كرقم إذا أمكن
      return typeof value === 'number' ? value : Number(value) || null;
    }
    const value = this.cashPaymentInput;
    if (value === " " || value === "" || value === null || value === undefined) {
      return null;
    }
    return typeof value === 'number' ? value : Number(value) || null;
  }

  setPaymentInputValue(value: any): void {
    if (this.selectedPaymentMethod === 'credit') {
      this.creditPaymentInput = value;
    } else {
      this.cashPaymentInput = value;
    }
  }

  // Get the actual payment amount to display in "المبلغ المستحق"
  getDisplayPaymentAmount(billAmount: number): number {
    // If finalTipSummary exists (after tip confirmation), use the payment amount
    if (this.finalTipSummary?.paymentAmount) {
      return this.finalTipSummary.paymentAmount;
    }

    // For credit payment, check credit_value or creditPaymentInput
    if (this.selectedPaymentMethod === 'credit') {
      const creditValue = Number(this.credit_value || 0);
      const creditInput = Number(this.creditPaymentInput || 0);
      if (creditValue > 0) {
        return creditValue;
      }
      if (creditInput > 0) {
        return creditInput;
      }
    }

    // For cash payment, check cashPaymentInput
    if (this.selectedPaymentMethod === 'cash') {
      const cashInput = Number(this.cashPaymentInput || 0);
      if (cashInput > 0) {
        return cashInput;
      }
    }

    // For mixed payment, sum both amounts
    if (this.selectedPaymentMethod === 'cash + credit') {
      const cash = Number(this.cashAmountMixed || 0);
      const credit = Number(this.creditAmountMixed || 0);
      const total = cash + credit;
      if (total > 0) {
        return total;
      }
    }

    // Default: return bill amount
    return billAmount;
  }
}
