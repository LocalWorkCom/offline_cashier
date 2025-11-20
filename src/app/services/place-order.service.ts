import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { catchError, Observable, throwError } from 'rxjs';
import { baseUrl } from '../environment';

@Injectable({
  providedIn: 'root'
})
export class PlaceOrderService {
 private apiUrl =  `${baseUrl}api`;

  couriers: any;

  constructor(private http: HttpClient) { }

  placeOrder(orderData: any): Observable<any> {
    // const token = localStorage.getItem('authToken');

    // if (!token) {
    //   console.error('No auth token found!');
    //   return throwError(() => new Error('Authentication token missing.'));
    // }

    // const headers = new HttpHeaders({
    //   Authorization: `Bearer ${token}`,
    //   'Content-Type': 'application/json'
    // });

    return this.http.post(`${this.apiUrl}/orders/cashier/store/api`, orderData);
  }

  placeOrder_offline(orderData: any): Observable<any> {
    // const token = localStorage.getItem('authToken');

    // if (!token) {
    //   console.error('No auth token found!');
    //   return throwError(() => new Error('Authentication token missing.'));
    // }

    // const headers = new HttpHeaders({
    //   Authorization: `Bearer ${token}`,
    //   'Content-Type': 'application/json'
    // });

    return this.http.post(`${this.apiUrl}/orders/cashier/offline/store/api`, orderData);
  }




  orderStatus(orderData: any): Observable<any>{
    return this.http.post(`${this.apiUrl}/orders/change-status/3`, orderData);
  }
  getCouriers(): Observable<any> {
    const token = localStorage.getItem('authToken');

    if (!token) {
      console.error('No auth token found!');
      return throwError(() => new Error('Authentication token missing.'));
    }

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });

    return this.http.get(`${this.apiUrl}/deliveries`, { headers }).pipe(
      catchError((error: any) => {
        console.error('Error fetching couriers:', error);
        return throwError(() => error);
      })
    );
  }
}
