import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { PusherService } from './pusher.service';

@Injectable({
  providedIn: 'root'
})
export class NewDishService {
  // Observable that emits newly added dishes
  dishAdded$ = new Subject<any>();

     branchId =  localStorage.getItem('branch_id');;
     channelName = `dish-${this.branchId}`;
  constructor(private pusherService: PusherService) {}

  listenToNewdish() { 
    this.pusherService.subscribe(this.channelName, 'dish-added', (res: any) => {
      this.dishAdded$.next(res); // Emit the new dish to subscribers
    });
  }
    stopListening() {
    if (this.channelName) {
      this.pusherService.unsubscribe(this.channelName);
      this.dishAdded$.complete();
    }
  }
}
