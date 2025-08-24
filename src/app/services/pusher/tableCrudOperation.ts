
import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { PusherService } from './pusher.service';

@Injectable({
  providedIn: 'root'
})
export class TableCrudOperationService {

    private branchId = localStorage.getItem('branch_id');
  private listenedTableIds = new Set<number>();

  // Emits every changed table
  tableChanged$ = new Subject<any>();
  newTable$ = new Subject<any>();

  constructor(private pusherService: PusherService) {}
 tableStatusChannel=`table-${this.branchId}`
 newTableChannel=`table-added-branch-${this.branchId}`
  listenToTable() {

    this.pusherService.subscribe(this.tableStatusChannel, 'tables', (res: any) => {

      this.tableChanged$.next(res);
    });
  }

  newTable(){
  // const channel=`table-added-branch-${this.branchId}`;
this.pusherService.subscribe(this.newTableChannel,'tables-added', (res: any) => {

      this.newTable$.next(res);
  console.log(' Received new table event:', res);

});
}
  stopListeningForNewTable() {
    if (this.newTableChannel) {
      this.pusherService.unsubscribe(this.newTableChannel);
      this.newTable$.complete();
    }
  }
  stopListeningForChangeTableStatus() {
    if (this.tableStatusChannel) {
      this.pusherService.unsubscribe(this.tableStatusChannel);
      this.newTable$.complete();
    }
  }
}
