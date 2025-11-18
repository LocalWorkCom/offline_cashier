import { Component, OnDestroy, OnInit, ChangeDetectorRef } from '@angular/core';
import { TablesService } from '../services/tables.service';
import { CommonModule, Location } from '@angular/common';
import { Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { TableCrudOperationService } from '../services/pusher/tableCrudOperation';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { finalize } from 'rxjs';
import { IndexeddbService } from '../services/indexeddb.service';


@Component({
  selector: 'app-tables',
  standalone: true,
  imports: [CommonModule, FormsModule, ShowLoaderUntilPageLoadedDirective],
  templateUrl: './tables.component.html',
  styleUrls: ['./tables.component.css'],
})
export class TablesComponent implements OnInit, OnDestroy {
  tables: any[] = [];
  tabless: any[] = [];
  tablesByStatus: { status: number; label: string; tables: any[] }[] = [];
  filteredTablesByStatus: { status: number; label: string; tables: any[] }[] = [];
  selectedStatus: number = -1;
  clickedTableId: number | null = null;
  searchText: string = '';
  loading: boolean = true;
  errorMessage: any;
  private isOnline: boolean = navigator.onLine;

  // Bound event handlers for network status (needed for proper cleanup)
  private boundHandleOnline: () => void;
  private boundHandleOffline: () => void;

  constructor(
    private tablesRequestService: TablesService,
    private router: Router,
    private location: Location,
    private tableOperation: TableCrudOperationService,
    private dbService: IndexeddbService,
    private cdr: ChangeDetectorRef
  ) {
    // Bind event handlers once in constructor for proper cleanup
    this.boundHandleOnline = this.handleOnline.bind(this);
    this.boundHandleOffline = this.handleOffline.bind(this);
  }

  ngOnInit(): void {
    // Pre-initialize IndexedDB for faster offline loading
    this.dbService.init().catch(err => {
      console.error('IndexedDB init error:', err);
    });

    // Setup network status listeners
    this.setupNetworkListeners();

    if (this.isOnline) {
      this.fetchTablesData();
    } else {
      this.loadTablesFromIndexedDB();
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
    }

    this.loadClickedTable();
    this.listenToNewTable();
    this.listenOnTableChangeStatus();
  }

  // Setup network status change listeners
  private setupNetworkListeners(): void {
    window.addEventListener('online', this.boundHandleOnline);
    window.addEventListener('offline', this.boundHandleOffline);
  }

  // Handle when connection comes back online
  private handleOnline(): void {
    console.log('🌐 Connection restored - back online');
    this.isOnline = true;

    // Clear error message if it was about connection failure
    if (this.errorMessage && this.errorMessage.includes('فشل فى الاتصال')) {
      this.errorMessage = '';
      // Trigger change detection to update UI
      this.cdr.detectChanges();
    }

    // Refresh tables data from API
    this.fetchTablesData();
  }

  // Handle when connection goes offline
  private handleOffline(): void {
    console.log('📴 Connection lost - going offline');
    this.isOnline = false;

    // Set error message
    this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
    // Trigger change detection to update UI
    this.cdr.detectChanges();

    // Try to load from IndexedDB if no tables are loaded
    if (this.tables.length === 0) {
      this.loadTablesFromIndexedDB();
    }
  }

  // Load tables from IndexedDB when offline
  private loadTablesFromIndexedDB(): void {
    this.dbService.init().then(() => {
      return this.dbService.getAll('tables');
    }).then(tables => {
      if (tables && tables.length > 0) {
        console.log('Tables loaded from IndexedDB:', tables.length);

        // Process tables data
        this.tables = tables.map((table: any) => ({
          ...table,
          status: Number(table.status),
        }));

        this.tabless = [...this.tables];

        // Update tables by status
        this.tablesByStatus = [
          {
            status: 1,
            label: 'متاحة',
            tables: this.tables.filter((t) => t.status === 1),
          },
          {
            status: 2,
            label: 'مشغولة',
            tables: this.tables.filter((t) => t.status === 2),
          },
        ];

        // Initialize filtered list with all tables
        this.filteredTablesByStatus = JSON.parse(
          JSON.stringify(this.tablesByStatus)
        );

        this.loading = true;
      } else {
        this.loading = true;
        console.warn('No tables available offline');
      }
    }).catch(err => {
      console.error('Error loading tables from IndexedDB:', err);
      this.loading = true;
    });
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

            this.tablesByStatus = [
              {
                status: 1,
                label: 'متاحة',
                tables: this.tables.filter((t) => t.status === 1),
              },
              {
                status: 2,
                label: 'مشغولة',
                tables: this.tables.filter((t) => t.status === 2),
              },
            ];

            // Initialize filtered list with all tables
            this.filteredTablesByStatus = JSON.parse(
              JSON.stringify(this.tablesByStatus)
            );

            // Save to IndexedDB for offline access
            this.dbService.saveData('tables', this.tables).then(() => {
              console.log('Tables saved to IndexedDB');
            }).catch(err => {
              console.error('Error saving tables to IndexedDB:', err);
            });
          }
        },
        error: (err) => {
          console.error('Error fetching tables:', err);
          this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';

          // Try to load from IndexedDB as fallback
          if (this.tables.length === 0) {
            this.loadTablesFromIndexedDB();
          }
        },
      });
  }

  updateTableStatusLists() {
    this.tablesByStatus = [
      {
        status: 1,
        label: 'متاحة',
        tables: this.tables.filter((t) => t.status == 1),
      },
      {
        status: 2,
        label: 'مشغولة',
        tables: this.tables.filter((t) => t.status == 2),
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

    if (!selectedTable) {
      console.warn('Table not found:', tableId);
      return;
    }

    if (selectedTable.status === 2) {
      alert('هذه الطاولة مشغولة، يرجى اختيار طاولة أخرى.');
      return;
    }

    localStorage.setItem('selected_table', JSON.stringify(selectedTable));
    localStorage.setItem('table_id', JSON.stringify(tableId));
    localStorage.setItem(
      'table_number',
      JSON.stringify(selectedTable.table_number)
    );
    this.router.navigate(['/home']);
    if (localStorage.getItem('cameFromSideDetails') === 'true') {
      this.router.navigate(['/home']);
      localStorage.removeItem('cameFromSideDetails');
    }
  }
  ngOnDestroy(): void {
    this.tableOperation.stopListeningForChangeTableStatus();
    this.tableOperation.stopListeningForNewTable();

    // Remove network event listeners
    window.removeEventListener('online', this.boundHandleOnline);
    window.removeEventListener('offline', this.boundHandleOffline);
  }

  get activeTables() {
    return this.selectedStatus === -1
      ? this.tabless
      : this.filteredTablesByStatus[this.selectedStatus]?.tables || [];
  }



  trackByTableId(index: number, table: any) {
    return table.id;
  }

}
