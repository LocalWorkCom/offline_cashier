import {
  Component,
  OnInit,
  ViewChild,
  ElementRef,
  ChangeDetectorRef,
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
  cash_value: number | null = null;
  credit_value: number | null = null;
  amountError: boolean = false;
  Delivery_show_delivered_only: boolean = false;
  referenceNumber: any;
  referenceNumberTouched: boolean = false;
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

  constructor(
    private pillDetailsService: PillDetailsService,
    private route: ActivatedRoute,
    private orderService: PillDetailsService,
    private cdr: ChangeDetectorRef,
    private datePipe: DatePipe,
    private printedInvoiceService: PrintedInvoiceService,
    private router: Router,
    private http: HttpClient
  ) { }

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

        this.paymentStatus = this.invoices[0]?.['payment_status'];
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

    if (this.paymentStatus === 'paid' && !this.isPaymentAmountValid()) {
      this.amountError = true;

    }
    
    // التحقق من رقم المرجع للفيزا
    var creditAmount = this.credit_value != null ? this.credit_value : 0;
    if (this.paymentStatus === 'paid' && creditAmount > 0 && (!this.referenceNumber || !this.referenceNumber.trim())) {
      this.referenceNumberTouched = true;
      alert('❌ رقم المرجع مطلوب عند الدفع بالفيزا.');
      return;
    }
    
    var cashAmount = this.cash_value != null ? this.cash_value : 0;
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
    
    if (this.amountError == false && this.loading == false) {
      this.loading = true
      
      // إعداد بيانات الكوبون إذا كان موجوداً
      const couponData = (this.discountAmount > 0 || this.couponCode) ? {
        coupon_code: this.couponCode || this.invoices?.[0]?.invoice_summary?.coupon_code || '',
        coupon_value: this.discountAmount || this.invoices?.[0]?.invoice_summary?.coupon_value || 0,
        coupon_type: this.couponType || this.invoices?.[0]?.invoice_summary?.coupon_type || '',
        coupon_title: this.couponTitle || this.invoices?.[0]?.invoice_summary?.coupon_title || ''
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
          undefined,
          this.referenceNumber,
          couponData // إرسال بيانات الكوبون
        ).pipe(finalize(() => this.loading = false))
        .subscribe({
          next: (response) => {
            if (response.status === false && response.message) {
              this.errr = response.message
            }
            if (response.status === false || response.errorData) {
              // Handle validation or logical API errors
              this.apiErrors = Object.values(
                response.errorData as { [key: string]: string[] }
              ).flat();

              return; // ❌ Do not continue
            }


            // ✅ Success
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
            return;
          }
          
          this.appliedCoupon = res.data;
          this.discountAmount = res.data.total_discount || 0;
          this.couponTitle = res.data.coupon_title || this.couponCode;
          this.couponType = res.data.value_type || '';
          
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
  }

  // دالة لتطبيق الكوبون تلقائياً عند تغيير القيمة
  onCouponCodeChange(value: string): void {
    // تطبيق الكوبون تلقائياً إذا تم إدخال كود
    if (value && value.trim()) {
      // تطبيق الكوبون تلقائياً بعد تأخير بسيط لتجنب الطلبات المتكررة
      setTimeout(() => {
        if (this.couponCode && this.couponCode.trim() === value.trim()) {
          this.applyCouponForInvoice();
        }
      }, 500);
    }
  }
}
