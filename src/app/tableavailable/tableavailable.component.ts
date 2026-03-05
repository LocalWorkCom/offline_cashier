import { Component, OnDestroy, OnInit, Inject, PLATFORM_ID } from '@angular/core';
import { TablesService } from '../services/tables.service';
import { CommonModule, Location, isPlatformBrowser } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { TableCrudOperationService } from '../services/pusher/tableCrudOperation';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { finalize } from 'rxjs';
import { COLORS } from 'html2canvas/dist/types/css/types/color';

@Component({
  selector: 'app-tables',
  standalone: true,
  imports: [CommonModule, FormsModule, ShowLoaderUntilPageLoadedDirective],
  templateUrl: './tableavailable.component.html',
  styleUrls: ['./tableavailable.component.css'],
})
export class TableAvailableComponent implements OnInit, OnDestroy {
  tables: any[] = [];
  tabless: any[] = [];
  tablesByStatus: { status: number; label: string; tables: any[] }[] = [];
  filteredTablesByStatus: { status: number; label: string; tables: any[] }[] = [];
  selectedStatus: number = -1;
  clickedTableId: number | null = null;
  searchText: string = '';
  loading: boolean = true;
  errorMessage: any;
  orderId: string | null = null;

  constructor(
    private tablesRequestService: TablesService,
    private router: Router,
    private location: Location,
    private tableOperation: TableCrudOperationService,
    private route: ActivatedRoute,
    @Inject(PLATFORM_ID) private platformId: Object
  ) { }

  ngOnInit(): void {
    // Get order_id from route parameter if available
    this.route.params.subscribe(params => {
      if (params['orderId']) {
        this.orderId = params['orderId']; // UUID — do NOT convert to number
        console.log('Order ID from route:', this.orderId);
      }
    });

    if (navigator.onLine) {
      this.fetchTablesData();
    }
    else {
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
    }
    this.loadClickedTable();
    this.listenToNewTable();
    this.listenOnTableChangeStatus();
  }
  listenToNewTable() {
    this.tableOperation.newTable();

    this.tableOperation.newTable$.subscribe((newTable) => {
      console.log(newTable, 'data');

      const table = newTable.table;
      this.tables = [...this.tables, table];
      this.updateTableStatusLists();
    });
  }
  /** Fetch tables from API */
  fetchTablesData(): void {
    this.loading = false;
    this.tablesRequestService
      .getTables()
      .pipe(
        finalize(() => {
          this.loading = true;
        })
      )
      .subscribe({
        next: (response) => {
          console.log(response.data, 'tables');
          this.tabless = response.data;
          if (response.status) {
            this.tables = response.data.map((table: any) => ({
              ...table,
              status: Number(table.status),
            }));

            // Filter only available tables (status === 1)
            const availableTables = this.tables.filter((t) => t.status === 1);

            this.tablesByStatus = [
              {
                status: 1,
                label: 'متاحة',
                tables: availableTables,
              },
            ];

            // Initialize filtered list with only available tables
            this.filteredTablesByStatus = JSON.parse(
              JSON.stringify(this.tablesByStatus)
            );
          }
        },
        error: (err) => {
          console.error('Error fetching tables:', err);
        },
      });
  }

  updateTableStatusLists() {
    // Filter only available tables (status === 1)
    const availableTables = this.tables.filter((t) => t.status === 1);

    this.tablesByStatus = [
      {
        status: 1,
        label: 'متاحة',
        tables: availableTables,
      },
    ];
    // Trigger UI update
    this.filteredTablesByStatus = [
      ...this.tablesByStatus.map((group) => ({
        ...group,
        tables: [...group.tables],
      })),
    ];
  }
  listenOnTableChangeStatus() {
    this.tableOperation.listenToTable();

    this.tableOperation.tableChanged$.subscribe((changedTable) => {
      const table = changedTable.table;
      const index = this.tables.findIndex((t) => t.id === table.id);
      console.log('changed table', changedTable);
      if (index !== -1) {
        if (changedTable.status === 'updated') {
          this.tables = [
            ...this.tables.slice(0, index),
            { ...table },
            ...this.tables.slice(index + 1),
          ];
        } else if (changedTable.status === 'delete') {
          this.tables = this.tables.filter((t) => t.id !== table.id);
        }
        this.tabless = [...this.tables]
      } else {
        // Add
        this.tables = [...this.tables, table];
        this.tabless = [...this.tables]
      }

      // 🔥 Recompute derived arrays
      this.updateTableStatusLists();
    });

  }
  loadClickedTable(): void {
    const savedTableId = localStorage.getItem('clickedTableId');
    if (savedTableId) {
      this.clickedTableId = JSON.parse(savedTableId);
    }
  }

  filterTables(): void {
    const searchValue = this.searchText.trim();

    if (!searchValue) {
      this.filteredTablesByStatus = JSON.parse(
        JSON.stringify(this.tablesByStatus)
      );
      this.selectedStatus = -1; // Reset tab selection
      return;
    }

    const filtered = this.tablesByStatus
      .map((statusGroup) => ({
        ...statusGroup,
        tables: statusGroup.tables.filter((table) =>
          table.table_number.toString().includes(searchValue)
        ),
      }))
      .filter((statusGroup) => statusGroup.tables.length > 0);

    this.filteredTablesByStatus = filtered;

    if (this.filteredTablesByStatus.length > 0) {
      this.selectedStatus = 0;
    } else {
      this.selectedStatus = -1;
    }
  }

  selectStatusGroup(index: number): void {
    this.selectedStatus = index;
  }

  getStatusText(status: number): string {
    return status === 1 ? 'متاحة' : status === 2 ? 'مشغولة' : 'غير معروف';
  }

  onTableClick(tableId: number): void {
    const selectedTable = this.tables.find((table) => table.id === tableId);

    console.log(selectedTable, 'selectedTable');

    if (!selectedTable) {
      console.warn('Table not found:', tableId);
      return;
    }

    if (selectedTable.status === 2) {
      alert('هذه الطاولة مشغولة، يرجى اختيار طاولة أخرى.');
      return;
    }
    // Use order_id from route if available, otherwise use table's order_id
    const orderIdToUse = this.orderId || selectedTable.order_id;
    this.tablesRequestService.updateTableStatus(tableId, orderIdToUse).subscribe({
      next: (response) => {
        console.log(response, 'response');
        if (response.status) {
          // Show success modal
          if (isPlatformBrowser(this.platformId)) {
            import('bootstrap').then(({ Modal }) => {
              const modalElement = document.getElementById('successTableModal');
              if (modalElement) {
                const successModal = new Modal(modalElement);
                successModal.show();

                // Navigate to orders page after modal is shown
                setTimeout(() => {
                  successModal.hide();
                  this.router.navigate(['/orders']);
                }, 2000);
              }
            });
          }
        }
      },
      error: (err) => {
        console.error('Error updating table status:', err);
      }
    });


  }
  ngOnDestroy(): void {
    this.tableOperation.stopListeningForChangeTableStatus();
    this.tableOperation.stopListeningForNewTable();
  }

  get activeTables() {
    // Always return only available tables (status === 1)
    if (this.selectedStatus === -1) {
      return this.tabless.filter((t) => t.status === 1);
    }
    return this.filteredTablesByStatus[this.selectedStatus]?.tables || [];
  }



  trackByTableId(index: number, table: any) {
    return table.id;
  }

  onTableOrderDetailsClick(tableId: number): void {
    console.log(tableId, 'tableId');

    //get all orders for this table
    this.tablesRequestService.getOrdersByTableId(tableId).subscribe({
      next: (response) => {
        // Check if response has orders
        if (response && response.data) {
          // Get the first order's ID
          const firstOrder = response.data;
          const orderId = firstOrder.order_id;
          if (orderId) {
            // Navigate to order details page
            this.router.navigate(['/order-details/', orderId]);
          } else {
            console.warn('Order ID not found in response');
            alert('لم يتم العثور على معرف الطلب');
          }
        } else {
          alert('لا توجد طلبات لهذه الطاولة');
        }
      },
      error: (err) => {
        console.error('Error fetching orders:', err);
        alert('حدث خطأ أثناء جلب الطلبات');
      }
    });
  }

}
