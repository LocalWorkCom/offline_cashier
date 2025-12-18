
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

  private isElectron(): boolean {
    return !!(window && (window as any).deviceAPI);
  }

  listenToNewOrder(a:string='string') {
    // Use longer delay for Electron to ensure localStorage and Pusher are ready
    const delay = this.isElectron() ? 500 : 100;

    setTimeout(() => {
      const branchId = localStorage.getItem('branch_id');
      const empId = localStorage.getItem('employee_id');

      if (!branchId || !empId) {
        console.error('Missing branch_id or employee_id in localStorage');
        console.log('branch_id:', branchId, 'employee_id:', empId);
        // Retry after a short delay, especially for Electron
        const retryDelay = this.isElectron() ? 1000 : 500;
        setTimeout(() => this.listenToNewOrder(a), retryDelay);
        return;
      }

      console.log('Received new order event:');
      console.log('Branch ID:', branchId, 'Employee ID:', empId);
      console.log('Running in Electron:', this.isElectron());

      this.channelName = `newOrder2-${empId}-branch-${branchId}`;
      console.log('Subscribing to channel:', this.channelName);

      try {
        this.pusherService.subscribe(this.channelName, 'new-order-added2', (res: any) => {
          console.log('Received new order event:', res.data);
          console.log('test where event listen', a);
          // this.orderAdded$.next(res.data);
          this.http.get(`${baseUrl}api/orders/orderDetails/${3672}`).subscribe({
            next: (response) => console.log('Order updated successfully:', response),
            error: (err) => console.error('Failed to update order:', err)
          });
        });
        console.log('Successfully subscribed to channel:', this.channelName);
      } catch (error) {
        console.error('Error subscribing to Pusher channel:', error);
        // Retry subscription after a delay, longer for Electron
        const retryDelay = this.isElectron() ? 2000 : 1000;
        setTimeout(() => this.listenToNewOrder(a), retryDelay);
      }
    }, delay);
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

