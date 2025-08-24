import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { baseUrl } from '../environment'; 

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
}
