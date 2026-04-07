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

  /**
   * Pull data from Cloud into the local offline database.
   * Single API call — the backend handles:
   *  1. Reading sync_checkpoints.last_pulled_at
   *  2. Calling SYNC_CLOUD_URL/api/sync/pull-data
   *  3. Upserting/deleting in the local DB
   *  4. Updating sync_checkpoints.last_pulled_at
   */
  pullOnlineData(): void {
    const branchId = this.authService.getBranchId();
    if (!branchId) {
      console.warn('⚠️ Cannot pull online data: branch_id not available yet.');
      return;
    }

    const token = this.authService.getToken();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    });

    console.log('🔄 Starting pull from Cloud for branch', branchId);

    // this.http.post(`${baseUrl}api/syncoffline/pull`, { branch_id: branchId }, { headers }).subscribe({
    //   next: (res: any) => {
    //     console.log('✅ Offline database successfully synced from Cloud!', res?.summary);
    //   },
    //   error: (err) => {
    //     console.error('❌ Failed to pull data from Cloud', err);
    //   }
    // });
  }
}

