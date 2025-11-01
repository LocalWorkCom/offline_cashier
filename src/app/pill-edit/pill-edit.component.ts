import {
  Component,
  OnInit,
  OnDestroy,
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
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { IndexeddbService } from '../services/indexeddb.service';

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

  selectedPaymentSuggestion: number | null = null;
  // hanan front
  tip_aption: any;
  tip: any;

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


  // Tip modal variables
  // selectedTipType: 'tip_the_change' | 'tip_specific_amount' | 'no_tip' = 'no_tip';
  // specificTipAmount: number = 0;

  finalTipSummary: {
    total: number;
    serviceFee: number;
    billAmount: number;
    paymentAmount: number;
    paymentMethod: string;
    tipAmount: number;
    grandTotalWithTip: number;
    changeToReturn: number;
    cashAmountMixed?: number;
    creditAmountMixed?: number;
    additionalPaymentRequired?: number;
    originalPaymentAmount?: number;
  } | null = null;
  selectedTipType: 'tip_the_change' | 'tip_specific_amount' | 'no_tip' = 'no_tip';
  specificTipAmount: number = 0; // المبلغ الذي يتم إدخاله يدوياً كإكرامية
  selectedSuggestionType: 'billAmount' | 'amount50' | 'amount100' | null = null; // متغير جديد لتخزين نوع الاقتراح
  isOnline: boolean = navigator.onLine;
  hasPendingOfflineUpdate: boolean = false;
  pendingUpdateMessage: string = '';
  private onlineHandler = async () => {
    this.isOnline = true;
    this.cdr.detectChanges();
    // Sync pending invoice updates when coming back online
    await this.syncPendingInvoiceUpdates();
  };
  private offlineHandler = () => {
    this.isOnline = false;
    this.cdr.detectChanges();
  };


  constructor(
    private pillDetailsService: PillDetailsService,
    private route: ActivatedRoute,
    private orderService: PillDetailsService,
    private cdr: ChangeDetectorRef,
    private datePipe: DatePipe,
    private printedInvoiceService: PrintedInvoiceService,
    private router: Router,
    private dbService: IndexeddbService,
    private modalService: NgbModal
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
    this.isOnline = navigator.onLine;

    // Listen to online/offline events
    window.addEventListener('online', this.onlineHandler);
    window.addEventListener('offline', this.offlineHandler);

    // Sync pending updates if online on init
    if (this.isOnline) {
      this.syncPendingInvoiceUpdates();
    }

    // checkPendingOfflineUpdate will be called after orderNumber is set

    this.route.paramMap.subscribe((params) => {
      this.pillId = params.get('id');

      if (this.pillId) {
        // this.fetchPillsDetails(this.pillId);
        if (navigator.onLine) {
          // ✅ Online
          this.fetchPillsDetails(this.pillId);
        } else {
          // ✅ Offline
          this.fetchPillFromIndexedDB(this.pillId);
          console.log(this.pillId);

        }
      }
    });
    // this.fetchPillsDetails(this.pillId);

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

  ngOnDestroy(): void {
    // Clean up event listeners
    window.removeEventListener('online', this.onlineHandler);
    window.removeEventListener('offline', this.offlineHandler);
  }

  // Sync pending invoice updates when coming back online
  async syncPendingInvoiceUpdates(): Promise<void> {
    if (!this.isOnline) {
      return;
    }

    try {
      const pendingUpdates = await this.dbService.getPendingInvoiceUpdates();

      if (pendingUpdates.length === 0) {
        console.log('✅ No pending invoice updates to sync');
        return;
      }

      console.log(`🔄 Syncing ${pendingUpdates.length} pending invoice update(s)...`);

      for (const pendingUpdate of pendingUpdates) {
        try {
          const updateData = pendingUpdate;

          await new Promise<void>((resolve, reject) => {
            this.orderService
              .updateInvoiceStatus(
                updateData.orderNumber,
                updateData.paymentStatus,
                updateData.trackingStatus,
                updateData.cashAmount,
                updateData.creditAmount,
                updateData.DeliveredOrNot,
                updateData.totalll,
                updateData.tip
              )
              .subscribe({
                next: (response) => {
                  if (response.status !== false && !response.errorData) {
                    // Mark as synced and delete
                    this.dbService.markPendingInvoiceUpdateAsSynced(pendingUpdate.id)
                      .then(() => this.dbService.deleteSyncedPendingInvoiceUpdate(pendingUpdate.id))
                      .then(() => {
                        console.log(`✅ Successfully synced invoice update for order ${updateData.orderNumber}`);
                        // Clear pending flag if this is the current invoice
                        if (updateData.orderNumber === this.orderNumber) {
                          this.hasPendingOfflineUpdate = false;
                          this.pendingUpdateMessage = '';
                          this.cdr.detectChanges();
                        }
                        resolve();
                      })
                      .catch(reject);
                  } else {
                    console.error(`❌ API returned error for order ${updateData.orderNumber}:`, response);
                    resolve(); // Continue with next update even if this one failed
                  }
                },
                error: (err) => {
                  console.error(`❌ Error syncing invoice update for order ${updateData.orderNumber}:`, err);
                  resolve(); // Continue with next update even if this one failed
                }
              });
          });
        } catch (err) {
          console.error(`❌ Error processing pending update ${pendingUpdate.id}:`, err);
          // Continue with next update
        }
      }

      console.log('✅ Finished syncing all pending invoice updates');

      // Check again if this invoice has pending updates after sync
      this.checkPendingOfflineUpdate();
    } catch (err) {
      console.error('❌ Error in syncPendingInvoiceUpdates:', err);
    }
  }

  // Check if this invoice has a pending offline update
  async checkPendingOfflineUpdate(): Promise<void> {
    try {
      const pendingUpdates = await this.dbService.getPendingInvoiceUpdates();
      const pendingForThisInvoice = pendingUpdates.find(
        (update: any) => update.orderNumber === this.orderNumber
      );

      if (pendingForThisInvoice) {
        this.hasPendingOfflineUpdate = true;
        const statusMessages: { [key: string]: string } = {
          'paid': 'تم دفع هذه الفاتورة',
          'unpaid': 'تم تحديث حالة هذه الفاتورة',
          'delivered': 'تم تحديث حالة التوصيل',
          'on_way': 'تم تحديث حالة التوصيل'
        };
        const statusKey = pendingForThisInvoice.paymentStatus === 'paid' ? 'paid' :
                         (pendingForThisInvoice.trackingStatus ? pendingForThisInvoice.trackingStatus : 'unpaid');
        this.pendingUpdateMessage = `${statusMessages[statusKey] || 'تم تحديث هذه الفاتورة'}، في انتظار الاتصال بالإنترنت للمزامنة`;
      } else {
        this.hasPendingOfflineUpdate = false;
        this.pendingUpdateMessage = '';
      }
      this.cdr.detectChanges();
    } catch (err) {
      console.error('❌ Error checking pending offline update:', err);
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
        this.invoiceTips = response.data.invoice_tips ?? [];
        console.log("invoiceTips", this.invoices);

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

        // Check for pending offline updates after orderNumber is set
        this.checkPendingOfflineUpdate();

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
      console.log("offline-identifier", identifier);
      const pill = await this.dbService.getPillByInvoiceId(identifier);

      console.log("toqa_pills", pill);


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
    console.log("toqa offline", data);

    try {
      this.order_id = data.order_id;

      // ✅ لو جاية Object حطها في Array عشان تبقى زي الـ Online
      this.invoices = Array.isArray(data.invoice_details)
        ? data.invoice_details
        : [data.invoice_details];
      // ✅ إصلاح بيانات الكاشير للطباعة - مباشرة بدون method
      const cashierFullName = localStorage.getItem('fullName') || 'الكاشير';

      this.invoices.forEach((invoice: any) => {
        if (!invoice.cashier_info) {
          invoice.cashier_info = {
            first_name: cashierFullName,
            last_name: ''
          };
        } else {
          // ✅ إذا كانت البيانات موجودة ولكنها غير مكتملة
          if (!invoice.cashier_info.first_name || invoice.cashier_info.first_name === 'test') {
            invoice.cashier_info.first_name = cashierFullName;
          }
          if (!invoice.cashier_info.last_name) {
            invoice.cashier_info.last_name = '';
          }
        }
      });

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

      // Check for pending offline updates after orderNumber is set
      this.checkPendingOfflineUpdate();
      this.paymentMethod =
        this.invoices[0]?.transactions?.[0]?.['payment_method'];
      this.paymentStatus =
        this.invoices[0]?.transactions?.[0]?.['payment_status'];

      this.isDeliveryOrder = this.invoices.some(
        (invoice: any) => invoice.order_type === 'Delivery'
      );

      // ✅ إصلاح: دمج table_number من البيانات الرئيسية مع branch_details
      this.branchDetails = this.invoices.map((e: any) => {
        const branchDetails = e.branch_details || {};

        return {
          ...branchDetails,
          // ✅ استخدم table_number من البيانات الرئيسية إذا لم يكن موجوداً في branch_details
          table_number: branchDetails.table_number || data.table_number || branchDetails.table_id
        };
      });
      this.orderDetails = this.invoices.map((e: any) => {
        if (e.orderDetails && Array.isArray(e.orderDetails)) {
          return e.orderDetails.map((item: any) => ({
            ...item,
            // ✅ تطبيع هيكل الإضافات - هذا هو الجزء المهم!
            addons: this.normalizeAddons(item.addons)
          }));
        }
        return e.orderDetails || [];
      });

      this.invoiceSummary = this.invoices.map((e: any) => ({
        ...e.invoice_summary,
        currency_symbol: e.currency_symbol,
      }));

      this.addressDetails = this.invoices.map((e: any) => e.address_details);

      if (this.branchDetails?.length) {
        this.extractDateAndTime(this.branchDetails[0]);
      }

      this.invoiceTips = data.invoice_tips;

      console.log(
        this.orderNumber,
        this.couponType,
        this.addresDetails,
        this.paymentMethod,
        this.paymentStatus,
        this.isDeliveryOrder,
        this.branchDetails,
        this.invoiceSummary
      );
    } catch (error) {
      console.error('Error processing pill details offline:', error, data);
    }
  }
  private normalizeAddons(addons: any[]): any[] {
    if (!addons || !Array.isArray(addons)) return [];

    return addons.map(addon => {
      // إذا كانت الإضافة object تحتوي على name بدلاً من addon_name
      if (addon && typeof addon === 'object') {
        return {
          addon_name: addon.addon_name || addon.name || 'Unknown Addon',
          addon_price: addon.addon_price || addon.price || 0,
          // احتفظي بالبيانات الأصلية أيضاً
          ...addon
        };
      }
      // إذا كانت string
      else if (typeof addon === 'string') {
        return {
          addon_name: addon,
          addon_price: 0
        };
      }
      return addon;
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
    this.paymentStatus = 'unpaid';

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

    this.amountError = false;

    if (this.paymentStatus === 'paid' && !this.isPaymentAmountValid() && this.orderType !== 'Delivery') {
      this.amountError = false;

    }


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

    // var cashAmount = this.cash_value != null ? this.cash_value : 0;
    // var creditAmount = this.credit_value != null ? this.credit_value : 0;
    const paymentMethodForDB = this.selectedPaymentMethod === 'cash + credit' ? 'cash' : this.selectedPaymentMethod;

    // Calculate cash and credit amounts based on payment method
    let cashAmount = 0;
    let creditAmount = 0;

    if (this.selectedPaymentMethod === 'cash') {
      this.paymentStatus = 'paid';
      cashAmount = this.finalTipSummary?.paymentAmount ?? 0;
    } else if (this.selectedPaymentMethod === 'credit') {
      this.paymentStatus = 'paid';
      creditAmount = this.finalTipSummary?.paymentAmount ?? 0;
    } else if (this.selectedPaymentMethod === 'cash + credit') {
      this.paymentStatus = 'paid';
      // Use the calculated values from finalTipSummary
      cashAmount = this.finalTipSummary?.cashAmountMixed ?? 0;
      creditAmount = this.finalTipSummary?.creditAmountMixed ?? 0;
    }
    if (this.orderType == 'Delivery') {
      this.DeliveredOrNot = true;
    } else {
      this.DeliveredOrNot = false;
    }
    console.log(cashAmount,
      creditAmount)
    if (this.amountError == false && this.loading == false) {
      this.loading = true

      // Check if offline - save to IndexedDB for later sync
      if (!this.isOnline) {
        const invoiceUpdateData = {
          orderNumber: this.orderNumber,
          paymentStatus: this.paymentStatus,
          trackingStatus: this.trackingStatus,
          cashAmount: cashAmount,
          creditAmount: creditAmount,
          DeliveredOrNot: this.DeliveredOrNot,
          totalll: this.totalll,
          tip: this.tip
        };

        this.dbService.savePendingInvoiceUpdate(invoiceUpdateData)
          .then(() => {
            console.log('✅ Invoice update saved to IndexedDB for offline sync');
            this.loading = false;
            this.apiErrors = [];
            localStorage.removeItem('cash_value');
            localStorage.removeItem('credit_value');

            // Set pending update flag and message
            this.hasPendingOfflineUpdate = true;
            const statusMessages: { [key: string]: string } = {
              'paid': 'تم دفع هذه الفاتورة',
              'unpaid': 'تم تحديث حالة هذه الفاتورة',
              'delivered': 'تم تحديث حالة التوصيل',
              'on_way': 'تم تحديث حالة التوصيل'
            };
            const statusKey = this.paymentStatus === 'paid' ? 'paid' :
                             (this.trackingStatus ? this.trackingStatus : 'unpaid');
            this.pendingUpdateMessage = `${statusMessages[statusKey] || 'تم تحديث هذه الفاتورة'}، في انتظار الاتصال بالإنترنت للمزامنة`;
            this.cdr.detectChanges();

            this.showSuccessPillEditModal();
          })
          .catch((err) => {
            console.error('❌ Error saving pending invoice update:', err);
            this.loading = false;
            this.apiErrors = ['حدث خطأ أثناء حفظ التحديثات محلياً.'];
          });
        return;
      }

      // Online - proceed with API call
      this.orderService
        .updateInvoiceStatus(
          this.orderNumber,
          this.paymentStatus,
          this.trackingStatus,
          cashAmount,
          creditAmount,
          this.DeliveredOrNot, this.totalll,
          this.tip
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

  // Tip modal methods
  openTipModal(content: any, billAmount: number, paymentAmount: number, paymentMethod?: string): void {
    this.tempBillAmount = billAmount;
    this.tempPaymentAmount = paymentAmount;
    this.tempChangeAmount = paymentAmount - billAmount;

    if (paymentMethod) {
      this.paymentMethod = paymentMethod;
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
        this.specificTipAmount = parseFloat(initialTipAmount.toFixed(2));
        break;
    }
  }



  showAdditionalPaymentConfirmation(additionalAmount: number, modal: any) {
    const confirmed = confirm(
      `لتحقيق الإكرامية المطلوبة (${this.specificTipAmount} ج.م)، تحتاج لدفع ${additionalAmount} ج.م إضافية.\n\nهل تريد المتابعة؟`
    );

    if (confirmed) {
      modal.close(this.finalTipSummary);
    } else {
      this.tempPaymentAmount = this.finalTipSummary!.originalPaymentAmount!;
      this.finalTipSummary = null;
      this.specificTipAmount = 0;
    }
  }

  // Method to open tip modal when payment status is paid
  openTipModalIfPaid(): void {
    if (this.paymentStatus === 'paid' && this.invoices && this.invoices.length > 0) {
      const billAmount = this.invoices[0].invoice_summary.total_price;
      const paymentAmount = this.cash_value || this.credit_value || billAmount;
      this.openTipModal(this.tipModalContent, billAmount, paymentAmount, this.paymentMethod);
    }
  }

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

    const changeToReturn = Math.max(0, this.tempPaymentAmount - (this.tempBillAmount + finalTipAmount));
    // ✅ التعديل: كاش + فيزا تتحول لـ cash
    const paymentMethodForDB = this.selectedPaymentMethod === 'cash + credit' ? 'cash' : this.selectedPaymentMethod;
    // حساب المبالغ النهائية بناءً على طريقة الدفع
    let cashFinal = 0;
    let creditFinal = 0;

    if (paymentMethodForDB === 'cash') {
      cashFinal = this.tempPaymentAmount;
    } else if (paymentMethodForDB === 'credit') {
      creditFinal = this.tempPaymentAmount;
    } else if (this.selectedPaymentMethod === 'cash + credit') {
      const totalPaid = this.cashAmountMixed + this.creditAmountMixed + additionalPaymentRequired;

      if (totalPaid > 0) {
        const cashRatio = this.cashAmountMixed / (this.cashAmountMixed + this.creditAmountMixed);
        const creditRatio = this.creditAmountMixed / (this.cashAmountMixed + this.creditAmountMixed);

        const totalWithTip = this.tempBillAmount + finalTipAmount;

        cashFinal = totalWithTip * cashRatio;
        creditFinal = totalWithTip * creditRatio;
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
      originalPaymentAmount: originalPaymentAmount
    };

    // ✅ إذا كان هناك مبلغ إضافي مطلوب، نعرض تأكيد للمستخدم
    if (additionalPaymentRequired > 0) {
      this.showAdditionalPaymentConfirmation(additionalPaymentRequired, modal);
    } else {
      modal.close(this.finalTipSummary);
    }

    // إعادة تعيين المتغيرات
    this.selectedTipType = 'no_tip';
    this.specificTipAmount = 0;
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
      const paymentMethodForModal = this.selectedPaymentMethod === 'cash + credit' ? 'cash' : this.selectedPaymentMethod;
      this.openTipModal(modalContent, billAmount, paymentAmount, paymentMethodForModal);
    }
  }
  handleManualPaymentBlur(billAmount: number, modalContent: any): void {
    this.selectedPaymentSuggestion = null; // إعادة تعيين عند الإدخال اليدوي

    console.log('Bill Amount:', billAmount, 'Entered:', this.cashPaymentInput);
    const currentPaymentInput = this.cashPaymentInput;
    if (currentPaymentInput > 0 && currentPaymentInput >= billAmount) {
      const paymentMethodForModal = this.selectedPaymentMethod === 'cash + credit' ? 'cash' : this.selectedPaymentMethod;
      this.openTipModal(modalContent, billAmount, currentPaymentInput, paymentMethodForModal);
    }
  }
  // فتح مودال الإكرامية للدفع المختلط
  openMixedPaymentTipModal(billAmount: number, modalContent: any): void {
    const totalPaid = this.cashAmountMixed + this.creditAmountMixed;

    // التحقق من أن المبلغ المدفوع كافي
    if (totalPaid >= billAmount) {
      this.tempBillAmount = billAmount;
      this.tempPaymentAmount = totalPaid;
      this.tempChangeAmount = totalPaid - billAmount;
      // ✅ التعديل: تمرير القيمة المعدلة
      const paymentMethodForModal = this.selectedPaymentMethod === 'cash + credit' ? 'cash' : this.selectedPaymentMethod;
      this.openTipModal(modalContent, billAmount, totalPaid, paymentMethodForModal);
    } else {
      // يمكن إضافة رسالة تنبيه هنا إذا أردت
      console.warn('المبلغ المدفوع غير كافي لفتح مودال الإكرامية');
    }
  }

}
