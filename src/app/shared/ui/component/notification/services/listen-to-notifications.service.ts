import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { notification } from '../interface/notification';
import { BasicsConstance } from '../../../../../constants';
import { PusherService } from '../../../../../services/pusher/pusher.service';

@Injectable({
  providedIn: 'root',
})
export class ListenToNotificationsService {
  newNotificaion$ = new Subject<any>();
  lang = localStorage.getItem(BasicsConstance.LANG);
  employeeID = localStorage.getItem(BasicsConstance.employeeID)!; 
  private channelName: string = `notification-${this.employeeID}-${BasicsConstance.FLAG}`;
  event: string = 'Notifications';
  constructor(private pusherService: PusherService) {
    this.listenToNewNotification();
  }

  listenToNewNotification() {
    this.pusherService.subscribe(this.channelName, this.event, (res: any) => {
      console.log('Received new notificaion event:', res);
      const transformedNotification = this.transformNotificationByLang(res);
      this.newNotificaion$.next(transformedNotification);
    });

  }

  stopListening() {
    if (this.channelName) {
      this.pusherService.unsubscribe(this.channelName);
      this.newNotificaion$.complete();
    }
  }
  transformNotificationByLang(res: any): notification {
    const notification = {
      ...res,
      description: this.lang === 'ar' ? res.description_ar : res.description_en,
      title: this.lang === 'ar' ? res.title_ar : res.title_en, 
      date:res.date||'new'
    };
    return notification;
  }
}
