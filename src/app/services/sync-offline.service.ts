import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { baseUrl } from '../environment';
import { AuthService } from './auth.service';

@Injectable({
  providedIn: 'root'
})
export class SyncOfflineService {
  private syncUrl = `${baseUrl}api/syncoffline/push`;

  constructor(private http: HttpClient, private authService: AuthService) { }

  triggerSync(): Observable<any> {
    const token = this.authService.getToken();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    });

    return this.http.post(this.syncUrl, {}, { headers });
  }

  triggerActivityLogSync(): Observable<any> {
    const token = this.authService.getToken();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    });

    return this.http.post(`${baseUrl}api/syncoffline/activity-logs/push`, {}, { headers });
  }
}
