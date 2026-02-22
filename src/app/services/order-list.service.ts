import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { AuthService } from './auth.service';
import { Observable } from 'rxjs';
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

  getOrdersListV2(type: string = 'All', page: number = 1, orderNumber: string = ''): Observable<any> {
    const token = localStorage.getItem('authToken');

    if (!token) {
      throw new Error('No authentication token found');
    }

    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);
    let url = `${this.apiUrl}/orders/listv2?page=${page}`;
    
    if (type && type !== 'All') {
      // Use lower case for the type parameter as requested
      const typeParam = type.toLowerCase();
      url += `&type=${typeParam}`;
    }

    if (orderNumber) {
      url += `&order_number=${orderNumber}`;
    }

    return this.http.get(url, { headers });
  }

  getOrderTypesCounts(): Observable<any> {
    const token = localStorage.getItem('authToken');
    if (!token) {
      throw new Error('No authentication token found');
    }
    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);
    return this.http.get(`${this.apiUrl}/orders/types-counts`, { headers });
  }
}
