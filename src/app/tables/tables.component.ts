import { Component, OnDestroy, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { TablesService } from '../services/tables.service';
import { CommonModule, Location } from '@angular/common';
import { Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { TableCrudOperationService } from '../services/pusher/tableCrudOperation';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { finalize } from 'rxjs';
import { IndexeddbService } from '../services/indexeddb.service';
import { ChangeDetectorRef } from '@angular/core';
import { Subscription, fromEvent, merge, of } from 'rxjs';
import { map, tap } from 'rxjs/operators';

@Component({
  selector: 'app-tables',
  standalone: true,
  imports: [CommonModule, FormsModule, ShowLoaderUntilPageLoadedDirective],
  templateUrl: './tables.component.html',
  styleUrls: ['./tables.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class TablesComponent implements OnInit, OnDestroy {
  tables: any[] = [];
  tabless: any[] = [];
  tablesByStatus: { status: number; label: string; tables: any[] }[] = [];
  filteredTablesByStatus: { status: number; label: string; tables: any[] }[] = [];
  // Incremental rendering: render first chunk quickly, reveal rest gradually
  visibleCount = 12;
  selectedStatus: number = -1;
  clickedTableId: number | null = null;
  searchText: string = '';
  loading: boolean = true;
  isOnline: boolean = navigator.onLine;
  offlineMode: boolean = false;
  private onlineStatusSubscription!: Subscription;
  private tableSubscriptions: Subscription[] = [];
  constructor(
    private tablesRequestService: TablesService,
    private router: Router,
    private location: Location,
    private dbService: IndexeddbService,
    private cdr: ChangeDetectorRef,
    private tableOperation: TableCrudOperationService
  ) {}

  async ngOnInit(): Promise<void> {
    // Ensure DB is ready and online status monitoring is active
    await this.initDatabase();
    this.setupOnlineStatusMonitoring();

    await this.fetchTablesData();
    this.loadClickedTable();
    this.listenToNewTable();
    this.listenOnTableChangeStatus();

    // Schedule gradual reveal of remaining items to reduce first paint cost
    this.scheduleIncrementalReveal();
  }

  //start dalia
   private async initDatabase(): Promise<void> {
    try {
      await this.dbService.init();
      console.log('IndexedDB initialized successfully');
    } catch (error) {
      console.error('Failed to initialize IndexedDB:', error);
    }
  }
 // Monitor online/offline status
  private setupOnlineStatusMonitoring(): void {
    this.onlineStatusSubscription = merge(
      of(navigator.onLine),
      fromEvent(window, 'online').pipe(map(() => true)),
      fromEvent(window, 'offline').pipe(map(() => false))
    ).subscribe((online: boolean) => {
      this.handleOnlineStatusChange(online);
    });
  }
  private handleOnlineStatusChange(online: boolean): void {
    if (this.isOnline !== online) {
      this.isOnline = online;
      this.offlineMode = !online;
      console.log(`App is ${online ? 'online' : 'offline'}`);

      if (online) {
        // When coming back online, sync data
        this.syncData();
      }

      this.cdr.detectChanges();
    }
  }
    /** Sync data when coming back online */
  private syncData(): void {
    // Re-fetch data from API
    this.fetchFromAPI();

    // Re-setup listeners
    this.setupTableListeners();
  }

  // Setup table listeners based on online status
  private setupTableListeners(): void {
    if (this.isOnline) {
      this.listenToNewTable();
      this.listenOnTableChangeStatus();
    }
  }
    /** Fetch tables from API with offline fallback */
  async fetchTablesData(): Promise<void> {
    // Show skeleton briefly while we grab cache
    this.loading = false;
    // Always show cached data first for instant render
    await this.loadFromIndexedDB();

    // Then, if online, fetch fresh data in background and update UI
    if (this.isOnline) {
      this.fetchFromAPI();
    }
  }
  /** Fetch tables from API */
  // private fetchFromAPI(): void {
  //   this.tablesRequestService.getTables().pipe(
  //     finalize(() => {
  //       this.loading = true;
  //     })
  //   ).subscribe({
  //     next: (response) => {
  //       if (response.status) {
  //         this.tables = response.data.map((table: any) => ({
  //           ...table,
  //           status: Number(table.status),
  //         }));

  //         this.updateTableData();
  //         this.saveTablesToIndexedDB();
  //       }
  //     },
  //     error: (err) => {
  //       console.error('Error fetching tables from API:', err);
  //       this.offlineMode = true;
  //       this.loadFromIndexedDB();
  //     },
  //   });
  // }
  private fetchFromAPI(): void {
  this.tablesRequestService.getTables().pipe(
    finalize(() => {
      this.loading = true; // stop loading
    })
  ).subscribe({
    next: (response) => {
      if (response.status) {
        this.tables = response.data.map((table: any) => ({
          ...table,
          status: Number(table.status),
        }));

        this.tabless = [...this.tables]; // ✅ keep tabless updated

        this.updateTableData();
        this.saveTablesToIndexedDB();
        this.cdr.markForCheck();
      }
    },
    error: (err) => {
      console.error('Error fetching tables from API:', err);
      this.offlineMode = true;
      this.loadFromIndexedDB();
    },
  });
}
  /** Load tables from IndexedDB */
  // private async loadFromIndexedDB(): Promise<void> {
  //   try {
  //     const offlineTables = await this.dbService.getAll('tables');

  //     if (offlineTables && offlineTables.length) {
  //       this.tables = offlineTables;
  //       this.updateTableData();
  //       console.log('Tables loaded from IndexedDB');
  //     } else {
  //       console.warn('No tables found in IndexedDB');
  //       this.tables = [];
  //       this.updateTableData();
  //     }
  //   } catch (error) {
  //     console.error('Error loading tables from IndexedDB:', error);
  //     this.tables = [];
  //     this.updateTableData();
  //   } finally {
  //     this.loading = true;
  //   }
  // }
  private async loadFromIndexedDB(): Promise<void> {
  try {
    const offlineTables = await this.dbService.getAll('tables');

    if (offlineTables && offlineTables.length) {
      this.tables = offlineTables;
      this.tabless = [...this.tables]; // ✅
      this.updateTableData();
      console.log('Tables loaded from IndexedDB');
    } else {
      console.warn('No tables found in IndexedDB');
      this.tables = [];
      this.tabless = [];
      this.updateTableData();
    }
  } catch (error) {
    console.error('Error loading tables from IndexedDB:', error);
    this.tables = [];
    this.tabless = [];
    this.updateTableData();
  } finally {
    this.loading = true; // ✅ stop loading here too
    this.cdr.markForCheck();
  }
}

  /** Save tables to IndexedDB */
  private async saveTablesToIndexedDB(): Promise<void> {
    try {
      console.log('Tables to save:', this.tables);

       this.dbService.saveData('tables', this.tables);
      console.log('Tables saved to IndexedDB');
    } catch (error) {
      console.error('Error saving tables to IndexedDB:', error);
    }
  }
  /** Update table data and UI */
  private updateTableData(): void {
    this.updateTableStatusLists();
    this.saveTablesToIndexedDB().catch(error => {
      console.error('Error saving tables to IndexedDB:', error);
    });
  }
  selectedTable: any = null;
  loadSelectedTable(): void {
  this.dbService.getSelectedTable()
    .then(tables => {
      if (tables && tables.length) {
        this.selectedTable = tables[0];
        console.log('Selected table from IndexedDB:', this.selectedTable);
      } else {
        this.selectedTable = null;
      }
    })
    .catch(error => {
      console.error('Error loading selected table from IndexedDB:', error);
      this.selectedTable = null;
    });
}
  //end dalia
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
  // fetchTablesData(): void {
  //   this.loading = false;
  //   this.tablesRequestService
  //     .getTables()
  //     .pipe(
  //       finalize(() => {
  //         this.loading = true;
  //       })
  //     )
  //     .subscribe({
  //       next: (response) => {
  //         console.log(response.data, 'tables');
  //         this.tabless = response.data;
  //         if (response.status) {
  //           this.tables = response.data.map((table: any) => ({
  //             ...table,
  //             status: Number(table.status),
  //           }));

  //           this.tablesByStatus = [
  //             {
  //               status: 1,
  //               label: 'متاحة',
  //               tables: this.tables.filter((t) => t.status === 1),
  //             },
  //             {
  //               status: 2,
  //               label: 'مشغولة',
  //               tables: this.tables.filter((t) => t.status === 2),
  //             },
  //           ];

  //           // Initialize filtered list with all tables
  //           this.filteredTablesByStatus = JSON.parse(
  //             JSON.stringify(this.tablesByStatus)
  //           );
  //         }
  //       },
  //       error: (err) => {
  //         console.error('Error fetching tables:', err);
  //       },
  //     });
  // }

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
    // Reset visible chunk to ensure fast re-render after updates
    this.visibleCount = Math.max(20, Math.min(40, this.tabless.length));
  }
  listenOnTableChangeStatus() {
    this.tableOperation.listenToTable();

  this.tableOperation.tableChanged$.subscribe((changedTable) => {
  const table = changedTable.table;
  const index = this.tables.findIndex((t) => t.id === table.id);
  console.log('changed table',changedTable);
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
    this.tabless=[...this.tables]
  } else {
    // Add
    this.tables = [...this.tables, table];
    this.tabless=[...this.tables]
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
  //start dalia

  // onTableClick(tableId: number): void {
  //   const selectedTable = this.tables.find((table) => table.id === tableId);

  //   if (!selectedTable) {
  //     console.warn('Table not found:', tableId);
  //     return;
  //   }

  //   if (selectedTable.status === 2) {
  //     alert('هذه الطاولة مشغولة، يرجى اختيار طاولة أخرى.');
  //     return;
  //   }

  //   localStorage.setItem('selected_table', JSON.stringify(selectedTable));
  //   localStorage.setItem('table_id', JSON.stringify(tableId));
  //   localStorage.setItem(
  //     'table_number',
  //     JSON.stringify(selectedTable.table_number)
  //   );
  //   this.router.navigate(['/home']);
  //   if (localStorage.getItem('cameFromSideDetails') === 'true') {
  //     this.router.navigate(['/home']);
  //     localStorage.removeItem('cameFromSideDetails');
  //   }
  // }
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

  // Save locally
  localStorage.setItem('selected_table', JSON.stringify(selectedTable));
  localStorage.setItem('table_id', JSON.stringify(tableId));
  localStorage.setItem('table_number', JSON.stringify(selectedTable.table_number));

  // Save selected table to IndexedDB for offline access
    this.dbService.saveOrUpdateSelectedTable( selectedTable)
      .catch(error => console.error('Error saving selected table:', error));

  if (this.isOnline) {
    // ✅ Online → normal navigation
    this.router.navigate(['/home']);
    if (localStorage.getItem('cameFromSideDetails') === 'true') {
      this.router.navigate(['/home']);
      localStorage.removeItem('cameFromSideDetails');
    }
  } else {
     this.router.navigate(['/home']);
    // 🚫 Offline → update IndexedDB status
    // this.updateTableStatusOffline(selectedTable, 2); // mark as busy
  }
}
// dalia
// comment because  i am not need now this function update status of table at run time
/** Update table status in IndexedDB when offline */
private async updateTableStatusOffline(table: any, newStatus: number): Promise<void> {
  try {
    // Update in memory
    table.status = newStatus;
    const index = this.tables.findIndex(t => t.id === table.id);
    if (index !== -1) {
      this.tables[index] = { ...table };
    }
    // Save to IndexedDB
    // await this.dbService.saveData('tables', this.tables);
    // ✅ Update only this table in IndexedDB (مش الكل)
    // await this.dbService.updateTableStatus(table.id, newStatus);
    // Recompute lists & UI
    this.updateTableStatusLists();
    this.cdr.markForCheck();

    console.log(`Table ${table.id} updated to status ${newStatus} in IndexedDB`);

  } catch (error) {
    console.error('Failed to update table status offline:', error);
  }
}


  //end dalia
  ngOnDestroy(): void {
    this.tableOperation.stopListeningForChangeTableStatus();
    this.tableOperation.stopListeningForNewTable();
  }

get activeTables() {
  return this.selectedStatus === -1
    ? this.tabless
    : this.filteredTablesByStatus[this.selectedStatus]?.tables || [];
}



trackByTableId(index: number, table: any) {
  return table.id;
}

  private scheduleIncrementalReveal(): void {
    const step = 16;
    const bump = () => {
      const total = this.activeTables.length;
      if (this.visibleCount < total) {
        this.visibleCount = Math.min(this.visibleCount + step, total);
        this.cdr.markForCheck();
      }
    };

    const schedule = (cb: () => void) => {
      if (typeof (window as any).requestIdleCallback === 'function') {
        (window as any).requestIdleCallback(cb, { timeout: 500 });
      } else {
        setTimeout(cb, 120);
      }
    };

    // A few staged bumps after initial render
    schedule(bump);
    schedule(bump);
    schedule(bump);
  }

}
