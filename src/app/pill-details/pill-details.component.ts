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
import { PrintTimeService } from '../services/print-time.service';
import { ReceiptComponent } from '../receipt/receipt.component';
import html2canvas from 'html2canvas';

@Component({
  selector: 'app-pill-details',
  imports: [CommonModule, ShowLoaderUntilPageLoadedDirective, DecimalPipe, ConfirmDialogModule,
    ButtonModule, ConfirmDialogComponent,RouterLink ,RouterLinkActive, ReceiptComponent],
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
  invoices: any[] = [];
  pillDetails: any;
  receiptData: any;
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
  address_notes: string = 'لا يوجد';
  cuponValue: any;
  couponType: any;
  cashier_machine_id!: number;
  showPrices = false;
  test: boolean | undefined;
  paymentMethod: any;
  loading: boolean = false;
  isPrinting = false;

  constructor(
    private pillDetailsService: PillDetailsService,
    private route: ActivatedRoute,
    private orderService: PillDetailsService,
    private cdr: ChangeDetectorRef,
    private datePipe: DatePipe,
    private dbService: IndexeddbService,
    private printedInvoiceService: PrintedInvoiceService,
    private router: Router,
    private printTime: PrintTimeService) { }
  private extractDateAndTime(branch: any): void {
    const { created_at } = branch;

    if (created_at) {
      const { dateStr, timeStr } = this.printTime.formatForPrint(created_at);
      this.date = dateStr;
      this.time = timeStr;
    }
  }

  //  start dalia
  ngOnInit(): void {
    this.route.paramMap.subscribe((params) => {
      this.pillId = params.get('id');

      if (this.pillId) {
        this.fetchPillsDetails(this.pillId);
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
  try {
    this.order_id = data.order_id;
    this.invoices = Array.isArray(data.invoices.invoice_details) ? data.invoices.invoice_details : []; // ✅ fix

    if (!this.invoices.length) {
      console.warn("No invoices found in offline pill:", data);
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
    if (this.invoices[0] && trackingKey === 'completed') {
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

  getOrderTypeLabel(type: string): string {
    const map: any = {
      'dine-in': 'في المطعم',
      'Takeaway': 'استلام',
      'talabat': 'طلبات',
      'Delivery': 'توصيل'
    };

  return map[type] || type;
}

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
        this.order_id = response.data.order_id;
        this.invoices = response.data.invoices || [];
        
        if (this.invoices.length === 0) {
          console.warn('No invoices found in response');
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

        const firstInvoice = this.invoices[0];
        const trackingKey = firstInvoice?.['tracking-status'];
        if (trackingKey === 'completed') {
          this.isShow = false;
        }
        this.trackingStatus = statusMap[trackingKey] || trackingKey;
        this.orderNumber = response.data.order_id;
        this.couponType = firstInvoice?.invoice_summary?.coupon_type;

        this.addresDetails = firstInvoice?.address_details || {};
        this.paymentMethod = firstInvoice?.transactions?.[0]?.['payment_method'];
        this.paymentStatus = firstInvoice?.transactions?.[0]?.['payment_status'];
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


        this.receiptData = {
          branchDetails: Array.isArray(this.branchDetails) ? this.branchDetails : (this.branchDetails ? [this.branchDetails] : []),
          invoices: response.data.invoices,
          order_id: response.data.order_id,
          invoice_summary: this.invoiceSummary || [],
          orderDetails: this.orderDetails.flat() || [],
          date: this.date,
          time: this.time,
          showPrices: true,
          paymentStatus: this.paymentStatus,
          invoice_id: response.data.invoice_tips[0]?.invoice_id,
          order_type: response.data.invoices[0]?.order_type,
          table_number: this.branchDetails[0]?.table_number,
          transactions: this.invoices[0]?.transactions,
          isFinal: this.isFinal, // change to true if you want to print the final invoice
          cashier: response.data.cashier,
          waiter: response.data.waiter,
          make_type: response.data.make_type
        };
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
    this.isFinal = isFinal;
    this.isPrinting = true;

    if (!this.invoices?.length || !this.invoiceSummary?.length) {
      console.warn('Invoice data not ready.');
      this.isPrinting = false;
      return;
    }

    // Update isFinal in receiptData if it exists
    if (this.receiptData) {
      this.receiptData.isFinal = isFinal;
    }

    try {
      
    console.log('Printing invoice...........');

this.cdr.detectChanges();
// Allow time for view to update
await new Promise((resolve) => setTimeout(resolve, 100));

// Check if running in Electron with deviceAPI available
if ((window as any).deviceAPI) {
  console.log('Detected Electron environment. Attempting silent print via deviceAPI.');

  const printContent = document.getElementById('printSection');
  if (!printContent) {
    console.error('Print section not found.');
    this.isPrinting = false;
    return;
  }

  // Clone the content to control dimensions for the printer
  const clone = printContent.cloneNode(true) as HTMLElement;

  // Ensure the clone is visible by removing d-none class
  clone.classList.remove('d-none');

  const printerWidth = 576; // Standard 80mm printer width

  // Reset styles for capture to ensure no inherited margins/padding affect layout
  clone.style.margin = '0';
  clone.style.padding = '0';
  clone.style.position = 'absolute';
  clone.style.top = '0';
  clone.style.left = '-1000px'; // position off-screen
  clone.style.zIndex = '-1000';
  clone.style.backgroundColor = 'white';

  // Override max-width on inner elements
  const innerPrintSection = clone.querySelector('.printSection') as HTMLElement;
  if (innerPrintSection) {
    innerPrintSection.style.setProperty('max-width', 'none', 'important');
    innerPrintSection.style.setProperty('width', '100%', 'important');
  }

  const innerReceiptContent = clone.querySelector('.receipt-content') as HTMLElement;
  if (innerReceiptContent) {
    innerReceiptContent.style.setProperty('max-width', 'none', 'important');
    innerReceiptContent.style.setProperty('width', '100%', 'important');
  }

  // KEY: captureWidth = printerWidth / scale = 576 / 2 = 288
  // This means: canvas width = 288 * 2 = 576px = exact printer width
  // Result: 1:1 mapping, NO compression, NO stretching. Text looks natural.
  // To make text bigger/smaller, adjust fontSize above (not captureWidth).
  const captureWidth = 288;
  clone.style.width = `${captureWidth}px`;
  clone.style.maxWidth = 'none';

  // Append to body to ensure styles are applied
  document.body.appendChild(clone);

  // Wait for layout and potential image loading
  await new Promise((resolve) => setTimeout(resolve, 500));

  try {
    // Capture with scale 2 for sharpness (288 * 2 = 576px = printerWidth)
    const canvas = await html2canvas(clone, {
      scale: 2,
      useCORS: true,
      logging: false,
      backgroundColor: '#ffffff',
      width: captureWidth,
      windowWidth: captureWidth,
    });

    // Create final canvas — same size as captured canvas (1:1, no scaling)
    const finalCanvas = document.createElement("canvas");
    finalCanvas.width = printerWidth; // 576
    finalCanvas.height = canvas.height; // already correct because canvas.width == printerWidth

    const ctx = finalCanvas.getContext("2d");

    if (ctx && canvas.width > 0 && canvas.height > 0) {
      ctx.fillStyle = "#fff";
      ctx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
      
      // Draw 1:1 — no stretching or compression
      ctx.drawImage(canvas, 0, 0);
    } else {
      console.warn('Silent Print: Canvas is empty or invalid dimensions', canvas.width, canvas.height);
    }

    const pngDataUrl = finalCanvas.toDataURL("image/png");
    const base64Image = pngDataUrl.replace(/^data:image\/png;base64,/, "");

    // Printer settings
    const printerIP = "192.168.11.187"; 
    const port = 9100;

    console.log(`Sending silent print request to ${printerIP}:${port}!`);
    const result = await (window as any).deviceAPI.testPrinterConnection(printerIP, port, base64Image);

    if (result.success) {
      console.log("Silent print successful");
      location.reload(); // optional post-print action
    } else {
      console.error("Silent print failed:", result);
      alert(`فشلت الطباعة الصامتة: ${result.message || 'خطأ غير معروف'}`);
    }

  } catch (captureError) {
    console.error('Error capturing invoice for silent print:', captureError);
  } finally {
    // Clean up
    if (document.body.contains(clone)) {
      document.body.removeChild(clone);
    }
  }

  return; // Exit method, preventing fallback to window.print
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
      location.reload();
    } catch (error) {
      console.error('Error printing invoice:', error);
    } finally {
      this.isPrinting = false;
    }

  }

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

  // Calculate total paid amount from transactions
  getTotalPaid(): number {
    if (!this.invoices || !this.invoices[0]?.transactions) {
      return 0;
    }
    return this.invoices[0].transactions.reduce((sum: number, trans: any) => {
      return sum + (parseFloat(trans.paid) || 0);
    }, 0);
  }

  // Calculate remaining amount (paid - total)
  getRemainingAmount(): number {
    if (!this.invoiceSummary || !this.invoiceSummary[0]) {
      return 0;
    }
    const totalPrice = parseFloat(this.invoiceSummary[0].total_price) || 0;
    const totalPaid = this.getTotalPaid();
    return totalPaid - totalPrice;
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
}
