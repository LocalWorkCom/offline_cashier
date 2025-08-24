import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { PusherService } from './pusher.service';

@Injectable({
  providedIn: 'root'
})
export class totalBalance {
  // Observable that emits newly added dishes
  totalChange$ = new Subject<any>();

  constructor(private pusherService: PusherService) {}

     empId = localStorage.getItem('employee_id');;
     channel = `total-paid-${this.empId}`;
  listenToBalance() { 
    this.pusherService.subscribe(this.channel, 'Cashier-total-paid', (res: any) => {
      this.totalChange$.next(res); // Emit the new dish to subscribers
    });
  }
   stopListeningForBalance() {
    if (this.channel) {
      this.pusherService.unsubscribe(this.channel);
      this.totalChange$.complete();
    }
  }
}
