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
 
 getOrdersListE(page: number = 1, perPage: number = 10) {
  return this.http.get<any>(`${this.apiUrl}/orders/listE?page=${page}&per_page=${perPage}`);
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

}
