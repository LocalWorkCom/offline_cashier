import { Component, OnDestroy, OnInit } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { PillsService } from '../services/pills.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { NewOrderService } from '../services/pusher/newOrder';
import { finalize, Subject, takeUntil } from 'rxjs';
import { NewInvoiceService } from '../services/pusher/newInvoice';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
@Component({
  selector: 'app-pills',
  imports: [RouterLink, ShowLoaderUntilPageLoadedDirective, RouterLinkActive, CommonModule, FormsModule],
  templateUrl: './pills.component.html',
  styleUrl: './pills.component.css',
})
export class PillsComponent implements OnInit, OnDestroy {
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
  constructor(private pillRequestService: PillsService, private newOrder: NewOrderService, private newInvoice: NewInvoiceService) { }
  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
    this.newOrder.stopListening();
    this.newInvoice.stopListening();
  }
  ngOnInit() {
    this.fetchPillsData();
    this.listenToNewInvoice()
  }
  listenToNewInvoice() {
    this.newOrder.listenToNewOrder();
    this.newInvoice.listenToNewInvoice();
    this.newOrder.orderAdded$
      .pipe(takeUntil(this.destroy$)).subscribe((newOrder) => {
        const data = newOrder.data;
        const invoice = {
          "invoice_number": data.Order.invoice.invoice_number,
          "invoice_print_status": data.Order.invoice.invoice_print_status,
          "order_id": data.Order.order_details.order_id,
          "order_type": data.Order.order_details.order_type,
          "order_number": data.Order.order_details.order_number,
          "order_items_count": data.Order.order_details.order_items_count,
          "order_time": data.Order.invoice.order_time
        }
        this.pills = [...this.pills, invoice];

        this.updatePillsByStatus()
        console.log('pills', this.pills);

      })
    this.newInvoice.invoiceAdded$
      .pipe(takeUntil(this.destroy$)).subscribe((newInvoice) => {

        const data = newInvoice.data;
        const invoice = {
          "invoice_number": data.invoice_number,
          "invoice_print_status": data.invoice_print_status,
          "order_id": data.order_id,
          "order_type": data.order_type,
          "order_number": data.order_number,
          "order_items_count": data.order_items_count,
          "order_time": data.order_time
        }
        // Remove any existing pill with the same invoice_number
        this.pills = this.pills.filter(p => p.invoice_number !== data.invoice_number);

        this.pills = [invoice, ...this.pills];
        this.updatePillsByStatus()

      })

  }
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
fetchPillsData(): void {
  this.loading = false;
  this.pillRequestService.getPills().pipe(
    finalize(() => {
      this.loading = true;
    })
  ).subscribe((response) => {
    if (response.status) {
      this.pills = response.data.invoices
        // 1️⃣ Remove cancelled
        // .filter((pill: any) => pill.invoice_print_status !== 'cancelled')
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
}



  private updatePillsByStatus(): void {
    const allStatuses = ['hold', 'urgent', 'done'];
    const fetchedStatuses = Array.from(
      new Set(this.pills.map((pill) => pill.invoice_print_status))
    );
    const mergedStatuses = Array.from(new Set([...allStatuses, ...fetchedStatuses]));

    this.pillsByStatus = mergedStatuses.map((status) => ({
      status,
      pills: this.pills.filter((pill) => pill.invoice_print_status === status),
    }));

    this.filteredPillsByStatus = [...this.pillsByStatus];
    console.log('pill', this.filteredPillsByStatus);

    this.filterPills();
  }
// private updatePillsByStatus(): void {
//   const allStatuses = ['hold', 'urgent', 'done', 'returned'];
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
