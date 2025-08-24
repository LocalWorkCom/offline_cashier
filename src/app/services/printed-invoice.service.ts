import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { baseUrl } from '../environment';

@Injectable({
  providedIn: 'root',
})
export class PrintedInvoiceService {
  private apiUrl = `${baseUrl}api`;

  private token = localStorage.getItem('authToken');

  constructor(private http: HttpClient) { }

  printInvoice(order_id: number, cashier_machine_id: any, payment_method: any): Observable<any> {
    const headers = new HttpHeaders({
      Authorization: `Bearer ${this.token}`,
      'Content-Type': 'application/json',
    });

    const body = {
      order_id,
      cashier_machine_id,
      payment_method// Adding cashier_machine as 1
    };

    return this.http.post(`${this.apiUrl}/invoices/print`, body, { headers });
  }
}
