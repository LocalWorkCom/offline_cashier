import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { baseUrl,baseUrl2 } from '../environment';

@Injectable({
  providedIn: 'root',
})
export class PrintedInvoiceService {
  private apiUrl = `${baseUrl}api`;
  private apiUrl2 = `${baseUrl2}`;

  private token = localStorage.getItem('authToken');

  constructor(private http: HttpClient) { }

  printInvoice(order_id: number, cashier_machine_id: any, payment_method: any): Observable<any> {
    const token = localStorage.getItem('authToken');
    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    });

    const body = {
      order_id,
      cashier_machine_id,
      payment_method// Adding cashier_machine as 1
    };

    return this.http.post(`${this.apiUrl}/invoices/print`, body, { headers });
  }

  printkitchen(orderData: any,order_id:any): Observable<any> {
    const headers = new HttpHeaders({
      Authorization: `Bearer ${this.token}`,
      'Content-Type': 'application/json',
    });

    const body = {
      order_data: orderData,
      order_id:order_id
    };

    return this.http.post(`${this.apiUrl}/print-kitchen`, body, { headers });
  }

  printWaiter(order_id:any): Observable<any> {
    const headers = new HttpHeaders({
      Authorization: `Bearer ${this.token}`,
      'Content-Type': 'application/json',
    });

    const body = {
      order_id:order_id
    };

    return this.http.post(`${this.apiUrl2}/print-waiter`, body, { headers });
  }

  printMenu(order_id: number | string, data?: any): Observable<any> {
    const token = localStorage.getItem('authToken');
    console.log(`[PrintedInvoiceService] Requesting print menu for order_id: ${order_id} (Token present: ${!!token})`);
    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    });
    // const body = {
    //   order_id:order_id
    // };

    console.log("data",data);
    return this.http.post(`${this.apiUrl}/print-menu/${order_id}`, data, { headers });
  }
}
