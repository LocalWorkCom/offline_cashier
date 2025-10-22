
// import { Injectable } from '@angular/core';
// import { Subject } from 'rxjs';
// import { PusherService } from './pusher.service';

// @Injectable({
//   providedIn: 'root'
// })
// export class DishStatusService {
//   dishChanged$ = new Subject<any>();
//   private listenedDishOrderChannels = new Set<string>(); // Track by channel string
// channelName=`dish-order-statuses-changed`
//   constructor(private pusherService: PusherService) {}
  
//     listenToDishStatusInOrder(){ 
//     this.pusherService.subscribe(this.channelName, 'Dish-status', (res: any) => {
//       this.dishChanged$.next(res); // Emit the new dish to subscribers
//     });

//   }
//     stopListening() {
//     if (this.channelName) {
//       this.pusherService.unsubscribe(this.channelName);
//       this.dishChanged$.complete();
//       // create a new Subject so next time we can re-subscribe
//       this.dishChanged$ = new Subject<any>();
//     }
//   }
// }
import { Injectable, OnDestroy } from '@angular/core';
import { Subject, Observable } from 'rxjs';
import { PusherService } from './pusher.service';

@Injectable({
  providedIn: 'root'
})
export class DishStatusService implements OnDestroy {
  private dishChangedSubject = new Subject<any>();
  dishChanged$: Observable<any> = this.dishChangedSubject.asObservable();
   
  channelName = 'dish-order-statuses-changed';
  private readonly EVENT_NAME = 'Dish-status';
  private isListening = false;

  constructor(private pusherService: PusherService) {}
  
  listenToDishStatusInOrder(): void {
    if (this.isListening) {
      console.warn('Already listening to dish status updates');
      return;
    }

    this.pusherService.subscribe(
      this.channelName, 
      this.EVENT_NAME, 
      (res: any) => {
        this.dishChangedSubject.next(res);
      }
    );
    
    this.isListening = true;
  }

  stopListening(): void {
    if (!this.isListening) {
      return;
    }

    this.pusherService.unsubscribe(this.channelName);
    this.isListening = false;
  }

  ngOnDestroy(): void {
    this.stopListening();
    this.dishChangedSubject.complete();
  }
}
