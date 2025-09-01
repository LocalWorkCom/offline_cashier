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
        // إعداد مستمعات حالة الاتصال
        window.addEventListener('online', this.handleOnlineStatus.bind(this));
        window.addEventListener('offline', this.handleOnlineStatus.bind(this));

        // تهيئة قاعدة البيانات ثم الاستماع لمعاملات المسار
        this.dbService.init().then(async () => {
            this.route.paramMap.subscribe(async (params) => {
                this.pillId = params.get('id') || '';
                if (!this.pillId) return;

                // التحقق أولاً من وجود البيانات محلياً
                const hasOfflineData = await this.checkOfflineAvailability();

                if (navigator.onLine) {
                    // إذا كان اتصال متاح، جلب البيانات من الخادم
                    this.fetchPillsDetails(this.pillId);
                } else if (hasOfflineData) {
                    // إذا لا يوجد اتصال但有 بيانات محفوظة
                    await this.loadFromIndexedDB();
                } else {
                    // إذا لا يوجد اتصال ولا بيانات محفوظة
                    this.handleNoDataFound();
                }
            });
        });

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
        return String(id).trim();
    }


    // Update the fetchPillsDetails method to use the improved save function
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

                    // Save to IndexedDB for offline access
                    this.savePillDetailsToIndexedDB(pillId, this.pillDetails);

                    this.processPillDetails(this.pillDetails);
                    this.usingOfflineData = false;
                }
            },
            error: (error: any) => {
                console.error('Error fetching pill details:', error);
                // Try to load from offline storage
                this.loadFromIndexedDB();
            },
        });
    }

    hasDeliveryOrDineIn(): boolean {
        return this.invoices?.some((invoice: { order_type: string }) =>
            ['Delivery', 'Dine-in'].includes(invoice.order_type)
        );
    }

    private handleOnlineStatus(): void {
        const wasOnline = this.isOnline;
        this.isOnline = navigator.onLine;

        console.log('Network status changed:', this.isOnline ? 'Online' : 'Offline');

        if (wasOnline && !this.isOnline) {
            // الانتقال من اتصال إلى عدم اتصال
            console.log('Went offline, using cached data if available');
            if (this.pillId) {
                this.loadFromIndexedDB().catch(err => {
                    console.error('Failed to load from IndexedDB:', err);
                });
            }
        } else if (!wasOnline && this.isOnline) {
            // الانتقال من عدم اتصال إلى اتصال
            console.log('Went online, refreshing data');
            this.cdr.detectChanges();

            // التحديث التلقائي عند العودة للاتصال
            if (this.pillId && this.usingOfflineData) {
                this.fetchPillsDetails(this.pillId);
            }
        }
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
        this.loading = true;
        try {
            const key = this.getSafeKey();
            console.log('Attempting to load from IndexedDB for key:', key);

            // المحاولة الأولى: البحث في pillDetails بالمفتاح المباشر (invoice_id)
            let pillDetails = await this.dbService.getPillDetails(key);

            // المحاولة الثانية: إذا لم تُوجد، البحث في pills باستخدام الفهرس الجديد (invoice_id)
            if (!pillDetails) {
                console.log('Details not found in pillDetails store. Searching in pills store by invoice_id...');
                // استخدام الدالة الجديدة للبحث بواسطة invoice_id
                const foundPill = await this.dbService.getPillByInvoiceId(Number(key));

                if (foundPill) {
                    console.log('Found in "pills" store. Converting and caching.');
                    pillDetails = this.convertLightPillToDetails(foundPill);
                    await this.dbService.savePillDetails(key, pillDetails);
                }
            }

            if (pillDetails) {
                console.log('Data successfully loaded from offline storage.');
                this.pillDetails = pillDetails.data;
                this.usingOfflineData = true;
                this.processPillDetails(this.pillDetails);
            } else {
                console.warn('No data found in IndexedDB for the given key.');
                this.handleNoDataFound();
            }
        } catch (err) {
            console.error('IndexedDB read failed:', err);
            this.handleNoDataFound();
        } finally {
            this.loading = false;
        }
    }

    // في PillDetailsComponent
    private convertLightPillToDetails(pill: any): any {
    return {
        status: true,
        data: {
            order_id: pill.order_id,
            invoices: [
                {
                    ...pill,
                    orderDetails: pill.items || [],
                    invoice_summary: pill.invoice_summary || {},
                    branch_details: pill.branch_details || {},
                    address_details: pill.address_details || {},
                    transactions: pill.transactions || [{ payment_method: null, payment_status: null }],
                }
            ]
        }
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

    // دالة محسنة للتعامل مع عدم وجود بيانات
    private handleNoDataFound(): void {
        this.pillDetails = null;
        this.invoices = [];
        this.usingOfflineData = false;

        // عرض رسالة للمستخدم
        this.showOfflineMessage();
    }

    private showOfflineMessage(): void {
        // يمكنك استبدال هذا بتنفيذ رسالة واجهة المستخدم المناسبة
        console.warn('No offline data available for this invoice');
        alert('لا تتوفر بيانات لهذه الفاتورة في وضع عدم الاتصال. يرجى الاتصال بالإنترنت لتحميل البيانات.');
    }
    // دالة للتحقق من حالة التخزين المحلي
    async checkOfflineAvailability(): Promise<boolean> {
        try {
            const key = this.getSafeKey();
            return await this.dbService.hasPillDetails(key);
        } catch (error) {
            return false;
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
        return this.normalizeKey(this.pillId);
    }

    private async savePillDetailsToIndexedDB(pillId: string, pillDetails: any): Promise<void> {
        try {
            const normalizedKey = this.normalizeKey(pillId);

            // Ensure the data structure is consistent
            const dataToSave = {
                ...pillDetails,
                id: normalizedKey, // Ensure ID is set for keyPath
                savedAt: new Date().toISOString(),
                isSynced: navigator.onLine
            };

            await this.dbService.savePillDetails(normalizedKey, dataToSave);
            console.log('Pill details saved to IndexedDB successfully');
        } catch (err) {
            console.warn('Failed to save to IndexedDB:', err);
            // You might want to implement a retry mechanism here
        }
    }

    async checkStoredPills() {
        try {
            const allPills = await this.dbService.getAllPillDetails();
            console.log('Stored pills in IndexedDB:', allPills);

            // Also check if our current pill is stored
            const currentPill = await this.dbService.getPillDetails(this.normalizeKey(this.pillId));
            console.log('Current pill details:', currentPill);
        } catch (error) {
            console.error('Error checking stored pills:', error);
        }
    }

    async debugPillDetailsStore() {
        try {
            const transaction = this.dbService['db'].transaction(['pillDetails'], 'readonly');
            const store = transaction.objectStore('pillDetails');
            const request = store.getAll();

            request.onsuccess = () => {
                console.log('DEBUG - All items in pillDetails store:', request.result);
            };

            request.onerror = (error) => {
                console.error('DEBUG - Error accessing pillDetails store:', error);
            };
        } catch (error) {
            console.error('DEBUG - Failed to access IndexedDB:', error);
        }
    }

}
