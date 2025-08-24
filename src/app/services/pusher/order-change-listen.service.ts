
import { Injectable } from '@angular/core';
import { PusherService } from './pusher.service';
import { Subject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class OrderChangeListenService  {
  OrderStatusUpdated$ = new Subject<any>();
  dishStatusUpdated$ = new Subject<any>();

  constructor(private pusherService: PusherService) {
  }
OrderStatusUpdatedChannel=`order-status-update`;
  listenToOrder() {
    this.pusherService.subscribe( 'order-channel',this.OrderStatusUpdatedChannel, (res: any) => {
      this.OrderStatusUpdated$.next(res); // Emit the new dish to subscribers
      console.log('test order' , res);

    });
  }
  // listenToDishStatusInOrder(){
  //   const channel = `dish-order-statuses-changed`;
  //   this.pusherService.subscribe(channel, 'Dish-status', (res: any) => {
  //     this.dishStatusUpdated$.next(res); // Emit the new dish to subscriberssubscribers
  //     console.log('test dish' , res);
  //   });

  // }
    stopListeningOfOrderStatus() {
    if (this.OrderStatusUpdatedChannel) {
      this.pusherService.unsubscribe(this.OrderStatusUpdatedChannel);
      this.OrderStatusUpdated$.complete();
    }
  }
}
