import {
  Component,
  OnInit,
  OnDestroy,
  ViewChild,
  ElementRef,
  TemplateRef,
  ChangeDetectorRef,
} from '@angular/core';
import { PillDetailsService } from '../services/pill-details.service';
import { CommonModule, DecimalPipe } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { DatePipe } from '@angular/common';
import { PrintedInvoiceService } from '../services/printed-invoice.service';
import { Router } from '@angular/router';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { IndexeddbService } from '../services/indexeddb.service';
import { SyncService } from '../services/sync.service';
import { takeUntil } from 'rxjs/operators';
import { Subject } from 'rxjs';
declare var bootstrap: any;
import { FormsModule } from '@angular/forms';
import { ConfirmDialogComponent } from "../shared/ui/component/confirm-dialog/confirm-dialog.component";
import { finalize } from 'rxjs';

@Component({
  selector: 'app-pill-edit',
  imports: [CommonModule, DecimalPipe, FormsModule, ConfirmDialogComponent],
  templateUrl: './pill-edit.component.html',
  styleUrl: './pill-edit.component.css',
  providers: [DatePipe],
})
export class PillEditComponent implements OnInit, OnDestroy {
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
  couponType: any;
  cashier_machine_id!: number;
  showPrices?: boolean;
  test?: boolean;
  paymentMethod: any;
  cash_value: number | null = null;
  credit_value: number | null = null;
  amountError: boolean = false;
  Delivery_show_delivered_only: boolean = false;
  isPrinting = false;

  selectedPaymentSuggestion: number | null = null;
  // hanan front
  tip_aption: any;
  tip: any;
  referenceNumber: string = '';

  // selectedPaymentMethod: 'cash' | 'credit' | 'cash + credit' | null = null;
  tipPaymentStatus: 'paid' | 'unpaid' = 'unpaid'; // حالة دفع الإكرامية
  // متغيرات لتخزين البيانات مؤقتاً عند فتح المودال
  tempBillAmount: number = 0;
  tempPaymentAmount: number = 0;
  tempChangeAmount: number = 0;
  // المتغير الجديد لتخزين المبلغ الذي أدخله أو اختاره الكاشير للدفع
  cashPaymentInput: number = 0;
  // المتغيرات الجديدة للدفع المختلط
  cashAmountMixed: number = 0;
  creditAmountMixed: number = 0;
  selectedPaymentMethod: any;
  invoiceTips: any;

  Math = Math;
  finalTipSummary: {
    total: number; // المجموع قبل رسوم الخدمة
    serviceFee: number; // رسوم الخدمة
    billAmount: number; // المجموع الفرعي (المبلغ المستحق للدفع)
    paymentAmount: number; // قيمة الدفع الفعلية
    paymentMethod: string; // طريقة الدفع (كاش/فيزا/مختلط)
    tipAmount: number; // الإكرامية المعتمدة
    grandTotalWithTip: number; // المجموع الكلي مع الإكرامية
    changeToReturn: number; // المتبقي للرد
    cashAmountMixed?: number; // المبلغ المدفوع كاش في الدفع المختلط
    creditAmountMixed?: number; // المبلغ المدفوع فيزا في الدفع المختلط
  } | null = null;
  // متغيرات لإدارة خيارات الإكرامية داخل المودال
  selectedTipType: 'tip_the_change' | 'tip_specific_amount' | 'no_tip' = 'no_tip';
  specificTipAmount: number = 0; // المبلغ الذي يتم إدخاله يدوياً كإكرامية
  selectedSuggestionType: 'billAmount' | 'amount50' | 'amount100' | null = null; // متغير جديد لتخزين نوع الاقتراح

  // Network status and sync
  isOnline: boolean = navigator.onLine;
  private destroy$ = new Subject<void>();

  constructor(
    private pillDetailsService: PillDetailsService,
    private route: ActivatedRoute,
    private orderService: PillDetailsService,
    private cdr: ChangeDetectorRef,
    private datePipe: DatePipe,
    private printedInvoiceService: PrintedInvoiceService,
    private modalService: NgbModal,
    private dbService: IndexeddbService,
    private syncService: SyncService,
    private router: Router
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
    // Subscribe to invoice sync trigger from sync service
    this.syncService.retryInvoices$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        console.log('🔄 Invoice sync triggered from sync service');
        // Sync service already handles the sync, but you can add component-specific logic here if needed
      });

    // If already online, try to sync any pending invoice updates
    if (this.isOnline) {
      this.syncService.syncPendingInvoiceUpdates();
    }

    this.route.paramMap.subscribe((params) => {
      this.pillId = params.get('id');

      if (this.pillId) {
        // this.fetchPillsDetails(this.pillId);
        if (!navigator.onLine) {
          console.log("offline");
          this.fetchPillFromIndexedDB(this.pillId);
        } else {
          this.fetchPillsDetails(this.pillId);
        }
      }
    });
    this.fetchPillsDetails(this.pillId);

    this.fetchTrackingStatus();
    // this.getNoteFromLocalStorage();
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
        this.invoiceTips = response.data.invoice_tips || [];

        console.log(this.invoices[0].order_type);
        this.totalll = this.invoices[0].invoice_summary.total_price
        this.orderType = this.invoices[0].order_type;

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
        /*  if (trackingKey === 'completed') {
           this.isShow = false;
         } */
        this.trackingStatus = statusMap[trackingKey] || trackingKey;
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
  async fetchPillFromIndexedDB(identifier: string | number) {
    try {
      const pill = await this.dbService.getPillByInvoiceId(identifier);

      console.log("pill_offline",pill);
      if (pill) {
        console.log("Loaded pill from IndexedDB ✅");
        this.processPillDetails(pill);
      } else {
        console.log('Pill not found in IndexedDB, fallback to API');
        this.fetchPillsDetails(String(identifier)); // ✅ fetch online
      }
    } catch (error) {
      console.error('Error retrieving pill from IndexedDB:', error);
      this.fetchPillsDetails(String(identifier));  // ✅ fetch online
    }
  }


  private processPillDetails(data: any): void {

    console.log("processPillDetails",data);
    try {
      this.order_id = data.order_id;

      // Convert invoice_details to array (handle both API format 'invoices' and IndexedDB format 'invoice_details')
      if (data.invoices && Array.isArray(data.invoices)) {
        this.invoices = data.invoices;
      } else if (data.invoice_details && Array.isArray(data.invoice_details)) {
        this.invoices = data.invoice_details;
      } else if (data.invoice_details) {
        // If invoice_details exists but is not an array, convert it
        this.invoices = Array.isArray(data.invoice_details) ? data.invoice_details : [data.invoice_details];
      } else {
        this.invoices = [];
      }

      console.log("this.invoices", this.invoices);

      if (!this.invoices || !this.invoices.length) {
        console.warn("No invoices found in pill data:", data);
        return;
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
      this.orderNumber = data.order_id;
      this.couponType = this.invoices[0]?.invoice_summary?.coupon_type;

      this.addresDetails = this.invoices[0]?.address_details || {};
      this.paymentMethod = this.invoices[0]?.transactions?.[0]?.['payment_method'];
      this.paymentStatus = this.invoices[0]?.transactions?.[0]?.['payment_status'];

      this.isDeliveryOrder = this.invoices.some(
        (invoice: any) => invoice.order_type === 'Delivery'
      );

      this.branchDetails = this.invoices.map(
        (e: { branch_details: any }) => e.branch_details
      );
      this.orderDetails = this.invoices.map((e: any) => e.orderDetails);

      this.invoiceSummary = this.invoices.map((e: any) => ({
        ...e.invoice_summary,
        currency_symbol: e.currency_symbol,
      }));

      this.addressDetails = this.invoices.map((e: any) => e.address_details);

      if (this.branchDetails?.length) {
        this.extractDateAndTime(this.branchDetails[0]);
      }

      // Convert invoice_tips to array
      this.invoiceTips = Array.isArray(data.invoice_tips) ? data.invoice_tips : (data.invoice_tips ? [data.invoice_tips] : []);

      console.log(" this.invoiceTips ", this.invoiceTips);

      // Trigger change detection to update template
      this.cdr.detectChanges();
    } catch (error) {
      console.error("Error processing pill details offline:", error, data);
    }
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

  // saveOrder() {
  //   console.log('pa', this.paymentStatus);

  //   if (!this.paymentStatus && this.trackingStatus !== 'on_way') {
  //     alert('يجب تحديد حالة الدفع  قبل الحفظ!');
  //     if (
  //       this.orderType == 'Delivery' && !this.trackingStatus
  //     ) {
  //       alert('يجب تحديد  حالة التوصيل قبل الحفظ!');
  //       return;
  //     }
  //     else if (this.orderType == 'Delivery' && this.trackingStatus == 'delivered') {
  //       if (!this.paymentStatus) {
  //         alert('يجب تحديد حالة الدفع  قبل الحفظ!');
  //         return;
  //       }
  //     } else if (this.orderType == 'Delivery' && this.trackingStatus == 'on_way') {
  //       if (!this.paymentStatus) {
  //         alert('يجب تحديد حالة الدفع  قبل الحفظ!');
  //         return;
  //       }
  //     }
  //     else {
  //       if (!this.paymentStatus) {
  //         return;
  //       }
  //     }
  //     return
  //   }

  //   this.amountError = false;

  //   if (this.paymentStatus === 'paid' && !this.isPaymentAmountValid() && this.orderType !== 'Delivery') {
  //     this.amountError = true;

  //   }
  //   const cashAmount = this.selectedPaymentMethod === "cash" ? this.finalTipSummary?.billAmount ?? 0 : 0;
  //   const creditAmount = this.selectedPaymentMethod === "credit" ? this.finalTipSummary?.billAmount ?? 0 : 0;
  //   if (this.orderType == 'Delivery') {
  //     this.DeliveredOrNot = true;
  //   } else {
  //     this.DeliveredOrNot = false;
  //   }
  //   this.tip =
  //   {
  //     change_amount: this.tempChangeAmount || 0,
  //     // tips_aption : this.selectedTipType ?? "tip_the_change" ,                  //'tip_the_change', 'tip_specific_amount','no_tip'
  //     tips_aption: this.tip_aption ?? "tip_the_change",                  //'tip_the_change', 'tip_specific_amount','no_tip'
  //     tip_amount: this.finalTipSummary?.tipAmount ?? 0,
  //     tip_specific_amount: this.specificTipAmount ? this.finalTipSummary?.tipAmount : 0,
  //     payment_amount: this.finalTipSummary?.paymentAmount ?? 0,
  //     bill_amount: this.finalTipSummary?.billAmount ?? 0,
  //     total_with_tip: (this.finalTipSummary?.tipAmount ?? 0) + (this.finalTipSummary?.billAmount ?? 0),
  //     returned_amount: this.finalTipSummary?.changeToReturn ?? 0
  //   }

  //   console.log("this.tip", this.tip);
  //   console.log(cashAmount,
  //     creditAmount)
  //   if (this.amountError == false && this.loading == false) {
  //     this.loading = true
  //     this.orderService
  //       .updateInvoiceStatus(
  //         this.orderNumber,
  //         this.paymentStatus,
  //         this.trackingStatus,
  //         cashAmount,
  //         creditAmount,
  //         this.DeliveredOrNot, this.totalll,this.tip,
  //       ).pipe(finalize(() => this.loading = false))
  //       .subscribe({
  //         next: (response) => {
  //           if (response.status === false && response.message) {
  //             this.errr = response.message
  //           }
  //           if (response.status === false || response.errorData) {
  //             // Handle validation or logical API errors
  //             this.apiErrors = Object.values(
  //               response.errorData as { [key: string]: string[] }
  //             ).flat();

  //             return; // ❌ Do not continue
  //           }


  //           // ✅ Success
  //           this.apiErrors = [];
  //           localStorage.removeItem('cash_value')
  //           localStorage.removeItem('credit_value')
  //           localStorage.setItem(
  //             'pill_detail_data',
  //             JSON.stringify(response.data)
  //           );
  //           this.showSuccessPillEditModal();
  //           this.fetchPillsDetails(this.pillId);
  //           window.location.reload();
  //         },
  //         error: (err) => {
  //           console.error('خطأ في حفظ الطلب:', err);
  //           this.apiErrors = ['حدث خطأ أثناء الاتصال بالخادم.'];
  //         },
  //       });
  //   }
  // }
  async saveOrder() {
    console.log('pa', this.paymentStatus);
    console.log("this.orderNumber,", this.orderNumber);

    if (!this.paymentStatus && this.trackingStatus !== 'on_way') {
      alert('يجب تحديد حالة الدفع قبل الحفظ!');
      return;
    }

    this.amountError = false;
    if (this.paymentStatus === 'paid' && !this.isPaymentAmountValid() && this.orderType !== 'Delivery') {
      this.amountError = true;
    }

    //  const cashAmount= this.cash_value != null ? this.cash_value : 0;
    // const creditAmount = this.credit_value != null ? this.credit_value : 0;
    if ((this.selectedPaymentMethod === 'credit' || (this.selectedPaymentMethod === 'cash + credit' && this.creditAmountMixed > 0))
      && (!this.referenceNumber || this.referenceNumber.trim() === '')) {
      alert('رقم المرجع مطلوب للدفع بالفيزا');
      return;
    }

    // const cashAmount = this.selectedPaymentMethod === "cash" ? this.finalTipSummary?.billAmount ?? 0 : 0;
    // const creditAmount = this.selectedPaymentMethod === "credit" ? this.finalTipSummary?.billAmount ?? 0 : 0;
    let cashAmount = 0;
    let creditAmount = 0;
    if (this.selectedPaymentMethod === 'cash') {
      cashAmount = this.finalTipSummary?.billAmount ?? this.getInvoiceTotal();
      creditAmount = 0; // تأكدي من إرسال 0 وليس null
    } else if (this.selectedPaymentMethod === 'credit') {
      cashAmount = 0; // تأكدي من إرسال 0 وليس null
      creditAmount = this.finalTipSummary?.billAmount ?? this.getInvoiceTotal();
    } else if (this.selectedPaymentMethod === 'cash + credit') {
      cashAmount = this.cashAmountMixed || 0;
      creditAmount = this.creditAmountMixed || 0;
    }

    // تأكدي من أن القيم ليست null
    cashAmount = cashAmount || 0;
    creditAmount = creditAmount || 0;

    console.log('Cash Amount:', cashAmount, 'Credit Amount:', creditAmount);
    this.DeliveredOrNot = this.orderType == 'Delivery';
    console.log("DD");

    // if (this.amountError) return;
    console.log("DDd");
    // dalia start tips
    // tip_amount: this.tipAmount || 0,


    this.tip =
    {
      change_amount: this.tempChangeAmount || 0,
      // tips_aption : this.selectedTipType ?? "tip_the_change" ,                  //'tip_the_change', 'tip_specific_amount','no_tip'
      tips_aption: this.tip_aption ?? "tip_the_change",                  //'tip_the_change', 'tip_specific_amount','no_tip'
      tip_amount: this.finalTipSummary?.tipAmount ?? 0,
      tip_specific_amount: this.specificTipAmount ? this.finalTipSummary?.tipAmount : 0,
      payment_amount: this.finalTipSummary?.paymentAmount ?? 0,
      bill_amount: this.finalTipSummary?.billAmount ?? 0,
      total_with_tip: (this.finalTipSummary?.tipAmount ?? 0) + (this.finalTipSummary?.billAmount ?? 0),
      returned_amount: this.finalTipSummary?.changeToReturn ?? 0
    }

    console.log("this.tip", this.tip);
    // dalia end tips

    // ✅ Online Mode
    if (navigator.onLine) {
    this.loading = true;
    this.orderService.updateInvoiceStatus(
      this.orderNumber,
      this.paymentStatus,
      this.trackingStatus,
      cashAmount,
      creditAmount,
      this.DeliveredOrNot,
      this.totalll,
      this.tip,
      this.referenceNumber,
    ).pipe(finalize(() => this.loading = false))
      .subscribe({
        next: (response) => {
          console.log("response", response);
          if (response.status === false || response.errorData) {
            this.apiErrors = Object.values(response.errorData || {})
              .flat()
              .map(err => String(err));
            console.error('API Error Details:', response);
            return;
          }

          // ✅ Success
          this.apiErrors = [];
          localStorage.removeItem('cash_value');
          localStorage.removeItem('credit_value');
          localStorage.setItem('pill_detail_data', JSON.stringify(response.data));

          this.showSuccessPillEditModal();
          this.fetchPillsDetails(this.pillId);
          // window.location.reload(); ❌ مش ضروري
        },
        error: (err) => {
          console.error('خطأ في حفظ الطلب:', err);
          this.apiErrors = ['حدث خطأ أثناء الاتصال بالخادم.'];
        },
      });

    }
    else {
      // ✅ Offline Mode → Update in IndexedDB
      try {
        console.log("📴 Offline mode: Updating pill in IndexedDB");

        // Helper function to update pill
        const updatePill = async (existingPill: any) => {
          // Handle offline-created invoices (INV-OFF-*)
          if (existingPill.invoice_number === `INV-OFF-${this.pillId}`) {
            // Save order data for sync when online (for offline-created invoices)
            // Use the same order_id that was used when the order was initially created
            const orderId = existingPill.order_id || existingPill.invoice_id || this.pillId;
            const orderData = {
              orderId: orderId,
              cash_amount: cashAmount,
              credit_amount: creditAmount,
              tip: this.tip,
              edit_invoice: true
            };
            console.log("📝 Updating existing offline order for sync, orderId:", orderId);
            await this.dbService.savePendingOrderForSync(orderData);
            // console.log("💾 Order data saved for sync when online:", orderData);
          }

          // Update main pill fields
          existingPill.payment_status = this.paymentStatus;
          existingPill.tracking_status = this.trackingStatus;
          existingPill.cash_value = cashAmount;
          existingPill.credit_value = creditAmount;
          existingPill.isUpdatedOffline = true;
          existingPill.isSynced = false;

          // Update invoice tips
          if (this.tip) {
            existingPill.invoice_tips = Array.isArray(existingPill.invoice_tips)
              ? existingPill.invoice_tips.map((tip: any, index: number) =>
                  index === 0 ? { ...tip, ...this.tip } : tip
                )
              : [this.tip];
          }

          // Update inside invoice_details
          if (existingPill.invoice_details && Array.isArray(existingPill.invoice_details) && existingPill.invoice_details.length > 0) {
            const invoiceDetail = existingPill.invoice_details[0];

            // Update transactions
            if (invoiceDetail.transactions && Array.isArray(invoiceDetail.transactions) && invoiceDetail.transactions.length > 0) {
              invoiceDetail.transactions[0].payment_status = this.paymentStatus;
              invoiceDetail.transactions[0].payment_method =
                this.paymentStatus === "paid"
                  ? (cashAmount > 0 ? "cash" : (creditAmount > 0 ? "credit" : "cash"))
                  : "unpaid";
              invoiceDetail.transactions[0].paid = cashAmount + creditAmount;
            }

            // Update invoice summary
            if (invoiceDetail.invoice_summary) {
              invoiceDetail.invoice_summary.total_price = this.totalll;
            }
          }

          // Special handling for offline-created invoices
          if (existingPill.invoice_number === `INV-OFF-${this.pillId}`) {
            existingPill.isUpdatedOffline = false; // Don't mark as updated if it's an offline-created invoice
          }

          // Save updated pill
          await this.dbService.updatePill(existingPill);
          console.log("💾 Pill updated offline in IndexedDB:", existingPill);
        };

        // Helper function to save invoice update data
        const saveInvoiceUpdate = async (existingPill?: any) => {
          const invoiceUpdateData = {
            orderNumber: this.orderNumber,
            paymentStatus: this.paymentStatus,
            trackingStatus: this.trackingStatus,
            cashAmount: cashAmount,
            creditAmount: creditAmount,
            DeliveredOrNot: this.DeliveredOrNot,
            totalll: this.totalll,
            tip: this.tip,
            referenceNumber: this.referenceNumber,
            pillId: this.pillId
          };

          // Only save invoice update if it's NOT an offline-created invoice (INV-OFF-*)
          // For INV-OFF invoices, we use savePendingOrderForSync instead
          if (!existingPill || existingPill.invoice_number !== `INV-OFF-${this.pillId}`) {
            await this.dbService.savePendingInvoiceUpdate(invoiceUpdateData);
            console.log("💾 Invoice update saved for sync when online");
          } else {
            console.log("⏭️ Skipping invoice update save for INV-OFF invoice (handled by savePendingOrderForSync)");
          }
        };

        // 1️⃣ Try to get order first (for pending orders)
        const order: any = await this.dbService.getOrderById(this.pillId);

        if (order) {
          console.log("✅ Order found in IndexedDB, updating order");

          // Handle offline orders (need to update pill instead)
          if (order.isOffline === true) {
            const existingPill: any = await this.dbService.getPillByInvoiceId(this.pillId);
            if (existingPill) {
              console.log("✅ Pill found in IndexedDB, updating pill");
              await updatePill(existingPill);
              await saveInvoiceUpdate(existingPill);
              alert("تم تحديث الفاتورة Offline وسيتم رفعها عند الاتصال بالإنترنت ✅");
              return;
            }
          } else {
            // Update regular order details
            if (order.order_details) {
              order.order_details.payment_method = cashAmount > 0 ? 'cash' : (creditAmount > 0 ? 'credit' : order.order_details.payment_method);
              order.order_details.payment_status = this.paymentStatus;
              order.order_details.cash_amount = cashAmount || 0;
              order.order_details.credit_amount = creditAmount || 0;
            }

            // Update tip information
            if (this.tip) {
              order.tip_amount = this.tip.tip_amount || 0;
              order.tip_specific_amount = this.tip.tip_specific_amount || 0;
              order.payment_amount = this.tip.payment_amount || 0;
              order.bill_amount = this.tip.bill_amount || 0;
              order.total_with_tip = this.tip.total_with_tip || 0;
              order.returned_amount = this.tip.returned_amount || 0;
              order.change_amount = this.tip.change_amount || 0;
              order.tips_aption = this.tip.tips_aption || "tip_the_change";
            }

            // Update status
            order.isUpdatedOffline = true;
            order.isSynced = false;

            // Save updated order
            await this.dbService.savePendingOrder(order);
            await saveInvoiceUpdate(); // No existingPill for regular orders

            console.log("💾 Order updated offline:", order);
            alert("تم تحديث الطلب Offline ✅");
            return;
          }
        }

        // 2️⃣ If no order found, try to get pill
        console.log("⚠️ Order not found, trying to get pill");
        const existingPill: any = await this.dbService.getPillByInvoiceId(this.pillId);

        if (existingPill) {
          console.log("✅ Pill found in IndexedDB, updating pill");
          await updatePill(existingPill);
          await saveInvoiceUpdate(existingPill);
          alert("تم تحديث الفاتورة Offline وسيتم رفعها عند الاتصال بالإنترنت ✅");
        } else {
          console.warn("❌ لم يتم العثور على الفاتورة أو الطلب في IndexedDB");
          alert("⚠️ لم يتم العثور على الفاتورة في قاعدة البيانات المحلية");
        }
      } catch (err) {
        console.error("❌ Error updating offline order/pill:", err);
        alert("❌ حدث خطأ    تحديث الفاتورة Offline");
      }
    }
  }

  ngOnDestroy(): void {
    // Clean up subscriptions
    this.destroy$.next();
    this.destroy$.complete();
  }
  isFinal: boolean = false;
  // async printInvoice(isfinal: boolean) {
  //   this.isFinal = isfinal
  //   if (!this.invoices?.length || !this.invoiceSummary?.length) {
  //     console.warn('Invoice data not ready.');
  //     return;
  //   }

  //   try {
  //     const response = await this.printedInvoiceService
  //       .printInvoice(this.orderNumber, this.cashier_machine_id, this.paymentMethod)
  //       .toPromise();
  //     console.log(response, 'testttttt')
  //     console.log('Print invoice response:', response);
  //     const printContent = document.getElementById('printSection');
  //     if (!printContent) {
  //       console.error('Print section not found.');
  //       return;
  //     }

  //     const originalHTML = document.body.innerHTML;

  //     const copies = this.isDeliveryOrder
  //       ? [
  //         { showPrices: true, test: true },
  //         { showPrices: false, test: false },
  //         { showPrices: true, test: true },
  //       ]
  //       : [
  //         { showPrices: true, test: true },
  //         { showPrices: false, test: false },
  //       ];

  //     for (let i = 0; i < copies.length; i++) {
  //       this.showPrices = copies[i].showPrices;
  //       this.test = copies[i].test;
  //       await new Promise((resolve) => setTimeout(resolve, 300));

  //       const singlePageHTML = `
  //       <div>
  //         ${printContent.innerHTML}
  //       </div>
  //     `;

  //       document.body.innerHTML = singlePageHTML;

  //       await new Promise((resolve) =>
  //         setTimeout(() => {
  //           window.print();
  //           resolve(true);
  //         }, 200)
  //       );
  //     }

  //     document.body.innerHTML = originalHTML;
  //     location.reload();
  //   } catch (error) {
  //     console.error('Error printing invoice:', error);
  //   }
  // }
  async printInvoice(isfinal: boolean) {
    console.log('جاري طباعة الفاتورة...');
    this.isFinal = isfinal;
    this.isPrinting = true;
    // تأكد من أن بيانات الإكرامية جاهزة
    if (this.invoiceTips && this.invoiceTips.length > 0) {
      console.log('بيانات الإكرامية متاحة للطباعة:', this.invoiceTips);
    }

    if (this.finalTipSummary) {
      console.log('بيانات الإكرامية النهائية متاحة:', this.finalTipSummary);
    }
    // إغلاق الـ modal فورًا بعد بدء الطباعة
    this.closeConfirmationDialog();
    if (!this.invoices?.length || !this.invoiceSummary?.length) {
      console.warn('بيانات الفاتورة غير جاهزة.');
      // محاولة تحميل البيانات من التخزين المحلي
      // if (!this.isOnline) {
      //   await this.fetchPillFromIndexedDB(this.pillId);
      // }
      if (!this.invoices?.length) {
        alert('بيانات الفاتورة غير متوفرة للطباعة.');
        this.isPrinting = false;
        return;
      }
    }
    try {
      // إذا كان هناك اتصال، محاولة الطباعة عبر الخدمة
      // if (this.isOnline) {
      //   try {
      //     const response = await this.printedInvoiceService
      //       .printInvoice(this.orderNumber, this.cashier_machine_id, this.paymentMethod)
      //       .toPromise();
      //     console.log('استجابة طباعة الفاتورة:', response);
      //   } catch (onlineError) {
      //     console.warn('فشل الطباعة عبر الخدمة، الانتقال للطباعة المحلية:', onlineError);
      //   }
      // } else {
      //   console.log('الطباعة في وضع عدم الاتصال');
      // }
      // الطباعة المحلية
      await this.performLocalPrint();
    } catch (error) {
      console.error('خطأ في طباعة الفاتورة:', error);
      // في حالة الخطأ، حاولي الطباعة محلياً فقط
      await this.performLocalPrint();
    } finally {
      this.isPrinting = false;
      // التأكد من إغلاق الـ modal نهائيًا
      this.closeConfirmationDialog();
    }
  }
  // دورة الطباعة المحلية
  // private async performLocalPrint(): Promise<void> {
  //   const printContent = document.getElementById('printSectionn');
  //   if (!printContent) {
  //     console.error('قسم الطباعة غير موجود.');
  //     return;
  //   }
  //   const originalHTML = document.body.innerHTML;
  //   const copies = this.isDeliveryOrder
  //     ? [
  //       { showPrices: true, test: true },
  //       { showPrices: false, test: false },
  //       { showPrices: true, test: true },
  //     ]
  //     : [
  //       { showPrices: true, test: true },
  //       { showPrices: false, test: false },
  //     ];
  //   for (let i = 0; i < copies.length; i++) {
  //     this.showPrices = copies[i].showPrices;
  //     this.test = copies[i].test;
  //     await new Promise((resolve) => setTimeout(resolve, 300));
  //     // const singlePageHTML = `
  //     //   <div>
  //     //     ${printContent.innerHTML}
  //     //     ${!this.isOnline ? '<div style="text-align: center; color: red; margin-top: 10px;">:red_circle: طباعة محلية - غير متصل بالإنترنت</div>' : ''}
  //     //   </div>
  //     // `;
  //     // document.body.innerHTML = singlePageHTML;
  //     await new Promise((resolve) =>
  //       setTimeout(() => {
  //         window.print();
  //         resolve(true);
  //       }, 200)
  //     );
  //   }
  //   document.body.innerHTML = originalHTML;
  //   // إعادة تحميل الصفحة فقط إذا كان هناك اتصال
  //   // if (this.isOnline) {
  //   //   location.reload();
  //   // }
  // }
  private async performLocalPrint(): Promise<void> {
  const printContent = document.getElementById('printSectionn');
  if (!printContent) {
    console.error('قسم الطباعة غير موجود.');
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

    // 👉 IMPORTANT: replace whole body with only the print section
    document.body.innerHTML = `
      <div>
        ${printContent.innerHTML}
      </div>
    `;

    await new Promise((resolve) =>
      setTimeout(() => {
        window.print();
        resolve(true);
      }, 200)
    );
  }

  // 👉 Restore the full page after printing
  document.body.innerHTML = originalHTML;
}

  private closeConfirmationDialog(): void {
    if (this.confirmationDialog) {
      // إغلاق الـ modal يدويًا
      const modalElement = document.querySelector('.p-dialog-mask');
      if (modalElement) {
        modalElement.remove();
      }
      // إزالة class الـ backdrop إذا كان موجودًا
      const backdropElement = document.querySelector('.p-component-overlay');
      if (backdropElement) {
        backdropElement.remove();
      }
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

  // hanan frontend

  selectPaymentMethod(method: 'cash' | 'credit' | 'cash + credit'): void {
    this.selectedPaymentMethod = method;

    // إعادة تعيين القيم عند تغيير طريقة الدفع
    if (method === 'cash') {
      this.cashAmountMixed = 0;
      this.creditAmountMixed = 0;
    } else if (method === 'credit') {
      this.cashAmountMixed = 0;
      this.creditAmountMixed = 0;
      this.cashPaymentInput = 0;
      // فتح مودال الإكرامية مباشرة للفيزا
      // const billAmount = this.getInvoiceTotal();
      // this.openTipModal(this.tipModalContent, billAmount, billAmount);
    } else if (method === 'cash + credit') {
      this.cashPaymentInput = 0;
      // تعيين القيم الافتراضية للدفع المختلط
      const billAmount = this.getInvoiceTotal();
      this.cashAmountMixed = billAmount / 2;
      this.creditAmountMixed = billAmount / 2;
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

    this.selectedTipType = 'no_tip';
    this.specificTipAmount = 0;

    this.modalService.open(content, {
      centered: true,
      size: 'md'
    }).result.then((result) => {
      console.log('Tip Modal Closed with final result:', result);
    }, (reason) => {
      console.log('Tip Modal Dismissed:', reason);
    });
  }

  selectTipOption(type: 'tip_the_change' | 'tip_specific_amount' | 'no_tip'): void {
    this.selectedTipType = type;
    this.tip_aption = type;

    switch (type) {
      case 'tip_the_change':
        // إذا اختار العميل إكرامية الباقي بالكامل
        this.specificTipAmount = this.tempChangeAmount;
        break;
      case 'no_tip':
        // إذا اختار العميل لا إكرامية
        this.specificTipAmount = 0;
        break;
      case 'tip_specific_amount':
        // لا نفعل شيئًا عند الاختيار، فقط نُهيئ القيمة للقيمة الزائدة
        // يمكن إعادة تعيينها إلى المبلغ الزائد كنقطة بداية
        this.specificTipAmount = this.tempChangeAmount > 0 ? this.tempChangeAmount : 0;
        break;
    }
  }

  // تحديث دالة تأكيد الإكرامية
  confirmTipAndClose(modal: any): void {
    let finalTipAmount: number = 0;

    if (this.selectedTipType === 'tip_the_change') {
      finalTipAmount = this.tempChangeAmount;
    } else if (this.selectedTipType === 'tip_specific_amount') {
      finalTipAmount = Math.max(0, this.specificTipAmount);
    }

    const changeToReturn = Math.max(0, this.tempChangeAmount - finalTipAmount);

    // حساب المبالغ النهائية بناءً على طريقة الدفع
    let cashFinal = 0;
    let creditFinal = 0;

    if (this.selectedPaymentMethod === 'cash') {
      cashFinal = this.tempPaymentAmount;
    } else if (this.selectedPaymentMethod === 'credit') {
      creditFinal = this.tempPaymentAmount;
    } else if (this.selectedPaymentMethod === 'cash + credit') {
      // توزيع المبلغ على الكاش والفيزا مع احتساب الإكرامية
      const totalPaid = this.cashAmountMixed + this.creditAmountMixed;

      if (totalPaid > 0) {
        const cashRatio = this.cashAmountMixed / totalPaid;
        const creditRatio = this.creditAmountMixed / totalPaid;

        const totalWithTip = this.tempBillAmount + finalTipAmount;

        // إذا كان المبلغ المدفوع أكبر من المستحق + الإكرامية
        if (totalPaid >= totalWithTip) {
          cashFinal = totalWithTip * cashRatio;
          creditFinal = totalWithTip * creditRatio;
        } else {
          // إذا كان المبلغ المدفوع أقل، نستخدم المبالغ المدخلة كما هي
          cashFinal = this.cashAmountMixed;
          creditFinal = this.creditAmountMixed;
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
      // إضافة المبالغ التفصيلية للدفع المختلط
      cashAmountMixed: cashFinal,
      creditAmountMixed: creditFinal
    };

    modal.close({
      tipAmount: finalTipAmount,
      changeToReturn: changeToReturn,
      cashAmount: cashFinal,
      creditAmount: creditFinal,
      paymentMethod: this.selectedPaymentMethod,
      tipPaymentStatus: this.tipPaymentStatus // إضافة حالة دفع الإكرامية
    });

    // إعادة تعيين المتغيرات
    this.selectedTipType = 'no_tip';
    this.specificTipAmount = 0;
    this.cashAmountMixed = this.cashAmountMixed; // ابقى كما هو
    this.creditAmountMixed = this.creditAmountMixed;

    // إعادة تعيين cashPaymentInput فقط إذا كان مستخدم
    if (this.selectedPaymentMethod === 'cash' || this.selectedPaymentMethod === 'credit') {
      this.cashPaymentInput = 0;
    }
  }

  getChangeToReturn(changeAmount: number, tipAmount: number): number {
    return Math.max(0, changeAmount - tipAmount);
  }
  // تغيير حالة دفع الإكرامية
  changeTipPaymentStatus(status: 'paid' | 'unpaid'): void {
    this.tipPaymentStatus = status;
    console.log('حالة دفع الإكرامية:', this.tipPaymentStatus);
  }

  selectPaymentSuggestionAndOpenModal(type: 'billAmount' | 'amount50' | 'amount100', billAmount: number, paymentAmount: number, modalContent: any): void {
    this.selectedSuggestionType = type; // هنا يتم حفظ النوع الذي تم الضغط عليه
    this.selectedPaymentSuggestion = paymentAmount;

    if (paymentAmount >= billAmount) {
      this.cashPaymentInput = paymentAmount;
      this.openTipModal(modalContent, billAmount, paymentAmount);
    }
  }

  handleManualPaymentBlur(billAmount: number, modalContent: any): void {
    this.selectedPaymentSuggestion = null; // إعادة تعيين عند الإدخال اليدوي

    console.log('Bill Amount:', billAmount, 'Entered:', this.cashPaymentInput);
    const currentPaymentInput = this.cashPaymentInput;
    if (currentPaymentInput > 0 && currentPaymentInput >= billAmount) {
      this.openTipModal(modalContent, billAmount, currentPaymentInput);
    }
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
      console.warn('المبلغ المدفوع غير كافي لفتح مودال الإكرامية');
    }
  }

  // التحقق إذا كان المبلغ المدفوع كافي
  isPaymentSufficient(billAmount: number): boolean {
    return this.getRemainingAmount(billAmount) <= 0;
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
