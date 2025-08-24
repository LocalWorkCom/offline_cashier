
import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { PusherService } from './pusher.service';

@Injectable({
  providedIn: 'root'
})
export class DishChangeService {
  private listenedDishIds = new Set<number>();
  private dishChangedSubject = new Subject<any>();
  dishChanged$ = this.dishChangedSubject.asObservable();

  constructor(private pusherService: PusherService) {}

  dishChange(dishes: any[]) {
    dishes.forEach((dish: any) => {
      const dishId = dish.id;
      if (!this.listenedDishIds.has(dishId)) {
        this.listenToNewDish(dishId);
        this.listenedDishIds.add(dishId);
        console.log('Listening to dish:', dishId);
      } else {
        console.log('Already listening to dish:', dishId);
      }
    });
  }

  private listenToNewDish(dishId: number) { 
    const branchId = localStorage.getItem('branch_id');

    const channel = `dish-${dishId}-branch-${branchId}`;
    this.pusherService.subscribe(channel, 'dish-change', (res: any) => {
      const updatedDish = res.data; // adjust if needed
      console.log('Received dish change event:', updatedDish);

      this.dishChangedSubject.next(updatedDish);
    });
  }
  
}
