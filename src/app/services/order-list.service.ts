import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { AuthService } from './auth.service';
import { Observable } from 'rxjs';
import { firstValueFrom } from 'rxjs';
import { baseUrl } from '../environment';
import { IndexeddbService } from './indexeddb.service';


@Injectable({
  providedIn: 'root'
})
export class OrderListService {

  private apiUrl = `${baseUrl}api`;
  constructor(private http: HttpClient, private authService: AuthService, private db:IndexeddbService) { }

  getOrdersList(): Observable<any> {
    // const token = this.authService.getToken();
    const token = localStorage.getItem('authToken');

    if (!token) {
      throw new Error('No authentication token found');
    }

    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);

    return this.http.get(`${this.apiUrl}/orders/list`, { headers });
  }

  getOrdersListE(page: number = 1, perPage: number = 50): Observable<any> {
    const token = localStorage.getItem('authToken');

    if (!token) {
      throw new Error('No authentication token found');
    }

    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);

    return this.http.get(`${this.apiUrl}/orders/list?page=${page}&per_page=${perPage}`, { headers });
  }
//start dalia
fetchAndSaveOrders(): Observable<any> {
  return new Observable(observer => {
    this.getOrdersList().subscribe({
      next: async (response: any) => {
        if (response.status && response.data.orders) {
          try {
            await this.db.saveOrders(response.data.orders);
            await this.db.setOrdersLastSync(Date.now());
            console.log('✅ Orders saved to IndexedDB:', response.data.orders.length);
          } catch (err) {
            console.error('❌ Error saving orders to IndexedDB:', err);
          }
        }
        observer.next(response);
        observer.complete();
      },
      error: (err) => {
        console.error('❌ Failed to fetch orders', err);
        observer.error(err);
      }
    });
  });
}
//end dalia

  getOrdersListV2(type: string = 'All', page: number = 1, orderNumber: string = '', perPage: number = 30, status: string = 'all'): Observable<any> {
    const token = localStorage.getItem('authToken');

    if (!token) {
      throw new Error('No authentication token found');
    }

    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);
    let url = `${this.apiUrl}/orders/listv2?page=${page}&per_page=${perPage}`;
    
    if (type && type !== 'All' && type !== 'all') {
      const typeParam = type.toLowerCase();
      url += `&type=${typeParam}`;
    }

    if (orderNumber) {
      url += `&order_number=${orderNumber}`;
    }

    if (status && status !== 'all') {
      url += `&status=${status}`;
    }

    return this.http.get(url, { headers });
  }

  /**
   * جلب كل الطلبات (كل الصفحات) وحفظها في IndexedDB للعمل offline
   * يمر على كل الصفحات حتى يتم جلب العدد الكلي (استناداً إلى pagination أو order_counts)
   */
  fetchAllOrdersAndSaveToIndexedDB(type: string = 'All', status: string = 'all'): Observable<{ count: number }> {
    return new Observable(observer => {
      const run = async () => {
        let page = 1;
        let allOrders: any[] = [];
        const perPage = 100;
        const maxPages = 200; // حد أمان لتجنب حلقة لا نهائية
        try {
          let totalFromApi: number | null = null;
          while (page <= maxPages) {
            const res = await firstValueFrom(
              this.getOrdersListV2(type, page, '', perPage, status)
            );
            const orders = res?.data?.orders ?? [];
            if (!orders.length) break;

            allOrders = allOrders.concat(orders);
            const pagination = res?.data?.pagination;
            const total = pagination?.total ?? res?.data?.order_counts ?? res?.data?.total ?? null;
            if (total != null) totalFromApi = total;

            const hasMoreFromApi = pagination?.has_more;
            const hasMoreByCount = totalFromApi != null && allOrders.length < totalFromApi;
            const hasMore = hasMoreFromApi ?? hasMoreByCount ?? (orders.length >= perPage);

            if (!hasMore) break;
            page++;
          }
          if (allOrders.length > 0) {
            await this.db.saveOrders(allOrders);
            await this.db.setOrdersLastSync(Date.now());
            console.log('✅ كل الطلبات محفوظة في IndexedDB:', allOrders.length, '(إجمالي من API:', totalFromApi ?? 'غير معروف', ')');
          }
          observer.next({ count: allOrders.length });
        } catch (err) {
          console.error('❌ خطأ في مزامنة كل الطلبات إلى IndexedDB:', err);
          observer.error(err);
        } finally {
          observer.complete();
        }
      };
      run();
    });
  }

  getOrderTypesCounts(): Observable<any> {
    const token = localStorage.getItem('authToken');
    if (!token) {
      throw new Error('No authentication token found');
    }
    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);
    return this.http.get(`${this.apiUrl}/orders/types-counts`, { headers });
  }

  getTypeStatusCounts(type: string = 'all'): Observable<any> {
    const token = localStorage.getItem('authToken');
    if (!token) {
      throw new Error('No authentication token found');
    }
    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);
    let url = `${this.apiUrl}/orders/types-status-counts`;
    if (type && type !== 'all') {
      url += `?type=${type}`;
    }
    return this.http.get(url, { headers });
  }
}
