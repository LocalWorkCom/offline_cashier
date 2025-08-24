import {
  AfterViewInit,
  Component,
  inject,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import { Popover } from 'primeng/popover';
import { InputGroupAddonModule } from 'primeng/inputgroupaddon';
import { ButtonModule } from 'primeng/button';
import { InputTextModule } from 'primeng/inputtext';
import { CommonModule } from '@angular/common';
import { notifications } from './interface/notification';
import { NotificationService } from './services/notification.service';
import { Router } from '@angular/router';
import { notificationType } from './notificationTypeEnum';
import { ListenToNotificationsService } from './services/listen-to-notifications.service';
import { BasicsConstance } from '../../../../constants';
import { Subject, takeUntil } from 'rxjs';
import { TranslateModule } from '@ngx-translate/core';
@Component({
  selector: 'app-notification',
  imports: [
    Popover,
    InputGroupAddonModule,
    ButtonModule,
    InputTextModule,
    CommonModule,
    TranslateModule,
  ],
  templateUrl: './notification.component.html',
  styleUrl: './notification.component.css',
  standalone: true,
})
export class NotificationComponent implements OnInit, OnDestroy, AfterViewInit {
  @ViewChild('op') op!: Popover;
  ngAfterViewInit(): void {
    document.addEventListener('click', (event) => {
      if (document.querySelector('.p-popover')) {
        this.op.hide();
      }
    });
  }
  allNotificaions!: notifications[];
  unreadMessages: number = 0;
  dir = localStorage.getItem('direction') || BasicsConstance.DefaultDir;
  private notificationService = inject(NotificationService);
  private newNotificationService = inject(ListenToNotificationsService);

  private destroy$ = new Subject<void>();
  private router = inject(Router);
  ngOnInit(): void {
    this.getAllUserNotifications();
    this.listenToNewNotification();
  }

  getAllUserNotifications() {
    this.notificationService
      .getNotifications()
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (res) => {
          if (res.data) {
            this.allNotificaions = res.data;
            this.unreadMessages = this.countUnreadMessages(
              this.allNotificaions
            );
          }
        },
      });
  }

  listenToNewNotification() {
    this.newNotificationService.newNotificaion$
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (notification) => {
          console.log('Notification from Pusher:', notification);
          let dateFound = false;

          this.unreadMessages++;

          const updatedNotifications = this.allNotificaions.map((item) => {
            if (item.date === notification.date) {
              dateFound = true;
              return {
                ...item,
                notifications: [notification, ...item.notifications], // Unshift notification
              };
            }
            return item;
          });
          if (!dateFound) {
            this.allNotificaions = [{
              date: notification.date,
              notifications: [notification],
            }, ...this.allNotificaions];
          } else {
            this.allNotificaions = updatedNotifications;
          }
        },
      });
  }

  onNotificationClick(
    orderId: number,
    notificationType: string,
    notificationId: number,
    op: any
  ) {
    op.hide();
    this.goToItemDetails(orderId, notificationType, notificationId);
  }
  goToItemDetails(orderId: number, type: string, notificationId: number) {
    this.readNotification(notificationId);
    // Logic for navigating based on notification type
    if (type ===notificationType.order) {
      this.router.navigate(['/order-details', orderId]);
    } else if (type === notificationType.table) {
      this.router.navigate(['/tables']);
    }else if (type === notificationType.invoice) {
      this.router.navigate(['/pill-details',orderId]);
    } else {
      console.warn(`Unknown notification type: ${type}`);
    }
  }
  readNotification(id: number) {
    this.notificationService.readNotifications(id).subscribe({
      next: (data) => {
        if (data.status) {
          this.unreadMessages--;
          this.allNotificaions = [
            ...this.removeReadNotificationFromDom(id, this.allNotificaions),
          ];
        } else {
          console.error(
            'ther is error occur during mark notification as unread ',
            data
          );
        }
      },
    });
  }
  countUnreadMessages(allNotifications: notifications[]): number {
    let totalCount = 0;
    allNotifications.forEach((notificationDay) => {
      totalCount += notificationDay.notifications.length;
    });

    return totalCount;
  }
  removeReadNotificationFromDom(
    id: number,
    allNotifications: notifications[]
  ): notifications[] {
    allNotifications.forEach((item) => {
      item.notifications = item.notifications.filter(
        (notification) => notification.id !== id
      );
    });

    return allNotifications;
  }

  ngOnDestroy(): void {
    this.newNotificationService.stopListening();
    this.destroy$.next();
    this.destroy$.complete();
  }
}
