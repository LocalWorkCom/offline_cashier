import { Component, OnDestroy, OnInit } from '@angular/core';
import { TablesService } from '../services/tables.service';
import { CommonModule, Location } from '@angular/common';
import { Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { TableCrudOperationService } from '../services/pusher/tableCrudOperation';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { finalize } from 'rxjs/operators';
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
})
export class TablesComponent implements OnInit, OnDestroy {
  tables: any[] = [];
  tablesByStatus: { status: number; label: string; tables: any[] }[] = [];
  filteredTablesByStatus: { status: number; label: string; tables: any[] }[] = [];
  selectedStatus: number = 0;
  clickedTableId: number | null = null;
  searchText: string = '';
  loading: boolean = true;
  isOnline: boolean = navigator.onLine;
  offlineMode: boolean = false;
  private onlineStatusSubscription!: Subscription;
  private tableSubscriptions: Subscription[] = [];

  constructor(
    private tablesRequestService: TablesService,
    private dbService: IndexeddbService,
    private cdr: ChangeDetectorRef,
    private router: Router,
    private location: Location,
    private tableOperation: TableCrudOperationService,
  ) {}

  ngOnInit(): void {
    this.setupOnlineStatusMonitoring();
    this.initDatabase().then(() => {
      this.fetchTablesData();
      this.loadSelectedTable();
    });
    this.loadClickedTable();
    this.setupTableListeners();
  }

  // Initialize database and check online status
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

  // Setup table listeners based on online status
  private setupTableListeners(): void {
    if (this.isOnline) {
      this.listenToNewTable();
      this.listenOnTableChangeStatus();
    }
  }

  // Listen for new tables (online only)
  listenToNewTable(): void {
    this.tableOperation.newTable();

    const newTableSubscription = this.tableOperation.newTable$.subscribe({
      next: (newTable) => {
        console.log('New table received:', newTable);
        const table = newTable.table;
        this.tables = [...this.tables, table];
        this.updateTableData();
      },
      error: (error) => {
        console.error('Error in new table subscription:', error);
      }
    });

    this.tableSubscriptions.push(newTableSubscription);
  }

  // Listen for table status changes (online only)
  listenOnTableChangeStatus(): void {
    this.tableOperation.listenToTable();

    const tableChangeSubscription = this.tableOperation.tableChanged$.subscribe({
      next: (changedTable) => {
        console.log('Table changed:', changedTable);
        const table = changedTable.table;
        const index = this.tables.findIndex(t => t.id === table.id);

        if (index !== -1) {
          if (changedTable.status === 'updated') {
            this.tables = [
              ...this.tables.slice(0, index),
              table,
              ...this.tables.slice(index + 1)
            ];
          } else if (changedTable.status === 'delete') {
            this.tables = [
              ...this.tables.slice(0, index),
              ...this.tables.slice(index + 1)
            ];
          }
        } else {
          this.tables = [...this.tables, table];
        }
        
        this.updateTableData();
      },
      error: (error) => {
        console.error('Error in table change subscription:', error);
      }
    });

    this.tableSubscriptions.push(tableChangeSubscription);
  }

  /** Fetch tables from API with offline fallback */
  fetchTablesData(): void {
    this.loading = false;
    
    if (this.isOnline) {
      this.fetchFromAPI();
    } else {
      this.loadFromIndexedDB();
    }
  }

  /** Fetch tables from API */
  private fetchFromAPI(): void {
    this.tablesRequestService.getTables().pipe(
      finalize(() => {
        this.loading = true;
      })
    ).subscribe({
      next: (response) => {
        if (response.status) {
          this.tables = response.data.map((table: any) => ({
            ...table,
            status: Number(table.status),
          }));
          
          this.updateTableData();
          this.saveTablesToIndexedDB();
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
  private async loadFromIndexedDB(): Promise<void> {
    try {
      const offlineTables = await this.dbService.getAll('tables');
      
      if (offlineTables && offlineTables.length) {
        this.tables = offlineTables;
        this.updateTableData();
        console.log('Tables loaded from IndexedDB');
      } else {
        console.warn('No tables found in IndexedDB');
        this.tables = [];
        this.updateTableData();
      }
    } catch (error) {
      console.error('Error loading tables from IndexedDB:', error);
      this.tables = [];
      this.updateTableData();
    } finally {
      this.loading = true;
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

  /** Sync data when coming back online */
  private syncData(): void {
    // Re-fetch data from API
    this.fetchFromAPI();
    
    // Re-setup listeners
    this.setupTableListeners();
  }

  updateTableStatusLists(): void {
    this.tablesByStatus = [
      { status: 1, label: 'متاحة', tables: this.tables.filter(t => t.status === 1) },
      { status: 2, label: 'مشغولة', tables: this.tables.filter(t => t.status === 2) }
    ];
    
    // Trigger UI update
    this.filteredTablesByStatus = [...this.tablesByStatus.map(group => ({
      ...group,
      tables: [...group.tables]
    }))];
    
    this.cdr.detectChanges();
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
      this.filteredTablesByStatus = JSON.parse(JSON.stringify(this.tablesByStatus));
      this.selectedStatus = 0;
      return;
    }

    const filtered = this.tablesByStatus
      .map(statusGroup => ({
        ...statusGroup,
        tables: statusGroup.tables.filter(table =>
          table.table_number.toString().includes(searchValue)
        ),
      }))
      .filter(statusGroup => statusGroup.tables.length > 0);

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
    const selectedTable = this.tables.find(table => table.id === tableId);

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
    localStorage.setItem('table_number', JSON.stringify(selectedTable.table_number));
    
    // Save selected table to IndexedDB for offline access
    this.dbService.saveData('selectedTable', [selectedTable])
      .catch(error => console.error('Error saving selected table:', error));
    
    this.router.navigate(['/home']);
    
    if (localStorage.getItem('cameFromSideDetails') === 'true') {
      localStorage.removeItem('cameFromSideDetails');
    }
  }

  selectedTable: any = null; 
  loadSelectedTable(): void {
  this.dbService.getAll('selectedTable')
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

  retryConnection(): void {
    this.offlineMode = false;
    this.fetchTablesData();
  }

  ngOnDestroy(): void {
    // Clean up all subscriptions
    if (this.onlineStatusSubscription) {
      this.onlineStatusSubscription.unsubscribe();
    }
    
    this.tableSubscriptions.forEach(sub => sub.unsubscribe());
    
    this.tableOperation.stopListeningForChangeTableStatus();
    this.tableOperation.stopListeningForNewTable();
  }
}