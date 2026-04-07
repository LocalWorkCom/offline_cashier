import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { firstValueFrom } from 'rxjs';
import { AuthService } from './auth.service';
import { baseUrl } from '../environment';
import { IndexeddbService } from './indexeddb.service';


@Injectable({
  providedIn: 'root',
})
export class PillsService {
  private apiUrl = `${baseUrl}api`;
  pills: any[] = [];

  constructor(private http: HttpClient, private authService: AuthService, private db: IndexeddbService) { }
  getPills(): Observable<any> {
    // Get the token from AuthService
    // const token = this.authService.getToken();
    const token = localStorage.getItem('authToken');

    if (!token) {
      throw new Error('No authentication token found');
    }

    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);

    // Make the HTTP request
    return this.http.get(`${this.apiUrl}/invoices`, { headers });
  }

  //start dalia
  fetchAndSave(): Observable<any> {
    return new Observable(observer => {
      this.getPills().subscribe({
        next: async (response: any) => {
          if (response.status && response.data.invoices) {
            try {
              this.pills = response.data.invoices
                .map((pill: any) => {
                  if (pill.invoice_type === 'credit_note') {
                    return {
                      ...pill,
                      invoice_print_status: 'returned',
                      invoice_number: pill.invoice_number || `temp_${Date.now()}_${Math.random()}`
                    };
                  }
                  return {
                    ...pill,
                    invoice_number: pill.invoice_number || `temp_${Date.now()}_${Math.random()}`
                  };
                });

              this.db.saveData('pills', this.pills)
                .then(() => {
                  console.log('Pills data saved to IndexedDB');
                })
                .catch(error => console.error('Error saving to IndexedDB:', error));
            } catch (err) {
              console.error('❌ Error saving pills to IndexedDB:', err);
            }
          }
          observer.next(response);
          observer.complete();
        },
        error: (err) => {
          console.error('❌ Failed to fetch pils', err);
          observer.error(err);
        }
      });
    });
  }
  //end dalia

  getPillsV2(page: number = 1, orderNumber: string = '', orderType: string = 'all', perPage: number = 28, status: string = 'all'): Observable<any> {
    const token = localStorage.getItem('authToken');
    if (!token) {
      throw new Error('No authentication token found');
    }
    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);
    let url = `${this.apiUrl}/invoices/v2?page=${page}&per_page=${perPage}`;
    if (orderNumber) {
      url += `&order_number=${orderNumber}`;
    }
    if (orderType && orderType !== 'all') {
      url += `&order_type=${orderType}`;
    }
    if (status && status !== 'all') {
      url += `&status=${status}`;
    }
    return this.http.get(url, { headers });
  }

  getTypeStatusCounts(type: string = 'all'): Observable<any> {
    const token = localStorage.getItem('authToken');
    if (!token) {
      throw new Error('No authentication token found');
    }
    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);
    let url = `${this.apiUrl}/invoices/types-status-counts`;
    if (type && type !== 'all') {
      url += `?type=${type}`;
    }
    return this.http.get(url, { headers });
  }

  /**
   * جلب كل الفواتير (كل الصفحات) وحفظها في IndexedDB للعمل offline
   */
  fetchAllInvoicesAndSaveToIndexedDB(): Observable<{ count: number }> {
    return new Observable(observer => {
      const run = async () => {
        let page = 1;
        let allInvoices: any[] = [];
        let hasMore = true;
        const perPage = 100;
        try {
          while (hasMore) {
            const res = await firstValueFrom(
              this.getPillsV2(page, '', 'all', perPage, 'all')
            );
            if (!res?.data?.invoices?.length) break;
            allInvoices = allInvoices.concat(res.data.invoices);
            const total = res.data.pagination?.total ?? 0;
            hasMore = res.data.pagination?.has_more ?? (page * perPage < total);
            page++;
          }
          if (allInvoices.length > 0) {
            await this.db.savePills(allInvoices);
            console.log('✅ كل الفواتير محفوظة في IndexedDB:', allInvoices.length);
          }
          observer.next({ count: allInvoices.length });
        } catch (err) {
          console.error('❌ خطأ في مزامنة كل الفواتير إلى IndexedDB:', err);
          observer.error(err);
        } finally {
          observer.complete();
        }
      };
      run();
    });
  }
}
