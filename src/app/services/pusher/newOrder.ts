
import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { PusherService } from './pusher.service';
import { baseUrl2, baseUrl } from '../../environment';
import { HttpClient, HttpHeaders } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class NewOrderService {
  orderAdded$ = new Subject<any>();
  private channelName!: string;

  constructor(private pusherService: PusherService , private http: HttpClient) {}

  listenToNewOrder(a:string='string') {
    const branchId = localStorage.getItem('branch_id');
    const empId =  localStorage.getItem('employee_id');;
    console.log('Received new order event:');

    this.channelName = `newOrder2-${empId}-branch-${branchId}`;

    this.pusherService.subscribe(this.channelName, 'new-order-added2', (res: any) => {
      console.log('Received new order event:', res.data);
      console.log('test where event listen',a);
      // this.orderAdded$.next(res.data);
      this.http.get(`${baseUrl}/orders/order-details/${1766}`).subscribe({
        next: (response) => console.log('Order updated successfully:', response),
        error: (err) => console.error('Failed to update order:', err)
      });

    });
  }
/*   listenToNewOrder(a:string='string') {
    const branchId = localStorage.getItem('branch_id');
    const empId =  localStorage.getItem('employee_id');;

    this.channelName = `newOrder-${empId}-branch-${branchId}`;

    this.pusherService.subscribe(this.channelName, 'new-order-added', (res: any) => {
      console.log('Received new order event:', res);
      console.log('test where event listen',a);

      this.orderAdded$.next(res);

    });
  } */

  stopListening() {
    if (this.channelName) {
      this.pusherService.unsubscribe(this.channelName);
      this.orderAdded$.complete();
    }
  }
}

