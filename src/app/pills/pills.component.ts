import { Component, OnDestroy, OnInit } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { PillsService } from '../services/pills.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { NewOrderService } from '../services/pusher/newOrder';
import { debounceTime, distinctUntilChanged, finalize, Subject, switchMap, takeUntil, timer } from 'rxjs';
import { NewInvoiceService } from '../services/pusher/newInvoice';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { IndexeddbService } from '../services/indexeddb.service';
import { ChangeDetectorRef } from '@angular/core';
import { OrderListDetailsService } from '../services/order-list-details.service';
@Component({
  selector: 'app-pills',
  imports: [
    RouterLink,
    ShowLoaderUntilPageLoadedDirective,
    RouterLinkActive,
    CommonModule,
    FormsModule,
  ],
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
    done: 'مكتملة',
  };
  selectedStatusLabel: string = 'all';
  searchOrderNumber: string = '';
  searchText: any;
  filteredPillsByStatus: any[] | undefined;
  orderType: any;
  orderTypeFilter: string = 'all';
  highlightedPillId: string | null = null;
  errorMessage: any;
  invoiceTypeCounts: any = {};
  invoiceStatusCounts: any = {};

  currentPage: number = 1;
  hasMoreInvoices: boolean = true;
  totalInvoicesCount: number = 0;
  isLoadMoreLoading: boolean = false;
  private searchSubject = new Subject<string>();

  private destroy$ = new Subject<void>();
  loading: boolean = true;
  constructor(
    private pillRequestService: PillsService,
    private newOrder: NewOrderService,
    private newInvoice: NewInvoiceService,
    private dbService: IndexeddbService,
    private _OrderListDetailsService: OrderListDetailsService,
    private cdr: ChangeDetectorRef
  ) {}
  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
    // Do NOT call stopListening() - the listener is managed globally in app.component.ts
    // this.newOrder.stopListening();
    // this.newInvoice.stopListening();
  }

  // ngOnInit() {
  //   this.fetchPillsData();
  //   this.listenToNewInvoice()
  // }

  // start dalia
  ngOnInit() {
    this.isOnline = navigator.onLine;
    if (this.isOnline) {
      this.fetchPillsData();
      this.fetchInvoiceCounts();
    } else {
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
    }
    // }).catch(error => {
    //   console.error('Error initializing IndexedDB:', error);
    //   this.loading = true;
    // });

    this.searchSubject.pipe(
      debounceTime(500),
      distinctUntilChanged(),
      takeUntil(this.destroy$)
    ).subscribe(() => {
      this.fetchPillsData();
    });

    this.listenToNewInvoice();
  }

  listenToNewInvoice() {
    this.newOrder.listenToNewOrder();
    this.newInvoice.listenToNewInvoice();

    this.newOrder.orderAdded$
      .pipe(
        takeUntil(this.destroy$),
        switchMap((newOrder) =>
          timer(2000).pipe(
            switchMap(() =>
              this._OrderListDetailsService.NewgetOrderById(newOrder.order_id)
            )
          )
        )
      )
      .subscribe((newOrder) => {
        if (!this.isOnline) return; // Don't process real-time updates when offline
        console.log(newOrder);

        const data = newOrder.data.order;
        console.log(data);
        const invoice = {
          invoice_number: data.order_details.invoice.invoice_number,
          invoice_id: data.order_details.invoice.invoice_id,
          invoice_print_status: data.order_details.invoice.invoice_print_status,
          order_id: data.order_details.order_id,
          order_type: data.order_details.order_type,
          order_number: data.order_details.order_number,
          order_items_count: data.order_details.order_items_count,
          order_time: data.order_details.invoice.order_time,
          payment_status:data.order_details.payment_status,
          table_number:data.order_details.table_number,
          // "invoice_type": data.Order.invoice.invoice_type || 'invoice'
        };
        console.log(invoice, 'invoice');
        this.pills = [invoice,...this.pills];
        console.log(this.pills, 'this.pills');

        this.updatePillsByStatus();
        this.cdr.detectChanges();

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
          invoice_number: data.invoice_number,
          invoice_print_status: data.invoice_print_status,
          order_id: data.order_id,
          order_type: data.order_type,
          order_number: data.order_number,
          order_items_count: data.order_items_count,
          order_time: data.order_time,
          // "invoice_type": data.invoice_type || 'invoice'
        };

        // Remove any existing pill with the same invoice_number
        this.pills = this.pills.filter(
          (p) => p.invoice_number !== data.invoice_number
        );
        this.pills = [invoice, ...this.pills];
        this.updatePillsByStatus();

        // Save to IndexedDB for offline access
        // this.dbService.saveData('pills', this.pills)
        //   .catch(error => console.error('Error saving to IndexedDB:', error));
      });
       this.cdr.detectChanges();
  }
  /*   listenToNewInvoice() {
    this.newOrder.listenToNewOrder();
    this.newInvoice.listenToNewInvoice();

    this.newOrder.orderAdded$
      .pipe(takeUntil(this.destroy$))
      .subscribe((newOrder) => {
        if (!this.isOnline) return; // Don't process real-time updates when offline
console.log(newOrder);

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
 */
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

  fetchPillsData(isLoadMore: boolean = false): void {
    if (!isLoadMore) {
      this.loading = false;
      this.currentPage = 1;
      this.pills = [];
    } else {
      this.isLoadMoreLoading = true;
    }

    const apiStatusMap: any = {
      'hold': 'unpaid',
      'done': 'paid',
      'cancelled': 'cancelled',
      'returned': 'returned'
    };
    const statusParam = apiStatusMap[this.selectedStatusLabel] || this.selectedStatusLabel;

    this.pillRequestService
      .getPillsV2(this.currentPage, this.searchOrderNumber, this.orderTypeFilter, 28, statusParam)
      .pipe(
        finalize(() => {
          this.loading = true;
          this.isLoadMoreLoading = false;
        }),
        takeUntil(this.destroy$)
      )
      .subscribe({
        next: (response) => {
          if (response.status && response.data.invoices) {
            const newInvoices = response.data.invoices;

            if (isLoadMore) {
              this.pills = [...this.pills, ...newInvoices];
            } else {
              this.pills = newInvoices;
            }

            this.totalInvoicesCount = response.data.pagination?.total || 0;
            this.hasMoreInvoices = response.data.pagination?.has_more || false;

            this.updatePillsByStatus();
            this.usingOfflineData = false;
            this.cdr.detectChanges();
          } else {
            console.warn('No invoices found in API response.');
            if (!isLoadMore) {
              this.pills = [];
              this.updatePillsByStatus();
            }
            this.hasMoreInvoices = false;
            this.cdr.detectChanges();
          }
        },

        error: (error) => {
          console.error('Error fetching pills data:', error);
          this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
          this.cdr.detectChanges();
        },
      });
  }

  onSearchChange(): void {
    this.searchSubject.next(this.searchOrderNumber);
  }

  loadMoreInvoices(): void {
    if (this.hasMoreInvoices && !this.isLoadMoreLoading) {
      this.currentPage++;
      this.fetchPillsData(true);
    }
  }

  fetchInvoiceCounts(): void {
    const typeParam = this.orderTypeFilter;
    this.pillRequestService.getTypeStatusCounts(typeParam).subscribe({
      next: (response) => {
        if (response.status && response.data) {
          const typesMap: any = {};
          if (response.data.types) {
            response.data.types.forEach((item: any) => {
              typesMap[item.type] = item.count;
            });
          }
          this.invoiceTypeCounts = typesMap;

          const statusMap: any = {};
          if (response.data.statuses) {
            response.data.statuses.forEach((item: any) => {
              statusMap[item.status] = item.count;
            });
          }
          this.invoiceStatusCounts = statusMap;
        }
      },
      error: (err) => {
        console.error('Error fetching invoice counts:', err);
      }
    });
  }

  getInvoiceTypeCount(type: string): number {
    if (type === 'all') {
      return Object.values(this.invoiceTypeCounts).reduce((sum: number, c: any) => sum + (Number(c) || 0), 0);
    }
    return this.invoiceTypeCounts[type] || 0;
  }

  getInvoiceStatusCount(status: string): number {
    if (status === 'all') {
      return this.totalInvoicesCount;
    }
    const apiStatusMap: any = {
      'hold': 'unpaid', 
      'done': 'paid',
      'cancelled': 'cancelled',
      'returned': 'returned'
    };
    const apiStatus = apiStatusMap[status] || status;
    return this.invoiceStatusCounts[apiStatus] || 0;
  }

  selectOrderTypeFilter(type: string): void {
    this.orderTypeFilter = type;
    this.currentPage = 1;
    this.fetchPillsData();
    this.fetchInvoiceCounts();
  }

  private loadFromIndexedDB() {
    this.dbService
      .getAll('pills')
      .then((pills) => {
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
      .catch((error) => {
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
    const allStatuses = ['all', 'hold', 'done', 'cancelled', 'returned'];
    this.selectedStatusLabel = allStatuses[index] || 'all';
    this.fetchPillsData();
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
    const allStatuses = ['all', 'hold', 'done', 'cancelled', 'returned'];

    this.pillsByStatus = allStatuses.map((status) => {
      // 1. If 'all' group, show everything
      if (status === 'all') {
        return { status, pills: this.pills };
      }

      // 2. If the current active tab matches this status group, show all items from the server response
      if (this.selectedStatusLabel === status) {
        return { status, pills: this.pills };
      }

      // 3. Otherwise, filter locally based on the print status (mainly for the 'All' tab view)
      return {
        status,
        pills: this.pills.filter(
          (pill) => (pill.invoice_print_status || '').toLowerCase() === status
        ),
      };
    });

    this.filterPills();
  }

  getTranslatedStatus(status: string): string {
    const statusTranslations: { [key: string]: string } = {
      all: 'الكل',
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

    this.filteredPillsByStatus = this.pillsByStatus;

    const search = this.searchOrderNumber?.trim().toLowerCase();
    if (search && this.pills.length > 0) {
      const match = this.pills[0]; // Take first match since API filtered it

      if (match) {
        this.highlightedPillId = `pill-${match.order_number}`;

        // 👇 Set the correct status group tab (hold, urgent, done)
        const statusIndex = this.pillsByStatus.findIndex(
          (group: { status: any }) =>
            group.status === match.invoice_print_status
        );
        if (statusIndex !== -1) {
          this.selectedStatus = statusIndex;
        }

        // 👇 Scroll into view
        setTimeout(() => {
          const pillElement = document.getElementById(
            `pill-${match.order_number}`
          );
          if (pillElement) {
            pillElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            pillElement.classList.add('highlight-order');
          }
        }, 300);
      }
    }
  }
}
