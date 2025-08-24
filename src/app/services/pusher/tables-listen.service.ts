import { Injectable } from '@angular/core';
import { PusherService } from './pusher.service';

@Injectable({
  providedIn: 'root'
})
export class TablesListenService {

  constructor(private pusherService:PusherService) {}

  tableChange(tables:any){
const listenedTableIds = new Set();

tables.filter((table: any) => {
  const tableId = table.id;

  if (!listenedTableIds.has(tableId)) {
    this.listenToTable(tableId);
    listenedTableIds.add(tableId);
    console.log('Listening to table:', tableId);
  } else {
    console.log('Already listening to table:', tableId);
  }

  return true; // If you're using `filter`, this keeps all orders. Adjust if needed.
});

}


listenToTable(tableId:number){
const channel=`table-status${tableId}`;
this.pusherService.subscribe(channel,'Waiter-requests', (res: any) => {
  const data=res.data

  console.log(' Received updated table event:', res);

});
}

}
