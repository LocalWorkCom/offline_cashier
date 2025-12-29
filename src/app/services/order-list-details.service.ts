import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { baseUrl, baseUrl2 } from '../environment';

@Injectable({
  providedIn: 'root',
})
export class OrderListDetailsService {
 private apiUrl = `${baseUrl}api`;

  private token = localStorage.getItem('authToken');

  constructor(private http: HttpClient) {}
  getOrderById(orderId: string): Observable<any> {
    const headers = new HttpHeaders().set(
      'Authorization',
      `Bearer ${this.token}`
    );

    return this.http.get(`${this.apiUrl}/orders/orderDetails/${orderId}`, {
      headers,
    });
  }
  NewgetOrderById(orderId: any): Observable<any> {
    const headers = new HttpHeaders().set(
      'Authorization',
      `Bearer ${this.token}`
    );

    return this.http.get(`${this.apiUrl}/orders/listnew/${orderId}`, {
      headers,
    });
  }

  getMergeableOrders(orderId: string): Observable<any> {
    const headers = new HttpHeaders().set(
      'Authorization',
      `Bearer ${this.token}`
    );

    return this.http.get(`${this.apiUrl}/orders/cashier/mergeable-orders/${orderId}`, {
      headers,
    });
  }

  mergeOrders(primaryOrderId: string, secondaryOrderId: string): Observable<any> {
    const headers = new HttpHeaders().set(
      'Authorization',
      `Bearer ${this.token}`
    );

    return this.http.post(
      `${this.apiUrl}/orders/cashier/merge-orders`,
      {
        primary_order_id: primaryOrderId,
        secondary_order_id: secondaryOrderId,
      },
      { headers }
    );
  }
}
