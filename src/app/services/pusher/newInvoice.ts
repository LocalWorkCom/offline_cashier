
import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { PusherService } from './pusher.service';

@Injectable({
  providedIn: 'root'
})
export class NewInvoiceService {
  invoiceAdded$ = new Subject<any>();
  private channelName!: string;

  constructor(private pusherService: PusherService) {}
// Cashier-requests
// [3:34 pm, 22/6/2025] shrook: event  'Cashier-requests'
// [3:34 pm, 22/6/2025] shrook: 'request-bill-' . $this->cashier channel name
  listenToNewInvoice() { 
    const empId =  localStorage.getItem('employee_id');;

    this.channelName = `request-bill-${empId}`;

    this.pusherService.subscribe(this.channelName, 'Cashier-requests', (res: any) => {
      console.log('Received new invoice event:', res);
      this.invoiceAdded$.next(res);
    });
  }

  stopListening() {
    if (this.channelName) {
      this.pusherService.unsubscribe(this.channelName);
      this.invoiceAdded$.complete();
    }
  }
}

