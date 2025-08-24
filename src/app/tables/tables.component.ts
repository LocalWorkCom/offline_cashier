import { Component, OnDestroy, OnInit } from '@angular/core';
import { TablesService } from '../services/tables.service';
import { CommonModule,Location } from '@angular/common';
import { Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { TableCrudOperationService } from '../services/pusher/tableCrudOperation';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { finalize } from 'rxjs';

@Component({
  selector: 'app-tables',
  standalone: true,
  imports: [CommonModule, FormsModule,ShowLoaderUntilPageLoadedDirective],
  templateUrl: './tables.component.html',
  styleUrls: ['./tables.component.css'],
})
export class TablesComponent implements OnInit,OnDestroy {
  tables: any[] = [];
  tablesByStatus: { status: number; label: string; tables: any[] }[] = [];
  filteredTablesByStatus: { status: number; label: string; tables: any[] }[] = [];
  selectedStatus: number = 0;
  clickedTableId: number | null = null;
  searchText: string = '';
loading:boolean=true;
  constructor(private tablesRequestService: TablesService, private router: Router,private location: Location,private tableOperation: TableCrudOperationService,) {}


  ngOnInit(): void {
    this.fetchTablesData();
    this.loadClickedTable();
    this.listenToNewTable();
this.listenOnTableChangeStatus()
  }
    listenToNewTable() {
  this.tableOperation.newTable();

  this.tableOperation.newTable$.subscribe((newTable) => {
    console.log(newTable,'data');

          const table = newTable.table
            this.tables = [...this.tables, table];
            this.updateTableStatusLists()

  });
}
  /** Fetch tables from API */
  fetchTablesData(): void {
    this.loading=false;
    this.tablesRequestService.getTables().pipe(
    finalize(() => {
      this.loading=true;
    })
  ).subscribe({
      next: (response) => {
        console.log(response.data , 'tables')
        if (response.status) {
          this.tables = response.data.map((table: any) => ({
            ...table,
            status: Number(table.status),
          }));




          this.tablesByStatus = [
            { status: 1, label: 'متاحة', tables: this.tables.filter(t => t.status === 1) },
            { status: 2, label: 'مشغولة', tables: this.tables.filter(t => t.status === 2) }
          ];

          // Initialize filtered list with all tables
          this.filteredTablesByStatus = JSON.parse(JSON.stringify(this.tablesByStatus));
        }
      },
      error: (err) => {
        console.error('Error fetching tables:', err);
      },
    });
  }

updateTableStatusLists(){
        this.tablesByStatus = [
            { status: 1, label: 'متاحة', tables: this.tables.filter(t => t.status == 1) },
            { status: 2, label: 'مشغولة', tables: this.tables.filter(t => t.status == 2) }
          ];
       // Trigger UI update
  this.filteredTablesByStatus = [...this.tablesByStatus.map(group => ({
    ...group,
    tables: [...group.tables]
  }))];
}
listenOnTableChangeStatus(){
    this.tableOperation.listenToTable();

        this.tableOperation.tableChanged$.subscribe((changedTable) => {
          console.log('Table changed:', changedTable);
          const table = changedTable.table;
          const index = this.tables.findIndex(t => t.id === table.id);

          if (index !== -1) {//edit or delete
            //check status
            if (changedTable.status == 'updated') {
              this.tables = [
                ...this.tables.slice(0, index),
                table,
                ...this.tables.slice(index + 1)
              ];
              console.log('f',this.tables);

            } else if (changedTable.status == 'delete') {
              this.tables = [
                ...this.tables.slice(0, index),
                ...this.tables.slice(index + 1)
              ];
            }

          } else {//add
            this.tables = [...this.tables, table];
          this.updateTableStatusLists();
          }

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

      this.filteredTablesByStatus = JSON.parse(JSON.stringify(this.tablesByStatus));
      this.selectedStatus = 0; // Reset tab selection
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
   this.router.navigate(['/home']);
    if (localStorage.getItem('cameFromSideDetails') === 'true') {
      this.router.navigate(['/home']);
      localStorage.removeItem('cameFromSideDetails');
    }
  }
   ngOnDestroy(): void {
    this.tableOperation.stopListeningForChangeTableStatus();
    this.tableOperation.stopListeningForNewTable()
  }
}
