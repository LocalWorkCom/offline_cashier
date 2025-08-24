
import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { PusherService } from './pusher.service';

@Injectable({
  providedIn: 'root'
})
export class NewOrderService {
  orderAdded$ = new Subject<any>();
  private channelName!: string;

  constructor(private pusherService: PusherService) {}

  listenToNewOrder(a:string='string') {
    const branchId = localStorage.getItem('branch_id');
    const empId =  localStorage.getItem('employee_id');;

    this.channelName = `newOrder-${empId}-branch-${branchId}`;

    this.pusherService.subscribe(this.channelName, 'new-order-added', (res: any) => {
      console.log('Received new order event:', res);
    console.log('test where event listen',a);

      this.orderAdded$.next(res);

    });
  }

  stopListening() {
    if (this.channelName) {
      this.pusherService.unsubscribe(this.channelName);
      this.orderAdded$.complete();
    }
  }
}

