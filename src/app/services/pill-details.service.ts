import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { catchError, Observable, tap, throwError } from 'rxjs';
import { AuthService } from './auth.service'; // Import AuthService
import { baseUrl } from '../environment';

@Injectable({
  providedIn: 'root',
})
export class PillDetailsService {
  private apiUrl = `${baseUrl}api`;

  constructor(
    private http: HttpClient,
    private authService: AuthService // Inject AuthService
  ) { }
  payload: any;
  getPillsDetailsById(pillId: any): Observable<any> {
    // const token = this.authService.getToken();
    const token = localStorage.getItem('authToken');
    if (!token) {
      throw new Error('No token found. User is not authenticated.');
    }

    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);

    // return this.http.get(`${this.apiUrl}/invoices/invoice-details/${pillId}`, {
    return this.http.get(`${this.apiUrl}/invoices/invoice/${pillId}`, {

      headers,
    });
  }

  updateInvoiceStatus(
    orderNumber: string,
    paymentStatus?: string,
    trackingStatus?: string,
    cash?: number,
    credit?: number,
    DeliveredOrNot?: boolean,
    totalll?: any,
    tip?: any,
    referenceNumber?: string
  ): Observable<any> {
    const token = localStorage.getItem('authToken');
    if (!token) {
      throw new Error('No token found. User is not authenticated.');
    }

    console.log('Payment Status:', paymentStatus);
    console.log('Tracking Status:', trackingStatus);
    console.log('Delivered Or Not:', DeliveredOrNot);

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    });

    if (DeliveredOrNot) { // delivery
      if (trackingStatus == 'on_way') {
        this.payload = {
          order_number: orderNumber,  // ✅ أضيفي order_number
          payment_status: paymentStatus || null,  // ✅ أضيفي payment_status
          order_status: trackingStatus || null,
          Tracking_status: trackingStatus || null,
          cash_amount: 0,  // ✅ أضيفي قيم افتراضية
          credit_amount: 0,
          tip: tip || null,
          reference_number: referenceNumber || null,
        };
      } else {
        this.payload = {
          order_number: orderNumber,
          payment_status: paymentStatus || null,
          order_status: trackingStatus || null,
          Tracking_status: trackingStatus || null,
          cash_amount: totalll,
          credit_amount: 0,
          tip: tip || null,
          reference_number: referenceNumber || null,
        };
      }
    } else { // dine-in, takeaway, talabat
      if (paymentStatus == 'paid') {
        this.payload = {
          order_number: orderNumber,
          payment_status: paymentStatus || null,
          cash_amount: cash || 0,
          credit_amount: credit || 0,
          tip: tip || null,
          reference_number: referenceNumber || null,
        };
      } else {
        this.payload = {
          order_number: orderNumber,
          payment_status: paymentStatus || null,
          tip: tip || null,
          reference_number: referenceNumber || null,
        };
      }
    }

    console.log('✅ Sending Payload:', JSON.stringify(this.payload));

    return this.http
      .post<any>(`${this.apiUrl}/invoices/update/${orderNumber}`, this.payload, {
        headers,
      })
      .pipe(
        tap((response) => console.log('✅ API Success:', response)),
        catchError((err) => {
          console.error('❌ API Error:', err);
          return throwError(err);
        })
      );
  }
}
