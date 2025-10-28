import { Component, OnDestroy, OnInit } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { PillsService } from '../services/pills.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { NewOrderService } from '../services/pusher/newOrder';
import { finalize, Subject, takeUntil } from 'rxjs';
import { NewInvoiceService } from '../services/pusher/newInvoice';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { PillDetailsService } from '../services/pill-details.service';
import { IndexeddbService } from '../services/indexeddb.service';
import { ChangeDetectorRef } from '@angular/core';
import { SyncService } from '../services/sync.service';
import e from 'express';
import { InfiniteScrollModule } from 'ngx-infinite-scroll';

@Component({
  selector: 'app-pills',
  standalone: true,
  imports: [RouterLink, ShowLoaderUntilPageLoadedDirective, RouterLinkActive, CommonModule, FormsModule, InfiniteScrollModule],
  templateUrl: './pills.component.html',
  styleUrl: './pills.component.css',
})
export class PillsComponent implements OnInit, OnDestroy {
  isOnline: boolean = navigator.onLine;
  usingOfflineData: boolean = false;
  pills: any[] = [];
  uniqueStatuses: any[] = [];
  pillsByStatus: any;
  countHoldItems!: number;
  selectedStatus: number = 0;
  statusTranslations: { [key: string]: string } = {
    hold: 'معلقة',
    urgent: 'طارئة',
    done: 'مكتملة',
  };
  searchOrderNumber: string = '';
  searchText: any;
  filteredPillsByStatus: any[] | undefined;
  orderType: any
  orderTypeFilter: string = "dine-in"
  highlightedPillId: string | null = null;

  private destroy$ = new Subject<void>();
  loading: boolean = true;

  // dalia enchance
  displayedPills: any[] = [];
  isDataLoaded = false;
  private allPillsLoaded = false; // لمعرفة لو كل البيانات اتعرضت
  private isScrolling = false;    // لمنع التكرار في التحميل


  // batchSize: number = 30;
  batchSize = 30;
  displayedPillsByStatus: any[] = [];
  page = 1;
  perPage = 50;
  hasMore = true;
  isLoading = false;





  constructor(private pillRequestService: PillsService,
    private syncService: SyncService,
    private newOrder: NewOrderService, private orderService: PillDetailsService, private newInvoice: NewInvoiceService, private dbService: IndexeddbService,
    private cdr: ChangeDetectorRef) { }
  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
    this.newOrder.stopListening();
    this.newInvoice.stopListening();
  }

  // ngOnInit() {
  //   this.fetchPillsData();
  //   this.listenToNewInvoice()
  // }

  // start dalia
  ngOnInit() {
    window.addEventListener('online', this.handleOnlineStatus.bind(this));
    window.addEventListener('offline', this.handleOnlineStatus.bind(this));
    this.startTimeRefresh();
    //  window.addEventListener("online", () => {
    //     this.syncPillsToServer();
    //   });

    this.syncService.retryPills$.subscribe(() => {
      this.syncPillsToServer(); // 👈 الفانكشن اللي عندك
    });

    this.dbService.init()
      .then(async () => {
        await this.loadFromIndexedDB();
        // ✅ ثانياً: لو في إنترنت، حمّلي من السيرفر في الخلفية
        if (navigator.onLine) {
          this.syncAllPills();
          // this.fetchPillsData(); // دي هتعمل merge/update وتحدث الـ IndexedDB
        }
      })
      .catch(error => {
        console.error('Error initializing IndexedDB:', error);
        this.loading = true;
      })

    this.listenToNewInvoice();
  }

  listenToNewInvoice() {
    this.newOrder.listenToNewOrder();
    this.newInvoice.listenToNewInvoice();
    this.newOrder.orderAdded$
      .pipe(takeUntil(this.destroy$))
      .subscribe((newOrder) => {
        if (!this.isOnline) return; // Don't process real-time updates when offline

        const data = newOrder.data;
        const invoice = {
          "invoice_number": data.Order.invoice.invoice_number,
          "invoice_print_status": data.Order.invoice.invoice_print_status,
          "order_id": data.Order.order_details.order_id,
          "order_type": data.Order.order_details.order_type,
          "order_number": data.Order.order_details.order_number,
          "order_items_count": data.Order.order_details.order_items_count,
          "order_time": data.Order.invoice.order_time,
          // "invoice_type": data.Order.invoice.invoice_type || 'invoice'
        };

        this.pills = [...this.pills, invoice];
        this.updatePillsByStatus();

        // Save to IndexedDB for offline access
        this.dbService.saveData('pills', this.pills)
          .catch(error => console.error('Error saving to IndexedDB:', error));
      });

    this.newInvoice.invoiceAdded$
      .pipe(takeUntil(this.destroy$))
      .subscribe((newInvoice) => {
        if (!this.isOnline) return; // Don't process real-time updates when offline

        const data = newInvoice.data;
        const invoice = {
          "invoice_number": data.invoice_number,
          "invoice_print_status": data.invoice_print_status,
          "order_id": data.order_id,
          "order_type": data.order_type,
          "order_number": data.order_number,
          "order_items_count": data.order_items_count,
          "order_time": data.order_time,
          // "invoice_type": data.invoice_type || 'invoice'
        };

        // Remove any existing pill with the same invoice_number
        this.pills = this.pills.filter(p => p.invoice_number !== data.invoice_number);
        this.pills = [invoice, ...this.pills];
        this.updatePillsByStatus();

        // Save to IndexedDB for offline access
        this.dbService.saveData('pills', this.pills)
          .catch(error => console.error('Error saving to IndexedDB:', error));
      });
  }

  private handleOnlineStatus() {
    this.isOnline = navigator.onLine;
    // console.log('Network status changed:', this.isOnline ? 'Online' : 'Offline');
    if (this.isOnline && this.usingOfflineData) {
      // When coming back online, refresh data from server
      this.usingOfflineData = false;
      this.fetchPillsData();
    } else if (!this.isOnline) {
      // When going offline, load from IndexedDB
      this.loadFromIndexedDB();
    }
    this.cdr.detectChanges();
  }

  // dalia infinite scroll start
  //  fetchPillsData(): void {
  //     this.loading = false;
  //     this.pillRequestService.getPills().pipe(
  //       finalize(() => {
  //         this.loading = true;
  //       })
  //     ).subscribe({
  //       next: (response) => {
  //         if (response.status) {
  //           // Ensure all pills have an invoice_number before saving
  //          this.pills = response.data.invoices
  //           // 1️⃣ Remove cancelled (filter first)
  //           .filter((pill: any) => !(pill.order_items_count === 0 && pill.payment_status === "unpaid"))
  //           // 2️⃣ Transform credit notes to "returned"
  //           .map((pill: any) => {
  //             if (pill.invoice_type === 'credit_note') {
  //               return {
  //                 ...pill,
  //                 invoice_print_status: 'returned'
  //               };
  //             }
  //             return pill;
  //           });

  //           // ✅ حفظ الفواتير في IndexedDB عند العمل online
  //           this.dbService.saveData('pills', this.pills)
  //             .then(() => {
  //               console.log('Pills data saved to IndexedDB');
  //             })
  //             .catch(error => console.error('Error saving to IndexedDB:', error));

  //             //dalia enhance
  //           // this.updatePillsByStatus();
  //           this.displayedPills = this.pills.slice(0, this.batchSize);
  //             this.updatePillsByStatus();
  //             //end dalia enhance
  //           this.usingOfflineData = false;
  //         }
  //       },
  //       error: (error) => {
  //         console.error('Error fetching pills data:', error);
  //         // If online but API fails, try to load from IndexedDB
  //         this.loadFromIndexedDB();
  //         this.loading = true;
  //       }
  //     });
  //   }
  // async fetchPillsData(): Promise<void> {
  //   console.log('🔄 Fetching pills data from server...');
  //   try {
  //      console.log('🔄 try to cal Api pills data from server...');
  //     // this.pillRequestService.getPills().pipe(
  //     //   finalize(() => console.log('🔁 Server sync finished'))
  //     // ).subscribe({
  //     //   next: async (response) => {
  //     //     if (response.status) {
  //     //        console.log('🔄 get reponse from call Api pills data from server...');
  //     //       const serverPills = response.data.invoices
  //     //         .filter((pill: any) => !(pill.order_items_count === 0 && pill.payment_status === "unpaid"))
  //     //         .map((pill: any) =>
  //     //           pill.invoice_type === 'credit_note'
  //     //             ? { ...pill, invoice_print_status: 'returned' }
  //     //             : pill
  //     //         );
  //     //         console.log('🔄 get pills data from server...');

  //     //       // 🔄 2️⃣ مزامنة السيرفر مع IndexedDB
  //     //       await this.mergeServerAndLocalPills(serverPills);

  //     //       // 🔁 3️⃣ بعد المزامنة، حمّل النسخة المحدثة من IndexedDB (بدون فاصل زمني)
  //     //       await this.loadFromIndexedDB();

  //     //       this.usingOfflineData = false;
  //     //       console.log('✅ Data refreshed from server');
  //     //     }
  //     //   },
  //     //   error: async (error) => {
  //     //     console.error('⚠️ Server fetch failed, showing offline data:', error);
  //     //     // بنسيب البيانات اللي في IndexedDB زي ما هي
  //     //     this.usingOfflineData = true;
  //     //   }
  //     // });
  //   this.pillRequestService.getPillsD().subscribe({
  //   next: async (response) => {
  //     if (response.status) {
  //       const invoices = response.data.invoices;
  //       console.log('🔄 Streaming pills data from server...');
  //       let counter = 0;

  //       for (const pill of invoices) {
  //         // احفظ في IndexedDB
  //         await this.dbService.updatePill(pill);
  //         // console.log('🔄 Pill update to IndexedDB:');

  //         // اعرض أول 50 بسرعة
  //         if (counter < 50) {
  //           this.displayedPills.push(pill);
  //         }

  //         counter++;
  //         if (counter % 200 === 0) {
  //           this.cdr.detectChanges(); // يحدث العرض كل فترة صغيرة
  //         }
  //       }

  //       // بعد ما يخلص
  //       this.updatePillsByStatus();
  //       this.loading = true;
  //       this.cdr.detectChanges();
  //       console.log('✅ Streamed pills loaded and displayed gradually');
  //     }
  //   }
  // });

  //   } catch (err) {
  //     console.error('❌ Error in fetchPillsData:', err);
  //   }
  // }

  // async fetchPillsData(sync: boolean = false): Promise<void> {
  //   if (this.isLoading || (!this.hasMore && !sync)) return;

  //   this.isLoading = true;
  //   console.log(`📥 Fetching page ${this.page}...`);

  //   try {
  //     this.pillRequestService.getPillsD(this.page, this.perPage).subscribe({
  //       next: async (response) => {
  //         if (response.status) {
  //           const invoices = response.data.invoices;
  //           const pagination = response.data.pagination;

  //           console.log(`🔄 Received ${invoices.length} invoices from server (page ${pagination.current_page})`);

  //           // 🧩 1️⃣ مزامنة IndexedDB مع السيرفر (إضافة أو تحديث)
  //           for (const pill of invoices) {
  //             await this.dbService.updatePill(pill);
  //             // console.log('🔄 Pill updated to IndexedDB:', pill.invoice_number);
  //           }

  //           // 🧩 2️⃣ تحميل للعرض فقط الجديد
  //           if (!sync) {
  //             this.displayedPills.push(...invoices);
  //           }

  //           // 🧩 3️⃣ تحديث الحالة
  //           this.updatePillsByStatus();
  //           this.hasMore = pagination.current_page < pagination.last_page;
  //           this.page++;
  //           this.cdr.detectChanges();

  //           console.log(`✅ Synced & displayed page ${pagination.current_page} / ${pagination.last_page}`);
  //         }

  //         this.isLoading = false;
  //       },
  //       error: (err) => {
  //         console.error('⚠️ Server fetch failed, fallback to offline data:', err);
  //         this.isLoading = false;
  //         this.usingOfflineData = true;

  //         if (sync) {
  //           this.loadFromIndexedDB();
  //         }
  //       }
  //     });
  //   } catch (err) {
  //     console.error('❌ Error in fetchPillsData:', err);
  //     this.isLoading = false;
  //   }
  // }

  async fetchPillsData(sync: boolean = false): Promise<void> {
    if (this.isLoading || (!this.hasMore && !sync)) return;

    this.isLoading = true;
    console.log(`📥 Fetching page ${this.page}...`);

    try {
      this.pillRequestService.getPillsD(this.page, this.perPage).subscribe({
        next: async (response) => {
          if (response.status) {
            const invoices = response.data.invoices;
            const pagination = response.data.pagination;

            // إضافة timestamp للبيانات
            const invoicesWithTimestamp = invoices.map((invoice: any) => ({
              ...invoice,
              saved_at: new Date().toISOString(),
              created_at: invoice.created_at || new Date().toISOString(),
              // تحديث order_time بناء على الوقت الحالي
              order_time: this.calculateOrderTime(invoice)
            }));


            console.log(`🔄 Received ${invoices.length} invoices from server (page ${pagination.current_page})`);

            // 🧩 1️⃣ تحديث IndexedDB بكل الفواتير الجديدة
            await Promise.all(invoices.map((p: any) => this.dbService.updatePill(p)));

            // 🧩 2️⃣ تحميل فقط التحديثات الجديدة من IndexedDB
            const updatedPills = await this.dbService.getAll('pills');

            // 🧩 3️⃣ تحديث واجهة العرض بدون reload
            this.displayedPills = updatedPills;
            this.updatePillsByStatus();
            this.cdr.detectChanges();

            // 🧩 4️⃣ تحديث حالة الـ pagination
            this.hasMore = pagination.current_page < pagination.last_page;
            this.page++;

            console.log(`✅ Synced & displayed page ${pagination.current_page} / ${pagination.last_page}`);
          }

          this.isLoading = false;
        },
        error: (err) => {
          console.error('⚠️ Server fetch failed, fallback to offline data:', err);
          this.isLoading = false;
          this.usingOfflineData = true;

          if (sync) {
            this.loadFromIndexedDB();
          }
        }
      });
    } catch (err) {
      console.error('❌ Error in fetchPillsData:', err);
      this.isLoading = false;
    }
  }
  private calculateOrderTime(invoice: any): string {
    if (!invoice.created_at) return invoice.order_time || '0';

    const createdTime = new Date(invoice.created_at).getTime();
    const currentTime = new Date().getTime();
    const diffMinutes = Math.floor((currentTime - createdTime) / (1000 * 60));

    return Math.max(0, diffMinutes).toString();
  }

  async syncAllPills(): Promise<void> {
    console.log('🔁 Starting full sync from server...');
    this.page = 1;
    this.hasMore = true;

    while (this.hasMore) {
      await this.fetchPillsData(true);
      await new Promise(resolve => setTimeout(resolve, 100)); // تأخير خفيف بين الصفحات
    }

    console.log('✅ Full sync completed. IndexedDB is up to date.');
  }





  // dalia infinite scroll end


  onScroll(): void {
    console.log('🌀 Scroll triggered');

    // if (this.isLoading) return;

    // if (this.usingOfflineData) {
    //   const nextIndex = this.displayedPills.length + this.batchSize;
    //   const more = this.pills.slice(this.displayedPills.length, nextIndex);

    //   if (more.length > 0) {
    //     this.displayedPills.push(...more);
    //     this.cdr.detectChanges();
    //     console.log(`📦 Loaded offline batch (${this.displayedPills.length}/${this.pills.length})`);
    //   } else {
    //     console.log('✅ No more offline data to load');
    //   }
    // } else {
    this.fetchPillsData();
    // }
  }






  // dalia infinte scroll start
  // private loadFromIndexedDB() {
  //   this.dbService.getAll('pills')
  //     .then(pills => {
  //       if (pills && pills.length > 0) {
  //         this.pills = pills;
  //         this.usingOfflineData = true;
  //         this.updatePillsByStatus();
  //         console.log('Loaded from offline storage:', pills.length, 'pills');
  //       } else {
  //         this.pills = [];
  //         this.usingOfflineData = false;
  //         this.updatePillsByStatus();
  //         console.log('No offline data available');
  //       }
  //       this.loading = true;
  //       this.cdr.detectChanges();
  //     })
  //     .catch(error => {
  //       console.error('Error loading from IndexedDB:', error);
  //       this.pills = [];
  //       this.usingOfflineData = false;
  //       this.loading = true;
  //       this.cdr.detectChanges();
  //     });
  // }
  private async loadFromIndexedDB() {
    try {
      const pills = await this.dbService.getAll('pills');

      if (pills && pills.length > 0) {
        // تحديث order_time للبيانات المخزنة
        const updatedPills = pills.map(pill => ({
          ...pill,
          order_time: this.calculateOrderTime(pill)
        }));

        this.pills = updatedPills.reverse();
        // this.displayedPills = this.pills.slice(0, this.batchSize);
        this.displayedPills = this.pills;
        this.usingOfflineData = true;
        this.allPillsLoaded = this.pills.length <= this.batchSize; // ✅ أول دفعة بس
        this.updatePillsByStatus();
        console.log(`✅ Loaded ${pills.length} pills from IndexedDB`);
      }


      // if (pills && pills.length > 0) {
      //   this.pills = pills;
      //   this.displayedPills = this.pills.slice(0, this.batchSize);
      //   this.usingOfflineData = true;
      //   this.updatePillsByStatus();
      //   console.log(`✅ Loaded ${pills.length} pills from IndexedDB`);
      // }
      else {
        this.pills = [];
        this.displayedPills = [];
        this.usingOfflineData = false;
        console.log('⚠️ No offline pills found');
      }
    } catch (error) {
      console.error('Error loading from IndexedDB:', error);
      this.pills = [];
      this.displayedPills = [];
      this.usingOfflineData = false;
    } finally {
      this.loading = true;
      this.cdr.detectChanges();
    }

    this.isDataLoaded = true;

  }
  // end dalia infinite scroll


  async syncPillsToServer() {
    console.log("✅  فواتير محتاجة رفع");
    if (!navigator.onLine) return; // ✅ لو أوفلاين متعملش حاجة

    try {
      const offlinePills = await this.dbService.getOfflineUpdatedPills();

      if (!offlinePills.length) {
        console.log("✅ مفيش فواتير محتاجة رفع");
        return;
      }

      console.log("🔄 بيتم رفع الفواتير للأونلاين:", offlinePills);

      for (const pill of offlinePills) {
        try {
          console.log(`⏳ بيتم رفع الفاتورة رقم...`, pill);
          console.log("pill.order_id", pill.order_id);
          console.log("pill.payment_status", pill.payment_status);
          console.log("pill.tracking_status", pill.tracking_status);
          console.log("pill.cash_value", pill.cash_value);
          console.log("pill.credit_value", pill.credit_value);
          console.log("pill.totalll", pill.totalll);
          console.log("pill.tip", pill.invoice_tips);

          // ✅ API call لتحديث الفاتورة
          const response: any = await this.orderService.updateInvoiceStatus(
            pill.order_id,
            pill.payment_status,
            pill.tracking_status,
            pill.cash_value || 0,
            pill.credit_value || 0,
            pill.order_type === "Delivery",
            pill.totalll || 0,
            pill.invoice_tips || 0

          ).toPromise();

          if (response && response.status !== false) {
            // ✅ تحديث الحالة في IndexedDB
            pill.isUpdatedOffline = false;
            pill.isSynced = true;
            await this.dbService.updatePill(pill);

            console.log(`☑️ تم مزامنة الفاتورة رقم ${pill.order_id} بنجاح`);
          } else {
            console.warn(`⚠️ فشل رفع الفاتورة رقم ${pill.order_id}`);
          }
        } catch (err) {
          console.error(`❌ خطأ أثناء رفع الفاتورة رقم ${pill.order_id}:`, err);
        }
      }
    } catch (err) {
      console.error("❌ Error in syncPillsToServer:", err);
    }
  }


  //end dalia
  // listenToNewInvoice() {
  //   this.newOrder.listenToNewOrder();
  //   this.newInvoice.listenToNewInvoice();
  //   this.newOrder.orderAdded$
  //     .pipe(takeUntil(this.destroy$)).subscribe((newOrder) => {
  //       const data = newOrder.data;
  //       const invoice = {
  //         "invoice_number": data.Order.invoice.invoice_number,
  //         "invoice_print_status": data.Order.invoice.invoice_print_status,
  //         "order_id": data.Order.order_details.order_id,
  //         "order_type": data.Order.order_details.order_type,
  //         "order_number": data.Order.order_details.order_number,
  //         "order_items_count": data.Order.order_details.order_items_count,
  //         "order_time": data.Order.invoice.order_time
  //       }
  //       this.pills = [...this.pills, invoice];

  //       this.updatePillsByStatus()
  //       console.log('pills', this.pills);

  //     })
  //   this.newInvoice.invoiceAdded$
  //     .pipe(takeUntil(this.destroy$)).subscribe((newInvoice) => {

  //       const data = newInvoice.data;
  //       const invoice = {
  //         "invoice_number": data.invoice_number,
  //         "invoice_print_status": data.invoice_print_status,
  //         "order_id": data.order_id,
  //         "order_type": data.order_type,
  //         "order_number": data.order_number,
  //         "order_items_count": data.order_items_count,
  //         "order_time": data.order_time
  //       }
  //       // Remove any existing pill with the same invoice_number
  //       this.pills = this.pills.filter(p => p.invoice_number !== data.invoice_number);

  //       this.pills = [invoice, ...this.pills];
  //       this.updatePillsByStatus()

  //     })

  // }
  selectStatusGroup(index: number): void {

    this.selectedStatus = index;
  }
  // fetchPillsData(): void {
  //   this.loading = false;
  //   this.pillRequestService.getPills().pipe(
  //     finalize(() => {
  //       this.loading = true;
  //     })
  //   ).subscribe((response) => {
  //     if (response.status) {
  //       // this.pills = response.data.invoices.filter((pill: { invoice_print_status: string; }) => pill.invoice_print_status !== 'cancelled');
  //       this.pills = response.data.invoices;
  //       this.updatePillsByStatus()
  //     }
  //   });
  // }
  /* fetchPillsData(): void {
    this.loading = false;
    this.pillRequestService.getPills().pipe(
      finalize(() => {
        this.loading = true;
      })
    ).subscribe((response) => {
      if (response.status) {
        this.pills = response.data.invoices
          // 1️⃣ Remove cancelled
          // .filter((pill: any) => (pill.order_items_count == 0 && pill.payment_status == "unpaid"))
          // 2️⃣ Transform credit notes to "returned"
          .map((pill: any) => {
            if (pill.invoice_type === 'credit_note') {
              return {
                ...pill,
                invoice_print_status: 'returned'
              };
            }
            return pill;
          });

        // ✅ Console returned invoices
        const returnedInvoices = this.pills.filter(
          (pill: any) => pill.invoice_print_status === 'returned'
        );
        console.log('Returned invoices:', returnedInvoices);

        this.updatePillsByStatus();
      }
    });
  } */



  // fetchPillsData(): void {
  //   this.loading = false;
  //   this.pillRequestService.getPills().pipe(
  //     finalize(() => {
  //       this.loading = true;
  //     })
  //   ).subscribe((response) => {
  //     if (response.status) {
  //       this.pills = response.data.invoices
  //         // 1️⃣ Remove cancelled (filter first)
  //         .filter((pill: any) => !(pill.order_items_count === 0 && pill.payment_status === "unpaid"))
  //         // 2️⃣ Transform credit notes to "returned"
  //         .map((pill: any) => {
  //           if (pill.invoice_type === 'credit_note') {
  //             return {
  //               ...pill,
  //               invoice_print_status: 'returned'
  //             };
  //           }
  //           return pill;
  //         });

  //       // ✅ Console returned invoices
  //       const returnedInvoices = this.pills.filter(
  //         (pill: any) => pill.invoice_print_status === 'returned'
  //       );
  //       console.log('Returned invoices:', returnedInvoices);

  //       this.updatePillsByStatus();
  //     }
  //   });
  // }

  // private updatePillsByStatus(): void {
  //   const allStatuses = ['hold', 'urgent', 'done'];
  //   const fetchedStatuses = Array.from(
  //     new Set(this.pills.map((pill) => pill.invoice_print_status))
  //   );
  //   const mergedStatuses = Array.from(new Set([...allStatuses, ...fetchedStatuses]));

  //   this.pillsByStatus = mergedStatuses.map((status) => ({
  //     status,
  //     pills: this.pills.filter((pill) => pill.invoice_print_status === status),
  //   }));

  //   this.filteredPillsByStatus = [...this.pillsByStatus];
  //   console.log('pill', this.filteredPillsByStatus);

  //   this.filterPills();
  // }
  // private updatePillsByStatus(): void {
  //   const allStatuses = ['hold', 'urgent', 'done', 'cancelled', 'returned'];
  //   const fetchedStatuses = Array.from(
  //     new Set(this.pills.map((pill) => pill.invoice_print_status))
  //   );
  //   const mergedStatuses = Array.from(new Set([...allStatuses, ...fetchedStatuses]));

  //   this.pillsByStatus = mergedStatuses.map((status) => {
  //     let pillsForStatus = this.pills.filter(pill => pill.invoice_print_status === status);

  //     // Ensure returned tab only has credit notes
  //     if (status === 'returned') {
  //       pillsForStatus = pillsForStatus.filter(pill => pill.invoice_type === 'credit_note');
  //     }

  //     return {
  //       status,
  //       pills: pillsForStatus
  //     };
  //   });

  //   this.filteredPillsByStatus = [...this.pillsByStatus];
  //   this.filterPills();
  // }



  private updatePillsByStatus(): void {
    const allStatuses = ['hold', 'urgent', 'done', 'cancelled', 'returned'];

    // ✅ استخدمي displayedPills بدل pills علشان التمرير التدريجي
    const fetchedStatuses = Array.from(
      new Set(this.displayedPills.map((pill) => pill.invoice_print_status))
    );

    console.log('updatePillsByStatus');

    const mergedStatuses = Array.from(new Set([...allStatuses, ...fetchedStatuses]));

    this.pillsByStatus = mergedStatuses.map((status) => {
      let pillsForStatus = this.displayedPills.filter(
        (pill) => pill.invoice_print_status === status
      );

      // ✅ تأكدي إن returned بيعرض بس فواتير credit_note
      if (status === 'returned') {
        pillsForStatus = pillsForStatus.filter(
          (pill) => pill.invoice_type === 'credit_note'
        );
      }

      return {
        status,
        pills: pillsForStatus,
      };
    });

    this.filteredPillsByStatus = [...this.pillsByStatus];
    this.filterPills();
  }



  getTranslatedStatus(status: string): string {
    const statusTranslations: { [key: string]: string } = {
      hold: 'معلقة',
      urgent: 'طارئة',
      done: 'مكتملة',
      returned: 'مرتجعة',
      cancelled: 'ملغية',

    };

    return statusTranslations[status] || status;
  }
  filterPills() {
    if (!this.pillsByStatus || this.pillsByStatus.length === 0) return;

    const search = this.searchText?.trim().toLowerCase() || '';

    this.filteredPillsByStatus = this.pillsByStatus.map((statusGroup: { status: string, pills: any[] }) => {
      let pills = statusGroup.pills;

      pills = pills.filter(pill => pill.order_type === this.orderTypeFilter);
      if (search) {
        pills = pills.filter(pill =>
          pill.order_number?.toString().toLowerCase().includes(search)
        );
      }

      return {
        status: statusGroup.status,
        pills,
      };
    });

    if (search) {
      const match = this.pills.find(pill =>
        pill.order_number?.toString().toLowerCase().includes(search)
      );

      if (match) {
        this.highlightedPillId = `pill-${match.order_number}`;

        // 👇 Set the order type tab (e.g. dine-in, Delivery, Takeaway , talabat)
        this.orderTypeFilter = match.order_type;

        // 👇 Re-filter pills again now that the tab changed
        this.filteredPillsByStatus = this.pillsByStatus.map((statusGroup: { status: string, pills: any[] }) => {
          let pills = statusGroup.pills;
          pills = pills.filter(pill => pill.order_type === this.orderTypeFilter);
          pills = pills.filter(pill =>
            pill.order_number?.toString().toLowerCase().includes(search)
          );
          return {
            status: statusGroup.status,
            pills,
          };
        });

        // 👇 Set the correct status group tab (hold, urgent, done)
        const statusIndex = this.pillsByStatus.findIndex((group: { status: any; }) => group.status === match.invoice_print_status);
        if (statusIndex !== -1) {
          this.selectedStatus = statusIndex;
        }

        // 👇 Scroll into view
        setTimeout(() => {
          const pillElement = document.getElementById(`pill-${match.order_number}`);
          if (pillElement) {
            pillElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            pillElement.classList.add('highlight-order');
          }
        }, 300);
      }
    }
  }


  //  dalia infinite scroll end
  // private async mergeServerAndLocalPills(serverPills: any[]) {
  //   console.log('🔄 Merging server pills with local IndexedDB...');
  //   // 1️⃣ جيب الفواتير اللي اتعدلت أوفلاين
  //   const offlineUpdated = await this.dbService.getOfflineUpdatedPills();
  //   const offlineIds = offlineUpdated.map(p => p.invoice_number);

  //   // 2️⃣ حلقة على فواتير السيرفر
  //   for (const serverPill of serverPills) {
  //     const localPill = await this.dbService.getPillByInvoiceId(serverPill.invoice_id);

  //     if (!localPill) {
  //       // ➕ فاتورة جديدة من السيرفر
  //       await this.dbService.updatePill(serverPill); // put يشتغل add أو update
  //       continue;
  //     }

  //     // ⛔ لو الفاتورة اتعدلت أوفلاين، متعملش فيها حاجة
  //     if (offlineIds.includes(localPill.invoice_number)) {
  //       continue;
  //     }

  //     // 🔄 لو السيرفر عنده نسخة مختلفة عن النسخة المحلية، نحدثها
  //     if (JSON.stringify(localPill) !== JSON.stringify(serverPill)) {
  //       await this.dbService.updatePill(serverPill);
  //     }
  //   }

  //   // 3️⃣ حذف الفواتير القديمة اللي مش موجودة على السيرفر (إلا لو أوفلاين)
  //   const localAll = await this.dbService.getAll('pills');
  //   for (const local of localAll) {
  //     if (!serverPills.some(sp => sp.invoice_number === local.invoice_number) &&
  //       !offlineIds.includes(local.invoice_number)) {
  //       await this.dbService.deleteFromIndexedDB('pills', local.invoice_number);
  //     }
  //   }

  //   console.log('✅ Local IndexedDB synced with server successfully');
  // }

  getPillsForStatus(statusGroup: any) {
    // console.log('Filtering pills for status:', statusGroup.status, 'with orderTypeFilter:', this.orderTypeFilter);
    return this.displayedPills.filter(
      pill => pill.invoice_print_status === statusGroup.status && pill.order_type === this.orderTypeFilter
    );
  }

  //  dalia infinite scroll end

  // أضف هذه الدالة لتحيين الوقت كل دقيقة
  startTimeRefresh() {
    if (this.usingOfflineData) {
      setInterval(() => {
        this.pills = this.pills.map(pill => ({
          ...pill,
          order_time: this.calculateOrderTime(pill)
        }));
        this.updatePillsByStatus();
        this.cdr.detectChanges();
      }, 60000); // كل دقيقة
    }
  }
}
