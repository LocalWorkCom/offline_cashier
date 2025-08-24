
import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { PusherService } from './pusher.service';

@Injectable({
  providedIn: 'root'
})
export class DishStatusService {
  dishChanged$ = new Subject<any>();
  private listenedDishOrderChannels = new Set<string>(); // Track by channel string
channelName=`dish-order-statuses-changed`
  constructor(private pusherService: PusherService) {}
  
    listenToDishStatusInOrder(){ 
    this.pusherService.subscribe(this.channelName, 'Dish-status', (res: any) => {
      this.dishChanged$.next(res); // Emit the new dish to subscribers
    });

  }
    stopListening() {
    if (this.channelName) {
      this.pusherService.unsubscribe(this.channelName);
      this.dishChanged$.complete();
    }
  }
}

