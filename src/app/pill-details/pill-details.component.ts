// import {
//   Component,
//   OnInit,
//   ViewChild,
//   ElementRef,
//   ChangeDetectorRef,
// } from '@angular/core';
// import { PillDetailsService } from '../services/pill-details.service';
// import { CommonModule, DecimalPipe } from '@angular/common';
// import { ActivatedRoute } from '@angular/router';
// import { DatePipe } from '@angular/common';
// import { PrintedInvoiceService } from '../services/printed-invoice.service';
// import { Router } from '@angular/router';
// import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
// import { finalize } from 'rxjs';
// import { ConfirmDialogModule } from 'primeng/confirmdialog';
// import { ButtonModule } from 'primeng/button';  
// import  { ConfirmDialogComponent } from "../shared/ui/component/confirm-dialog/confirm-dialog.component";

// @Component({
//   selector: 'app-pill-details',
//   imports: [CommonModule, ShowLoaderUntilPageLoadedDirective, DecimalPipe, ConfirmDialogModule,
//     ButtonModule, ConfirmDialogComponent],
//   templateUrl: './pill-details.component.html',
//   styleUrls: ['./pill-details.component.css'],
//   providers: [DatePipe],
// })
// export class PillDetailsComponent implements OnInit {printOptions = [
//   { name: 'طباعة نهائية', id: 0 },
//   { name: 'معاينة فقط', id: 1 },
// ];
//  @ViewChild('confirmPrintDialog') confirmationDialog!: ConfirmDialogComponent;

//   @ViewChild('printedPill') printedPill!: ElementRef;
//   // @ViewChild('deliveredButton', { static: false }) deliveredButton!: ElementRef;
//   currencySymbol = localStorage.getItem('currency_symbol');
//   note = localStorage.getItem('additionalNote');
//   invoices: any;
//   pillDetails: any;
//   branchDetails: any;
//   pillId!: any;
//   orderDetails: any[] = [];
//   date: string | null = null;
//   time: string | null = null;
//   invoiceSummary: any;
//   addressDetails: any;
//   isDeliveryOrder: boolean = false;
//   paymentStatus: any = '';
//   trackingStatus: any = '';
//   orderNumber: any;
//   addresDetails: any;
//   isShow: boolean = true;
//   address_notes: string = 'لا يوجد';
//   cuponValue: any;
//   couponType: any;
//   cashier_machine_id!: number;
//   showPrices = false;
//   test: boolean | undefined;
//   paymentMethod: any;
// loading:boolean=true;
// isPrinting = false;

//   constructor(
//     private pillDetailsService: PillDetailsService,
//     private route: ActivatedRoute,
//     private orderService: PillDetailsService,
//     private cdr: ChangeDetectorRef,
//     private datePipe: DatePipe,
//     private printedInvoiceService: PrintedInvoiceService,
//     private router: Router) {}
//   private extractDateAndTime(branch: any): void {
//     const { created_at } = branch;
//     // console.log(created_at, 'test');

//     if (created_at) {
//       const dateObj = new Date(created_at);

//       this.date = this.datePipe.transform(dateObj, 'yyyy-MM-dd');

//       this.time = this.datePipe.transform(dateObj, 'hh:mm a');
//     }
//   }

//   ngOnInit(): void {
//     this.route.paramMap.subscribe((params) => {
//       this.pillId = params.get('id');

//       window.addEventListener('online', this.handleOnlineStatus.bind(this));
//       window.addEventListener('offline', this.handleOnlineStatus.bind(this));

//       this.dbService.init(); // ✅ افتح قاعدة البيانات


//       if (this.pillId) {
//         this.fetchPillsDetails(this.pillId);
//       }
//     });
//     this.fetchTrackingStatus();
//     // this.getNoteFromLocalStorage();
//     this.cashier_machine_id = Number(
//       localStorage.getItem('cashier_machine_id')
//     );
//     const storedData: string | null =
//       localStorage.getItem('cashier_machine_id');

//     if (storedData !== null) {
//       // Safe to parse since storedData is guaranteed to be a string
//       const transactionDataFromLocalStorage = JSON.parse(storedData);

//       // Access the cashier_machine_id
//       this.cashier_machine_id = transactionDataFromLocalStorage;

//       // console.log(this.cashier_machine_id,'one');  // Output: 1
//     } else {
//       console.log('No data found in localStorage.');
//     }
//   }
//   getNoteFromLocalStorage() {
//     throw new Error('Method not implemented.');
//   }

//   // Print Invoice method
//   // printInvoice() {
//   //   let printContent = document.getElementById('printSection')!.innerHTML;
//   //   let originalContent = document.body.innerHTML;

//   //   document.body.innerHTML = printContent;
//   //   window.print();
//   //   document.body.innerHTML = originalContent;
//   //   location.reload();
//   // }

//   fetchTrackingStatus() {
//     this.pillDetailsService
//       .getPillsDetailsById(this.pillId)
//       .subscribe((response) => {
//         if (response.status && response.data.invoices.length > 0) {
//           this.trackingStatus =
//             response.data.invoices[0]['tracking-status'] || '';
//         }
//       });
//   }

//   fetchPillsDetails(pillId: string): void {
//     this.loading=false
//     this.pillDetailsService.getPillsDetailsById(pillId).pipe(
//     finalize(() => {
//       this.loading=true;
//     })
//   ).subscribe({
//       next: (response: any) => {
//         this.order_id = response.data.order_id
//         this.invoices = response.data.invoices;
//         console.log( response,'response gggg' );


//         const statusMap: { [key: string]: string } = {
//           completed: 'مكتمل',
//           pending: 'في انتظار الموافقة',
//           cancelled: 'ملغي',
//           packing: 'يتم تجهيزها',
//           readyForPickup: 'جاهز للاستلام',
//           on_way: 'في الطريق',
//           in_progress: 'يتم تحضير الطلب',
//           delivered: 'تم التوصيل',
//         };

//         const trackingKey = this.invoices[0]?.['tracking-status'];
//         if (trackingKey === 'completed') {
//           this.isShow = false;
//         }
//         this.trackingStatus = statusMap[trackingKey] || trackingKey;
//         this.orderNumber = response.data.order_id;
//         this.couponType = this.invoices[0].invoice_summary.coupon_type;

//         this.addresDetails = this.invoices[0]?.address_details || {};
//         this.paymentMethod = this.invoices[0]?.transactions[0]?.['payment_method'];
//         this.paymentStatus = this.invoices[0]?.transactions[0]?.['payment_status'];
//         //  if (this.trackingStatus === 'completed' ) {
//         //   this.deliveredButton?.nativeElement.click();
//         //   }
//         this.isDeliveryOrder = this.invoices?.some(
//           (invoice: any) => invoice.order_type === 'Delivery'
//         );
//         // console.log( this.invoices[0].order_type,'this.invoices[0].order_type')

//         this.branchDetails = this.invoices?.map(
//           (e: { branch_details: any }) => e.branch_details
//         );
//         console.log(this.branchDetails,'branchDetails')
//         this.orderDetails = this.invoices?.map((e: any) => e.orderDetails);

//         // this.invoiceSummary = this.invoices?.map((e: any) => e.invoice_summary );
//         // this.invoiceSummary = this.invoices?.map((e: any) => ({
//         //   ...e.invoice_summary,
//         //   currency_symbol: e.currency_symbol,
//         // }));
//         this.invoiceSummary = this.invoices?.map((e: any) => {
//           let summary = {
//             ...e.invoice_summary,
//             currency_symbol: e.currency_symbol,
//           };

//           // // Convert coupon_value if it's a percentage
//           // if (summary.coupon_type === 'percentage') {
//           //   const couponValue = parseFloat(summary.coupon_value); // "10.00" → 10
//           //   const subtotal = parseFloat(summary.subtotal_price);
//           //   summary.coupon_value = ((couponValue / 100) * subtotal).toFixed(2); // Convert to currency
//           // }

//           return summary;
//         });

//         // console.log(this.invoiceSummary,'test')
//         this.addressDetails = this.invoices?.map((e: any) => e.address_details);

//         if (this.branchDetails?.length) {
//           this.extractDateAndTime(this.branchDetails[0]);
//         }
//       },
//       error: (error: any) => {
//         console.error(' Error fetching pill details:', error);
//       },
//     });
//   }
//   hasDeliveryOrDineIn(): boolean {
//     return this.invoices?.some((invoice: { order_type: string }) =>
//       ['Delivery', 'Dine-in'].includes(invoice.order_type)
//     );
//   }
//   getTrackingStatusTranslation(status: string): string {
//     const statusTranslations: { [key: string]: string } = {
//       completed: 'مكتمل',
//       pending: 'في انتظار الموافقة',
//       cancelled: 'ملغي',
//       packing: 'يتم تجهيزها',
//       readyForPickup: 'جاهز للاستلام',
//       on_way: 'في الطريق',
//       in_progress: 'يتم تحضير الطلب',
//       delivered: 'تم التوصيل',
//     };

//     return statusTranslations[status] || status; // Default to status if no translation found
//   }

//   hasDineInOrder(): boolean {
//     return this.invoices?.some(
//       (invoice: { order_type: string }) => invoice.order_type === 'dine-in'
//     );
//   }

//   hastakeaway(): boolean {
//     return this.invoices?.some(
//       (invoice: { order_type: string }) => invoice.order_type === 'Takeaway'
//     );
//   }
//   // Helper method to extract date and time from 'created_at'
//   // private extractDateAndTime(branch: any): void {
//   //   const { created_at } = branch;

//   //   if (created_at) {
//   //     const [date, time] = created_at.split('T');
//   //     this.date = date;
//   //     this.time = time?.replace('Z', ''); // Remove 'Z' from the time string
//   //   }
//   // }
//   changePaymentStatus(status: string) {
//     this.paymentStatus = status;

//     this.cdr.detectChanges();
//   }

//   changeTrackingStatus(status: string) {
//     this.trackingStatus = status.trim();
//     console.log('تم تحديث حالة التوصيل:', this.trackingStatus);

//     this.cdr.detectChanges();
//   }
//   saveOrder() {
//     if (!this.paymentStatus || !this.trackingStatus) {
//       alert('يجب تحديد حالة الدفع وحالة التوصيل قبل الحفظ!');
//       return;
//     }

//     this.orderService
//       .updateInvoiceStatus(this.orderNumber, this.paymentStatus, this.trackingStatus)
//       .subscribe({
//         next: (response) => {
//           localStorage.setItem(
//             'pill_detail_data',
//             JSON.stringify(response.data)
//           );
//           this.router.navigate(['/pills']);
//         },
//         error: (err) => {
//           console.error(' خطأ في حفظ الطلب:', err);
//         },
//       });
//   }
// isFinal=false;
// order_id:any
//   async printInvoice(isFinal:boolean=false) {

//     this.isFinal=isFinal;
//   this.isPrinting = true;

//     if (!this.invoices?.length || !this.invoiceSummary?.length) {
//       console.warn('Invoice data not ready.');
//     this.isPrinting = false;
//       return;
//     }

//     try {
//       const response = await this.printedInvoiceService
//         .printInvoice(this.orderNumber, this.cashier_machine_id, this.paymentMethod)
//         .toPromise();
// /* if(response.status==false){
//   alert(response.message);
//   return;
// }
//    */    console.log('Print invoice response:', response);

//       const printContent = document.getElementById('printSection');
//       if (!printContent) {
//         console.error('Print section not found.');
//         return;
//       }

//       const originalHTML = document.body.innerHTML;

//       const copies = this.isDeliveryOrder
//         ? [
//             { showPrices: true, test: true },
//             { showPrices: false, test: false },
//             { showPrices: true, test: true },
//           ]
//         : [
//             { showPrices: true, test: true },
//             { showPrices: false, test: false },
//           ];

//       for (let i = 0; i < copies.length; i++) {
//         this.showPrices = copies[i].showPrices;
//         this.test = copies[i].test;
//         await new Promise((resolve) => setTimeout(resolve, 300));

//       const singlePageHTML = `
//   <div> 
//     ${printContent.innerHTML}
//   </div>
// `;


//         document.body.innerHTML = singlePageHTML;

//         await new Promise((resolve) =>
//           setTimeout(() => {
//             window.print();
//             resolve(true);
//           }, 200)
//         );
//       }

//       document.body.innerHTML = originalHTML;
//        location.reload(); 
//     } catch (error) {
//       console.error('Error printing invoice:', error);
//     }finally {
//     this.isPrinting = false;
//   }

//   }

//   getDiscountAmount(): number {
//     if (
//       !this.couponType ||
//       !this.invoices ||
//       !this.invoices[0]?.invoice_summary
//     ) {
//       return 0; // No discount if no coupon is applied or data is missing
//     }

//     const invoiceSummary = this.invoices[0].invoice_summary;
//     const couponValue = parseFloat(invoiceSummary.coupon_value);

//     if (isNaN(couponValue)) {
//       return 0; // If coupon_value is not a valid number, return 0
//     }

//     if (this.couponType === 'percentage') {
//       return (invoiceSummary.subtotal_price * couponValue) / 100;
//     } else if (this.couponType === 'fixed') {
//       return couponValue;
//     }

//     return 0; // Default to 0 if no valid coupon type
//   }

//   // To get final price after discount
//   getFinalPrice(): number {
//     return (
//       this.invoices[0].invoice_summary.subtotal_price - this.getDiscountAmount()
//     );
//   }


//    onPrintButtonClick() {
//     this.confirmationDialog.confirm();
//     }
// }


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
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { finalize } from 'rxjs';
import { ConfirmDialogModule } from 'primeng/confirmdialog';
import { ButtonModule } from 'primeng/button';
import { ConfirmDialogComponent } from "../shared/ui/component/confirm-dialog/confirm-dialog.component";
import { IndexeddbService } from '../services/indexeddb.service';

@Component({
    selector: 'app-pill-details',
    imports: [CommonModule, ShowLoaderUntilPageLoadedDirective, DecimalPipe, ConfirmDialogModule,
        ButtonModule, ConfirmDialogComponent],
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
    loading: boolean = true;
    isPrinting = false;
    isOnline: boolean = navigator.onLine;
    usingOfflineData: boolean = false;

    constructor(
        private pillDetailsService: PillDetailsService,
        private route: ActivatedRoute,
        private orderService: PillDetailsService,
        private dbService: IndexeddbService,
        private cdr: ChangeDetectorRef,
        private datePipe: DatePipe,
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

    ngOnInit(): void {
        window.addEventListener('online', this.handleOnlineStatus.bind(this));
        window.addEventListener('offline', this.handleOnlineStatus.bind(this));

        // ✅ ننتظر فتح الـ DB ثم نستمع للـ route
        this.dbService.init().then(() => {
            this.route.paramMap.subscribe(async (params) => {
                this.pillId = params.get('id') || '';
                if (!this.pillId) return;

                navigator.onLine
                    ? this.fetchPillsDetails(this.pillId)
                    : await this.loadFromIndexedDB(); // ✅ offline reader
            });
        });

        this.fetchTrackingStatus();
        this.cashier_machine_id = Number(localStorage.getItem('cashier_machine_id'));
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
                }
            });
    }
    private normalizeKey(id: string | number): string {
        return String(id);
    }
    fetchPillsDetails(pillId: string): void {
        this.loading = false;
        this.pillDetailsService.getPillsDetailsById(pillId).pipe(
            finalize(() => {
                this.loading = true;
            })
        ).subscribe({
            next: (response: any) => {
                if (response.status && response.data) {
                    this.pillDetails = response.data;
                    const key = String(this.pillId);
                    // ✅ Use the dedicated method for saving pill details
                    this.dbService.savePillDetails(
                        this.normalizeKey(this.pillId),
                        this.pillDetails
                    );

                    // Process the data
                    this.processPillDetails(this.pillDetails);
                }
            },
            error: (error: any) => {
                console.error('Error fetching pill details:', error);
                // Try to load from offline storage if online fetch fails
                this.loadFromIndexedDB();
            },
        });
    }

    hasDeliveryOrDineIn(): boolean {
        return this.invoices?.some((invoice: { order_type: string }) =>
            ['Delivery', 'Dine-in'].includes(invoice.order_type)
        );
    }

    private handleOnlineStatus() {
        this.isOnline = navigator.onLine;
        // Optional: automatically refresh data when coming back online
        if (this.isOnline && this.usingOfflineData) {
            this.fetchPillsDetails(this.pillId); // ✅ مرري pillId هنا
        }
        this.cdr.detectChanges();
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
    invoice_id: any
    async printInvoice(isFinal: boolean = false) {

        this.isFinal = isFinal;
        this.isPrinting = true;

        if (!this.invoices?.length || !this.invoiceSummary?.length) {
            console.warn('Invoice data not ready.');
            this.isPrinting = false;
            return;
        }

        try {
            const response = await this.printedInvoiceService
                .printInvoice(this.orderNumber, this.cashier_machine_id, this.paymentMethod)
                .toPromise();
/* if(response.status==false){
  alert(response.message);
  return;
}
   */    console.log('Print invoice response:', response);

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
        } finally {
            this.isPrinting = false;
        }

    }
    private async loadFromIndexedDB(): Promise<void> {
        try {
            await this.dbService.init();
            const key = this.getSafeKey();

            // 1) أولاً: full-details store
            let pillDetails = await this.dbService.getPillDetails(key);

            // 2) ثانياً: fallback إلى light pills store
            if (!pillDetails) {
                const allPills: any[] = await this.dbService.getAll('pills');

                const lightPill = allPills.find(
                    p =>
                        String(p.invoice_id || '').trim() === key ||
                        String(p.order_id || '').trim() === key ||
                        String(p.invoice_number || '').trim() === key
                );

                if (!lightPill) {
                    console.warn('⚠️ Invoice not found for key:', key);
                    this.handleNoDataFound();
                    return;
                }

                pillDetails = this.convertLightPillToDetails(lightPill);

                // ✅ اختيارى: تخزين نسخة كاملة للزيارات القادمة
                await this.dbService.savePillDetails(key, pillDetails);
            }

            this.pillDetails = pillDetails;
            this.usingOfflineData = true;
            this.processPillDetails(pillDetails);

            console.log('✅ Offline invoice rendered', pillDetails);
        } catch (err) {
            console.error('❌ IndexedDB read failed', err);
            this.handleNoDataFound();
        } finally {
            this.cdr.detectChanges();
        }
    }


    private convertLightPillToDetails(p: any): any {
        return {
            order_id: p.order_id || p.invoice_number,
            invoices: [
                {
                    ...p,
                    branch_details: p.branch_details || {},
                    address_details: p.address_details || {},
                    invoice_summary: p.invoice_summary || {},
                    orderDetails: p.orderDetails || p.items || [],
                    transactions: p.transactions || [{ payment_method: null, payment_status: null }],
                },
            ],
        };
    }

    private processPillDetails(pillDetails: any): void {
        this.order_id = pillDetails.order_id;
        this.invoices = pillDetails.invoices || [pillDetails]; // Handle both structures

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
        this.orderNumber = pillDetails.order_id;
        this.couponType = this.invoices[0]?.invoice_summary?.coupon_type;

        this.addresDetails = this.invoices[0]?.address_details || {};
        this.paymentMethod = this.invoices[0]?.transactions?.[0]?.['payment_method'];
        this.paymentStatus = this.invoices[0]?.transactions?.[0]?.['payment_status'];

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
    }

    private convertPillToDetailsStructure(pill: any): any {
        // Convert a pill from the pills store to the pillDetails structure
        return {
            order_id: pill.order_id,
            invoices: [pill], // Wrap the single pill in an invoices array
            // Add other properties that might be expected
        };
    }

    private handleNoDataFound(): void {
        this.pillDetails = null;
        this.invoices = [];
        this.usingOfflineData = false;
        console.warn('No offline data found for pill ID:', this.pillId);
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

    // To get final price after discount
    getFinalPrice(): number {
        return (
            this.invoices[0].invoice_summary.subtotal_price - this.getDiscountAmount()
        );
    }



    onPrintButtonClick() {
        this.confirmationDialog.confirm();
    }

    private getSafeKey(): string {
        return String(this.pillId).trim();
    }
}
