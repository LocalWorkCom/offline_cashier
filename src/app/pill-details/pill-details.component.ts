import { invoice } from './../interfaces/invoice';

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
import { RouterLink, RouterLinkActive } from '@angular/router';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { finalize } from 'rxjs';
import { ConfirmDialogModule } from 'primeng/confirmdialog';
import { ButtonModule } from 'primeng/button';
import { ConfirmDialogComponent } from "../shared/ui/component/confirm-dialog/confirm-dialog.component";
import { IndexeddbService } from '../services/indexeddb.service';

@Component({
  selector: 'app-pill-details',
  imports: [CommonModule, ShowLoaderUntilPageLoadedDirective, DecimalPipe, ConfirmDialogModule,
    ButtonModule, ConfirmDialogComponent, RouterLink, RouterLinkActive],
  templateUrl: './pill-details.component.html',
  styleUrls: ['./pill-details.component.css'],
  providers: [DatePipe],
})
export class PillDetailsComponent implements OnInit {
  printOptions = [
    { name: 'طباعة نهائية', id: 0 },
    { name: 'معاينة فقط', id: 1 },
  ];
  @ViewChild('confirmPrintDialog') confirmationDialog!: ConfirmDialogComponent;

  @ViewChild('printedPill') printedPill!: ElementRef;
  // @ViewChild('deliveredButton', { static: false }) deliveredButton!: ElementRef;
  currencySymbol = localStorage.getItem('currency_symbol');
  note = localStorage.getItem('additionalNote');
  invoices: any;
  pillDetails: any;
  branchDetails: any;
  // start hanan
  isOnline: boolean = navigator.onLine;
  offlinePillData: any = null;
  // end hanan
  pillId!: any;
  orderDetails: any[] = [];
  date: string | null = null;
  time: string | null = null;
  invoiceSummary: any;
  invoiceTips: any;
  addressDetails: any;
  isDeliveryOrder: boolean = false;
  paymentStatus: any = '';
  trackingStatus: any = '';
  orderNumber: any;
  addresDetails: any;
  isShow: boolean = true;
  address_notes: string = 'لا يوجد';
  cuponValue: any;
  couponType: any;
  cashier_machine_id!: number;
  showPrices = false;
  test: boolean | undefined;
  paymentMethod: any;
  loading: boolean = true;
  isPrinting = false;

  constructor(
    private pillDetailsService: PillDetailsService,
    private route: ActivatedRoute,
    private orderService: PillDetailsService,
    private cdr: ChangeDetectorRef,
    private datePipe: DatePipe,
    private dbService: IndexeddbService,
    private printedInvoiceService: PrintedInvoiceService,
    private router: Router) { }
  private extractDateAndTime(branch: any): void {
    const { created_at } = branch;
    // console.log(created_at, 'test');

    if (created_at) {
      const dateObj = new Date(created_at);

      this.date = this.datePipe.transform(dateObj, 'yyyy-MM-dd');

      this.time = this.datePipe.transform(dateObj, 'hh:mm a');
    }
  }

  //  start dalia
  ngOnInit(): void {
    this.route.paramMap.subscribe((params) => {
      this.pillId = params.get('id');

      if (this.pillId) {
        if (!navigator.onLine) {
          this.fetchPillFromIndexedDB(this.pillId);
        } else {
          this.fetchPillsDetails(this.pillId);
        }
        // this.fetchPillsDetails(this.pillId);
      }
    });
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

      // console.log(this.cashier_machine_id,'one');  // Output: 1
    } else {
      console.log('No data found in localStorage.');
    }
  }

  //   ngOnInit() {
  //   // Initialize DB first
  //    this.dbService.init();
  //   // Subscribe to route param
  //   this.route.paramMap.subscribe((params) => {
  //     this.pillId = params.get('id');
  //     if (this.pillId) {
  //       this.fetchPillFromIndexedDB(this.pillId);
  //     }
  //   });

  //   this.fetchTrackingStatus();

  //   this.cashier_machine_id = Number(
  //     localStorage.getItem('cashier_machine_id')
  //   );

  //   const storedData: string | null = localStorage.getItem('cashier_machine_id');
  //   if (storedData !== null) {
  //     const transactionDataFromLocalStorage = JSON.parse(storedData);

  //   //     // Access the cashier_machine_id
  //       this.cashier_machine_id = transactionDataFromLocalStorage;
  //   } else {
  //     console.log('No data found in localStorage.');
  //   }
  // }

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



  //end dalia
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
        }
      });
  }

  fetchPillsDetails(pillId: string): void {
    this.loading = false
    this.pillDetailsService.getPillsDetailsById(pillId).pipe(
      finalize(() => {
        this.loading = true;
      })
    ).subscribe({
      next: (response: any) => {
        this.order_id = response.data.order_id
        this.invoices = response.data.invoices;
        this.invoiceTips = response.data.invoice_tips || [];

        console.log(response, 'response gggg');


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
        this.paymentMethod = this.invoices[0]?.transactions[0]?.['payment_method'];
        this.paymentStatus = this.invoices[0]?.transactions[0]?.['payment_status'];
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
        console.log(this.branchDetails, 'branchDetails')
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

          // // Convert coupon_value if it's a percentage
          // if (summary.coupon_type === 'percentage') {
          //   const couponValue = parseFloat(summary.coupon_value); // "10.00" → 10
          //   const subtotal = parseFloat(summary.subtotal_price);
          //   summary.coupon_value = ((couponValue / 100) * subtotal).toFixed(2); // Convert to currency
          // }

          return summary;
        });

        // console.log(this.invoiceSummary,'test')
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

    return statusTranslations[status] || status; // Default to status if no translation found
  }

  hasDineInOrder(): boolean {
    return this.invoices?.some(
      (invoice: { order_type: string }) => invoice.order_type === 'dine-in'
    );
  }

  hastakeaway(): boolean {
    return this.invoices?.some(
      (invoice: { order_type: string }) => invoice.order_type === 'Takeaway'
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
  isFinal = false;
  order_id: any
  async printInvoice(isFinal: boolean = false) {
    console.log('جاري طباعة الفاتورة...');
    this.isFinal = isFinal;
    this.isPrinting = true;
    // إغلاق الـ modal فورًا بعد بدء الطباعة
    this.closeConfirmationDialog();
    // التحقق من وجود البيانات
    if (!this.invoices?.length || !this.invoiceSummary?.length) {
      console.warn('بيانات الفاتورة غير جاهزة.');

      // محاولة تحميل البيانات من التخزين المحلي
      if (!this.isOnline) {
        await this.fetchPillFromIndexedDB(this.pillId);
      }

      if (!this.invoices?.length) {
        alert('بيانات الفاتورة غير متوفرة للطباعة.');
        this.isPrinting = false;
        return;
      }
    }

    try {
      // إذا كان هناك اتصال، محاولة الطباعة عبر الخدمة
      if (this.isOnline) {
        try {
          const response = await this.printedInvoiceService
            .printInvoice(this.orderNumber, this.cashier_machine_id, this.paymentMethod)
            .toPromise();
          console.log('استجابة طباعة الفاتورة:', response);
        } catch (onlineError) {
          console.warn('فشل الطباعة عبر الخدمة، الانتقال للطباعة المحلية:', onlineError);
        }
      } else {
        console.log('الطباعة في وضع عدم الاتصال');
      }

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
  private async performLocalPrint(): Promise<void> {
    const printContent = document.getElementById('printSection');
    if (!printContent) {
      console.error('قسم الطباعة غير موجود.');
      return;
    }

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

      // Wait for Angular to update the view
      this.cdr.detectChanges();
      await new Promise((resolve) => setTimeout(resolve, 300));

      // Create a hidden iframe for printing
      const printFrame = document.createElement('iframe');
      printFrame.style.position = 'fixed';
      printFrame.style.right = '0';
      printFrame.style.bottom = '0';
      printFrame.style.width = '0';
      printFrame.style.height = '0';
      printFrame.style.border = '0';
      document.body.appendChild(printFrame);

      const printFrameDoc = printFrame.contentWindow?.document || printFrame.contentDocument;
      if (!printFrameDoc) {
        console.error('Failed to access iframe document');
        document.body.removeChild(printFrame);
        continue;
      }

      // Get the updated content after Angular changes
      const updatedContent = document.getElementById('printSection');
      if (!updatedContent) {
        document.body.removeChild(printFrame);
        continue;
      }

      // Clone the content to avoid affecting the original
      const clonedContent = updatedContent.cloneNode(true) as HTMLElement;

      // Get all stylesheets from the main document
      const stylesheets = Array.from(document.styleSheets)
        .map(sheet => {
          try {
            return Array.from(sheet.cssRules || [])
              .map(rule => rule.cssText)
              .join('\n');
          } catch (e) {
            return '';
          }
        })
        .filter(text => text.length > 0)
        .join('\n');

      // Write content to iframe
      printFrameDoc.open();
      printFrameDoc.write(`
        <!DOCTYPE html>
        <html>
          <head>
            <title>Print Invoice</title>
            <style>
              ${stylesheets}
              @page {
                size: auto;
                margin: 0;
              }
              body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
              }
              .printSection {
                width: 90mm !important;
                font-size: 8px !important;
                margin: 0 !important;
                padding: 0 !important;
              }
              .printSection .card-img img {
                width: 150px !important;
                height: auto !important;
                max-width: 150px !important;
                display: block !important;
                margin: 0 auto !important;
              }
              * {
                box-sizing: border-box;
              }
            </style>
          </head>
          <body>
            ${clonedContent.innerHTML}
            ${!this.isOnline ? '<div style="text-align: center; color: red; margin-top: 10px;">🔴 طباعة محلية - غير متصل بالإنترنت</div>' : ''}
          </body>
        </html>
      `);
      printFrameDoc.close();

      // Wait for iframe to load, then print
      await new Promise((resolve) => {
        printFrame.onload = () => {
          setTimeout(() => {
            printFrame.contentWindow?.focus();
            printFrame.contentWindow?.print();
            resolve(true);
          }, 200);
        };
        // Fallback if onload doesn't fire
        setTimeout(() => {
          printFrame.contentWindow?.focus();
          printFrame.contentWindow?.print();
          resolve(true);
        }, 500);
      });

      // Clean up iframe after printing
      setTimeout(() => {
        if (printFrame.parentNode) {
          document.body.removeChild(printFrame);
        }
      }, 1000);
    }
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
  // end hanan

  getDiscountAmount(): number {
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
    return (
      this.invoices[0].invoice_summary.subtotal_price - this.getDiscountAmount()
    );
  }



  onPrintButtonClick() {
    this.confirmationDialog.confirm();
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
