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

  getPullCheckpoint(): Observable<any> {
    const token = this.authService.getToken();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    });
    return this.http.get(`${baseUrl}api/syncoffline/pull-checkpoint`, { headers });
  }

  fetchCloudData(branchId: number, since: string | null): Observable<any> {
    const token = this.authService.getToken();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    });
    
    let url = `${baseUrl}api/sync/pull-data?branch_id=${branchId}`;
    if (since) {
      url += `&since=${encodeURIComponent(since)}`;
    }
    return this.http.get(url, { headers });
  }

  processPullDataLocally(data: any): Observable<any> {
    const token = this.authService.getToken();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    });
    return this.http.post(`${baseUrl}api/syncoffline/process-pull`, data, { headers });
  }

  pullOnlineData(): void {
    const branchId = this.authService.getBranchId();
    if (!branchId) return;

    // 1. Get checkpoint
    this.getPullCheckpoint().subscribe({
      next: (res) => {
        const lastPulledAt = res.last_pulled_at || null;
        
        // 2. Fetch from Cloud
        this.fetchCloudData(branchId, lastPulledAt).subscribe({
          next: (cloudRes) => {
            if (cloudRes.ok && cloudRes.data) {
                // Add branch_id to payload for processing
                const payload = {
                    ...cloudRes,
                    branch_id: branchId
                };
                
              // 3. Process into local database
              this.processPullDataLocally(payload).subscribe({
                next: () => console.log('✅ Offline database successfully synced from Cloud!'),
                error: (err) => console.error('❌ Failed to process pulled data locally', err)
              });
            }
          },
          error: (err) => console.error('❌ Failed to pull data from Cloud', err)
        });
      },
      error: (err) => console.error('❌ Failed to get pull checkpoint', err)
    });
  }
}
