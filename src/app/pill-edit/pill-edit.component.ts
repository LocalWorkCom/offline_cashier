import {
  Component,
  OnInit,
  ViewChild,
  ElementRef,
  ChangeDetectorRef,
  TemplateRef,
} from '@angular/core';
import { PillDetailsService } from '../services/pill-details.service';
import { CommonModule, DecimalPipe } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { DatePipe } from '@angular/common';
import { PrintedInvoiceService } from '../services/printed-invoice.service';
import { Router } from '@angular/router';
declare var bootstrap: any;
import { FormsModule } from '@angular/forms';
import { ConfirmDialogComponent } from "../shared/ui/component/confirm-dialog/confirm-dialog.component";
import { finalize } from 'rxjs';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { baseUrl } from '../environment';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-pill-edit',
  imports: [CommonModule, DecimalPipe, FormsModule, ConfirmDialogComponent],
  templateUrl: './pill-edit.component.html',
  styleUrl: './pill-edit.component.css',
  providers: [DatePipe],
})
export class PillEditComponent {
  @ViewChild('printedPill') printedPill!: ElementRef;
  @ViewChild('printDialog') confirmationDialog!: ConfirmDialogComponent;
  @ViewChild('tipModalContent') tipModalContent!: TemplateRef<any>;

  loading: boolean = false;
  // @ViewChild('deliveredButton', { static: false }) deliveredButton!: ElementRef;
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
    private route: ActivatedRoute,
    private orderService: PillDetailsService,
    private cdr: ChangeDetectorRef,
    private datePipe: DatePipe,
    private printedInvoiceService: PrintedInvoiceService,
    private router: Router,
    private http: HttpClient,
    private modalService: NgbModal
  ) { 
    this.currencySymbol = localStorage.getItem('currency_symbol') || 'ج.م';
  }

  private extractDateAndTime(branch: any): void {
    const { created_at } = branch;
    console.log(created_at, 'test');

    if (created_at) {
      const dateObj = new Date(created_at);

      this.date = this.datePipe.transform(dateObj, 'yyyy-MM-dd');

      this.time = this.datePipe.transform(dateObj, 'hh:mm a');
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
        console.log(this.invoices[0].order_type);
        this.orderType = this.invoices[0].order_type;

        // restore coupon data if exists on invoice
        const summary = this.invoices?.[0]?.invoice_summary;
        if (summary) {
          this.couponType = summary.coupon_type || '';
          this.couponTitle = summary.coupon_title || '';
          this.discountAmount = Number(summary.coupon_value) || 0;
          this.couponCode = summary.coupon_code || '';
          
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
        this.orderNumber = Number(response.data.order_id);
        this.couponType = this.invoices[0].invoice_summary.coupon_type;

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

          // Convert coupon_value if it's a percentage
          // if (summary.coupon_type === 'percentage') {
          //   const couponValue = parseFloat(summary.coupon_value); // "10.00" → 10
          //   const subtotal = parseFloat(summary.subtotal_price);
          //   summary.coupon_value = ((couponValue / 100) * subtotal).toFixed(2); // Convert to currency
          // }

          return summary;
        });

        this.addressDetails = this.invoices?.map((e: any) => e.address_details);

        if (this.branchDetails?.length) {
          this.extractDateAndTime(this.branchDetails[0]);
        }

      },
      error: (error: any) => {
        console.error(' Error fetching pill details:', error);
      },
    });
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
    console.log(this.paymentStatus);
    this.cdr.detectChanges();
  }

  changeTrackingStatus(status: string) {
    this.trackingStatus = status.trim();
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
    this.amountError = false;

    if (!this.paymentStatus && this.trackingStatus !== 'on_way') {
      alert('يجب تحديد حالة الدفع  قبل الحفظ!');
      if (
        this.orderType == 'Delivery' && !this.trackingStatus
      ) {
        alert('يجب تحديد  حالة التوصيل قبل الحفظ!');
        return;
      }
      else if (this.orderType == 'Delivery' && this.trackingStatus == 'delivered') {
        if (!this.paymentStatus) {
          alert('يجب تحديد حالة الدفع  قبل الحفظ!');
          return;
        }
      } else if (this.orderType == 'Delivery' && this.trackingStatus == 'on_way') {
        if (!this.paymentStatus) {
          alert('يجب تحديد حالة الدفع  قبل الحفظ!');
          return;
        }
      }
      else {
        if (!this.paymentStatus) {
          return;
        }
      }
      return
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

    if (this.paymentStatus === 'paid' && !this.isPaymentAmountValid()) {
      this.amountError = true;
      return; // 🔒 منع المتابعة إذا كان المبلغ غير صحيح
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
        // التحقق من رقم المرجع للفيزا أولاً قبل تعديل المبالغ
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
        
        // نجعل المبلغ المسجل دائماً يساوي الإجمالي: نستخدم الكاش أولاً ثم نكمل بالفيزا
        const usedCash = Math.min(Number(cashAmount || 0), total);
        const remaining = Number((total - usedCash).toFixed(2));
        cashAmount = usedCash;
        creditAmount = remaining > 0 ? remaining : 0;

        // مزامنة القيم المعروضة بعد التصحيح
        this.cash_value = cashAmount;
        this.credit_value = creditAmount;
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
        coupon_title: this.couponTitle || this.invoices?.[0]?.invoice_summary?.coupon_title || ''
      } : undefined;
      
      // ✅ إعداد بيانات الإكرامية إذا كانت موجودة
      const tipData = this.finalTipSummary ? {
        tip_amount: this.finalTipSummary.tipAmount || 0,
        returned_amount: this.finalTipSummary.changeToReturn || 0,
        total_with_tip: this.finalTipSummary.grandTotalWithTip || finalTotal,
        payment_amount: this.finalTipSummary.paymentAmount || (cashAmount + creditAmount),
        bill_amount: this.finalTipSummary.billAmount || finalTotal
      } : undefined;
      
      this.orderService
        .updateInvoiceStatus(
          this.orderNumber,
          this.paymentStatus,
          this.trackingStatus,
          cashAmount,
          creditAmount,
          this.DeliveredOrNot,
          finalTotal, // استخدام finalTotal بدلاً من totalll || getInvoiceTotal
          tipData, // ✅ إرسال بيانات الإكرامية
          this.referenceNumber,
          couponData // إرسال بيانات الكوبون
        ).pipe(finalize(() => this.loading = false))
        .subscribe({
          next: (response) => {
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

            // ✅ Success - فقط إذا تم الحفظ بنجاح
            this.apiErrors = [];
            localStorage.removeItem('cash_value')
            localStorage.removeItem('credit_value')
            localStorage.setItem(
              'pill_detail_data',
              JSON.stringify(response.data)
            );
            this.showSuccessPillEditModal();
            this.fetchPillsDetails(this.pillId);
            window.location.reload();
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
  
    // حفظ القيم الأصلية
    const subtotalBefore = Number(summary.subtotal_price_before_coupon ?? summary.total_price ?? 0);
    const servicePerc = Number(summary.service_percentage || 0);
    const serviceFixed = Number(summary.service_fees || 0);
    const taxPerc = Number(summary.tax_percentage || 0);
    const deliveryFees = Number(summary.delivery_fees || 0);
    
    // حساب المبلغ بعد الخصم
    const discountValue = Math.min(discount, subtotalBefore);
    let subtotalAfterDiscount = subtotalBefore - discountValue;
    
    // إضافة رسوم الخدمة (نسبة أو مبلغ ثابت)
    let serviceAmount = 0;
    if (servicePerc > 0) {
      serviceAmount = (subtotalAfterDiscount * servicePerc) / 100;
    } else {
      serviceAmount = serviceFixed;
    }
    
    // إضافة رسوم الخدمة إلى المبلغ
    let amountAfterService = subtotalAfterDiscount + serviceAmount;
    
    // حساب الضريبة
    let taxAmount = 0;
    if (taxPerc > 0) {
      taxAmount = (amountAfterService * taxPerc) / 100;
    }
    
    // الحساب النهائي (يشمل delivery_fees)
    const finalTotal = amountAfterService + taxAmount + deliveryFees;
  
    // تحديث بيانات الفاتورة
    summary.coupon_value = discountValue;
    summary.coupon_title = title;
    summary.coupon_type = type;
    summary.coupon_code = this.couponCode || title;
    summary.subtotal_price_before_coupon = subtotalBefore;
    summary.total_price = Number(finalTotal.toFixed(2));
    summary.total_after_tax = Number(finalTotal.toFixed(2));
    summary.tax = Number(taxAmount.toFixed(2));
    
    // تحديث بيانات رسوم الخدمة إذا كانت نسبة
    if (servicePerc > 0) {
      summary.service_fees = Number(serviceAmount.toFixed(2));
    }
  
    this.discountAmount = discountValue;
    this.couponTitle = title;
    this.couponType = type;
    this.totalll = summary.total_price;
    this.cdr.detectChanges();
    
    console.log('✅ Manual discount applied - Updated totals:', {
      subtotalBefore,
      discountValue,
      subtotalAfterDiscount,
      serviceAmount,
      amountAfterService,
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

  removeDiscount(): void {
    const summary = this.invoices?.[0]?.invoice_summary;
    if (!summary) return;
    
    // التحقق من وجود كوبون مطبق (من البيانات الأصلية أو المطبق حديثاً)
    const hasCoupon = (this.discountAmount > 0) || 
                      (summary.coupon_value && summary.coupon_value > 0);
    if (!hasCoupon) return;
    
    // حفظ القيم الأصلية
    const originalSubtotal = Number(summary.subtotal_price_before_coupon || summary.total_price || 0);
    const taxPerc = Number(summary.tax_percentage || 0);
    const servicePerc = Number(summary.service_percentage || 0);
    const serviceFixed = Number(summary.service_fees || 0);
    const deliveryFees = Number(summary.delivery_fees || 0);
    
    // إعادة الحساب من الصفر
    let subtotalAfter = originalSubtotal;
    
    // حساب رسوم الخدمة
    let serviceAmount = 0;
    if (servicePerc > 0) {
      serviceAmount = (subtotalAfter * servicePerc) / 100;
    } else {
      serviceAmount = serviceFixed;
    }
    
    subtotalAfter += serviceAmount;
    
    // حساب الضريبة
    let taxAmount = 0;
    if (taxPerc > 0) {
      taxAmount = (subtotalAfter * taxPerc) / 100;
    }
    
    // الحساب النهائي (يشمل delivery_fees)
    const finalTotal = subtotalAfter + taxAmount + deliveryFees;
  
    // تحديث بيانات الفاتورة
    summary.coupon_value = 0;
    summary.coupon_title = '';
    summary.coupon_type = '';
    summary.coupon_code = '';
    summary.total_price = Number(finalTotal.toFixed(2));
    summary.total_after_tax = Number(finalTotal.toFixed(2));
    summary.tax = Number(taxAmount.toFixed(2));
    
    // تحديث بيانات رسوم الخدمة إذا كانت نسبة
    if (servicePerc > 0) {
      summary.service_fees = Number(serviceAmount.toFixed(2));
    }
  
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
  async printInvoice(isfinal: boolean) {
    this.isFinal = isfinal
    if (!this.invoices?.length || !this.invoiceSummary?.length) {
      console.warn('Invoice data not ready.');
      return;
    }

    try {
      const response = await this.printedInvoiceService
        .printInvoice(this.orderNumber, this.cashier_machine_id, this.paymentMethod)
        .toPromise();
      console.log(response, 'testttttt')
      console.log('Print invoice response:', response);
      const printContent = document.getElementById('printSectionn');
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
    // استخدام totalll إذا كان محدثاً (بعد تطبيق الكوبون)، وإلا استخدام invoice_summary.total_price
    if (this.totalll && this.totalll > 0) {
      return this.totalll;
    }
    return this.invoices?.[0]?.invoice_summary?.total_price || 0;
  }

  isPaymentAmountValid(): boolean {
    if (this.paymentStatus !== 'paid') return true;

    const cash = Number(this.cash_value ?? 0);
    const credit = Number(this.credit_value ?? 0);
    const total = this.getInvoiceTotal();
    return Number(((Number(cash) || 0) + (Number(credit) || 0)).toFixed(2)) >= total;
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
    
    // مسح رسالة الخطأ عند إدخال رقم المرجع
    if (numericValue && numericValue.trim() !== '') {
      this.referenceNumberError = '';
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

    localStorage.setItem('finalTipSummary', JSON.stringify(this.finalTipSummary));
    console.log('✅ تم تطبيق الإكرامية على المتغيرات الرئيسية:', this.finalTipSummary);
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
      return;
    }

    // تحديث القيم بناءً على طريقة الدفع المختارة
    if (this.selectedPaymentMethod === 'cash') {
      this.cash_value = paymentAmount;
      this.cashPaymentInput = paymentAmount;
    } else if (this.selectedPaymentMethod === 'credit') {
      this.credit_value = paymentAmount;
      this.creditPaymentInput = paymentAmount;
    }

    if (paymentAmount > billAmount) {
      // مسح الأخطاء عند اختيار مبلغ صحيح
      this.paymentError = '';
      this.paymentAmountError = '';
      this.amountError = false;
      this.openTipModal(modalContent, billAmount, paymentAmount, this.selectedPaymentMethod);
    } else {
      // إذا كان المبلغ يساوي الفاتورة بالضبط، تحديث القيم مباشرة
      if (this.selectedPaymentMethod === 'cash') {
        this.cash_value = paymentAmount;
        this.cashPaymentInput = paymentAmount;
      } else if (this.selectedPaymentMethod === 'credit') {
        this.credit_value = paymentAmount;
        this.creditPaymentInput = paymentAmount;
      }
      // مسح الأخطاء عند اختيار مبلغ صحيح
      this.paymentError = '';
      this.paymentAmountError = '';
      this.amountError = false;
      // تحديث العرض
      this.cdr.detectChanges();
    }
  }

  handleManualPaymentBlur(billAmount: number, modalContent: any): void {
    this.selectedSuggestionType = null;
    this.paymentError = '';

    const currentPaymentInput = parseFloat(this.cashPaymentInput) || 0;

    if (currentPaymentInput <= 0) {
      this.paymentError = 'يرجى إدخال مبلغ صحيح';
      return;
    }

    if (currentPaymentInput < billAmount) {
      const remaining = billAmount - currentPaymentInput;
      this.paymentError = `المبلغ غير كافي. المتبقي: ${remaining.toFixed(2)} ${this.currencySymbol}`;
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

    if (currentPaymentInput > billAmount) {
      // مسح الأخطاء عند إدخال مبلغ صحيح
      this.paymentError = '';
      this.paymentAmountError = '';
      this.amountError = false;
      this.openTipModal(modalContent, billAmount, currentPaymentInput, this.selectedPaymentMethod);
    } else {
      // إذا كان المبلغ يساوي الفاتورة بالضبط، تحديث القيم مباشرة
      if (this.selectedPaymentMethod === 'cash') {
        this.cash_value = currentPaymentInput;
        this.cashPaymentInput = currentPaymentInput;
      } else if (this.selectedPaymentMethod === 'credit') {
        this.credit_value = currentPaymentInput;
        this.creditPaymentInput = currentPaymentInput;
      }
      // مسح الأخطاء عند إدخال مبلغ صحيح
      this.paymentError = '';
      this.paymentAmountError = '';
      this.amountError = false;
      // تحديث العرض
      this.cdr.detectChanges();
    }
  }

  openMixedPaymentTipModal(billAmount: number, modalContent: any): void {
    this.paymentError = '';
    const cashAmount = parseFloat(this.cashAmountMixed) || 0;
    const creditAmount = parseFloat(this.creditAmountMixed) || 0;
    const totalPaid = cashAmount + creditAmount;

    if (totalPaid < billAmount) {
      const remaining = billAmount - totalPaid;
      this.paymentError = `المبلغ غير كافي. المتبقي: ${remaining.toFixed(2)} ${this.currencySymbol}`;
      this.paymentAmountError = this.paymentError;
      this.amountError = true;
      return;
    }

    // مسح الأخطاء عند إدخال مبلغ صحيح
    this.paymentError = '';
    this.paymentAmountError = '';
    this.amountError = false;

    if (totalPaid > billAmount) {
      this.openTipModal(modalContent, billAmount, totalPaid, 'cash + credit');
    } else if (totalPaid === billAmount) {
      // إذا كان المبلغ يساوي الفاتورة بالضبط، مسح الأخطاء فقط
      this.paymentError = '';
      this.paymentAmountError = '';
      this.amountError = false;
      this.cdr.detectChanges();
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
    // مسح جميع الأخطاء عند تغيير طريقة الدفع
    this.paymentError = '';
    this.paymentAmountError = '';
    this.amountError = false;
  }

  // Helper method to ensure values are never negative (same as cart)
  getMaxZero(value: number): number {
    return Math.max(0, value);
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
