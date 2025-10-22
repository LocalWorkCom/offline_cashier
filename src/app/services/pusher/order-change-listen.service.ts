
// import { Injectable } from '@angular/core';
// import { PusherService } from './pusher.service';
// import { Subject } from 'rxjs';

// @Injectable({
//   providedIn: 'root'
// })
// export class OrderChangeListenService  {
//   OrderStatusUpdated$ = new Subject<any>();
//   dishStatusUpdated$ = new Subject<any>();

//   constructor(private pusherService: PusherService) {
//   }
// OrderStatusUpdatedChannel=`order-status-update`;
//   listenToOrder() {
//     this.pusherService.subscribe( 'order-channel',this.OrderStatusUpdatedChannel, (res: any) => {
//       this.OrderStatusUpdated$.next(res); // Emit the new dish to subscribers
//       console.log('test order' , res);

//     });
//   }
 
//     stopListeningOfOrderStatus() {
//     if (this.OrderStatusUpdatedChannel) {
//       this.pusherService.unsubscribe(this.OrderStatusUpdatedChannel);
//       this.OrderStatusUpdated$.complete();
//     }
//   }
// }
import { Injectable, OnDestroy } from '@angular/core';
import { PusherService } from './pusher.service';
import { Subject, Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class OrderChangeListenService implements OnDestroy {
  private OrderStatusUpdatedSubject = new Subject<any>();
  private dishStatusUpdatedSubject = new Subject<any>();
  
  // Public observables (read-only)
  OrderStatusUpdated$: Observable<any> = this.OrderStatusUpdatedSubject.asObservable();
  dishStatusUpdated$: Observable<any> = this.dishStatusUpdatedSubject.asObservable();

  private readonly CHANNEL_NAME = 'order-channel';
  OrderStatusUpdatedChannel = 'order-status-update';
  private isListening = false;

  constructor(private pusherService: PusherService) {}

  listenToOrder(): void {
    if (this.isListening) {
      console.warn('Already listening to order updates');
      return;
    }

    this.pusherService.subscribe(
      this.CHANNEL_NAME,
      this.OrderStatusUpdatedChannel,
      (res: any) => {
        this.OrderStatusUpdatedSubject.next(res);
        console.log('test order', res);
      }
    );
    
    this.isListening = true;
  }

  stopListeningOfOrderStatus(): void {
    if (!this.isListening) {
      return;
    }

    // this.pusherService.unsubscribe(this.CHANNEL_NAME, this.OrderStatusUpdatedChannel);
    this.isListening = false;
  }

  ngOnDestroy(): void {
    this.stopListeningOfOrderStatus();
    this.OrderStatusUpdatedSubject.complete();
    this.dishStatusUpdatedSubject.complete();
  }
}