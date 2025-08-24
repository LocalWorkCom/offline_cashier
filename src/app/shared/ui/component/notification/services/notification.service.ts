import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http'; // Import HttpClient
import { Observable } from 'rxjs'; // Import Observab
import { baseUrl } from '../../../../../environment';

@Injectable({
  providedIn: 'root',
})
export class NotificationService {
  constructor(private http: HttpClient) {}
  getNotifications() {
    return new Observable<any>((observer) => {
      this.http.get<any>(`${baseUrl}api/notifications`).subscribe({
        next: (data) => {
          observer.next(data);
          observer.complete();
        },
        error: (err) => {
          observer.error(err);
        },
      });
    });
  }

    readNotifications(id:number) {
    return new Observable<any>((observer) => {
      this.http.get<any>(`${baseUrl}api/notification/${id}`).subscribe({
        next: (data) => {
          observer.next(data);
          observer.complete();
        },
        error: (err) => {
          observer.error(err);
        },
      });
    });
  }
}
