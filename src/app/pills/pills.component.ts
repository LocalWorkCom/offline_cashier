import { Component, OnDestroy, OnInit } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { PillsService } from '../services/pills.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { NewOrderService } from '../services/pusher/newOrder';
import { finalize, Subject, takeUntil } from 'rxjs';
import { NewInvoiceService } from '../services/pusher/newInvoice';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { IndexeddbService } from '../services/indexeddb.service';
import { ChangeDetectorRef } from '@angular/core';
@Component({
  selector: 'app-pills',
  imports: [RouterLink, ShowLoaderUntilPageLoadedDirective, RouterLinkActive, CommonModule, FormsModule],
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
  errorMessage: any;

  private destroy$ = new Subject<void>();
  loading: boolean = true;
  constructor(private pillRequestService: PillsService, private newOrder: NewOrderService, private newInvoice: NewInvoiceService, private dbService: IndexeddbService,
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
    // window.addEventListener('online', this.handleOnlineStatus.bind(this));
    // window.addEventListener('offline', this.handleOnlineStatus.bind(this));

    this.fetchPillsData();
    // this.dbService.init().then(() => {
      if (navigator.onLine) {
        this.fetchPillsData(); // جلب وحفظ البيانات في IndexedDB
      } else {
        // this.loadFromIndexedDB(); // جلب البيانات من IndexedDB عند العمل offline
              this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';

      }
    // }).catch(error => {
    //   console.error('Error initializing IndexedDB:', error);
    //   this.loading = true;
    // });

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
        // this.dbService.saveData('pills', this.pills)
        //   .catch(error => console.error('Error saving to IndexedDB:', error));
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
        // this.dbService.saveData('pills', this.pills)
        //   .catch(error => console.error('Error saving to IndexedDB:', error));
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
      // this.loadFromIndexedDB();
    }
    this.cdr.detectChanges();
  }

  fetchPillsData(): void {
    this.loading = false;
    this.pillRequestService.getPills().pipe(
      finalize(() => {
        this.loading = true;
      })
    ).subscribe({
      next: (response) => {
        if (response.status) {
          // Ensure all pills have an invoice_number before saving
          this.pills = response.data.invoices
            // 1️⃣ Remove cancelled (filter first)
            .filter((pill: any) => !(pill.order_items_count === 0 && pill.payment_status === "unpaid"))
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

          // ✅ حفظ الفواتير في IndexedDB عند العمل online
          // First clear existing pills, then save new ones
          // this.dbService.clearPills()
          //   .then(() => {
          //     return this.dbService.saveData('pills', this.pills);
          //   })
          //   .then(() => {
          //     console.log('Pills data saved to IndexedDB');
          //   })
          //   .catch(error => console.error('Error clearing and saving pills:', error));

          this.updatePillsByStatus();
          this.usingOfflineData = false;
        }
      },

      error: (error) => {
        console.error('Error fetching pills data:', error);
        // If online but API fails, try to load from IndexedDB
        // this.loadFromIndexedDB();
        this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';

        this.loading = true;
      }
    });
  }

  private loadFromIndexedDB() {
    this.dbService.getAll('pills')
      .then(pills => {
        if (pills && pills.length > 0) {
          this.pills = pills;
          this.usingOfflineData = true;
          this.updatePillsByStatus();
          console.log('Loaded from offline storage:', pills.length, 'pills');
        } else {
          this.pills = [];
          this.usingOfflineData = false;
          this.updatePillsByStatus();
          console.log('No offline data available');
        }
        this.loading = true;
        this.cdr.detectChanges();
      })
      .catch(error => {
        console.error('Error loading from IndexedDB:', error);
        this.pills = [];
        this.usingOfflineData = false;
        this.loading = true;
        this.cdr.detectChanges();
      });
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
  private updatePillsByStatus(): void {
    const allStatuses = ['hold', 'urgent', 'done', 'cancelled', 'returned'];
    const fetchedStatuses = Array.from(
      new Set(this.pills.map((pill) => pill.invoice_print_status))
    );
    const mergedStatuses = Array.from(new Set([...allStatuses, ...fetchedStatuses]));

    this.pillsByStatus = mergedStatuses.map((status) => {
      let pillsForStatus = this.pills.filter(pill => pill.invoice_print_status === status);

      // Ensure returned tab only has credit notes
      if (status === 'returned') {
        pillsForStatus = pillsForStatus.filter(pill => pill.invoice_type === 'credit_note');
      }

      return {
        status,
        pills: pillsForStatus
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

        // 👇 Set the order type tab (e.g. dine-in, Delivery, Takeaway)
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







}
