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
    total?: any,
    tip?: any
  ): Observable<any> {
    const token = localStorage.getItem('authToken');
    if (!token) {
      throw new Error('No token found. User is not authenticated.');
    }
  
    console.log('Updating invoice with parameters:', {
      orderNumber,
      paymentStatus,
      trackingStatus,
      cash,
      credit,
      DeliveredOrNot,
      total,
      tip
    });
  
    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    });
  
    // بناء payload واحد متسق
    let payload: any = {
      order_number: orderNumber,
      payment_status: paymentStatus || null,
      tip: tip || null,
    };
  
    // 1. إضافة حالة التوصيل إذا كان الطلب توصيل وكانت الحالة موجودة
    if (DeliveredOrNot && trackingStatus && trackingStatus.trim() !== '') {
      // تحويل trackingStatus إلى order_status إذا كانت قيمة صالحة
      const validTrackingStatuses = ['on_way', 'delivered', 'pending', 'completed'];
      if (validTrackingStatuses.includes(trackingStatus)) {
        payload.order_status = trackingStatus;
        // إرسال كـ Tracking_status أيضًا إذا كان الـ API يتوقعها
        payload.Tracking_status = trackingStatus;
      }
    }
  
    // 2. إضافة المبالغ النقدية إذا كانت حالة الدفع "مدفوعة"
    if (paymentStatus === 'paid') {
      // تأكد من أن القيم غير سالبة
      payload.cash_amount = Math.max(0, cash || 0);
      payload.credit_amount = Math.max(0, credit || 0);
      
      // تحقق من أن المبلغين ليسا صفر معًا إذا كانت الفاتورة مدفوعة
      if (payload.cash_amount === 0 && payload.credit_amount === 0) {
        console.warn('Warning: Both cash and credit amounts are zero for paid invoice');
      }
    }
  
    // 3. إضافة الحقول المشتركة الإضافية
    if (total) {
      payload.total = total;
    }
  
    console.log('Sending Payload to API:', JSON.stringify(payload, null, 2));
  
    return this.http
      .post<any>(`${this.apiUrl}/invoices/update/${orderNumber}`, payload, {
        headers,
      })
      .pipe(
        tap((response) => {
          console.log('✅ API Success Response:', response);
          console.log('Response Status:', response.status);
          console.log('Response Message:', response.message);
          console.log('Response Data:', response.data);
        }),
        catchError((err) => {
          console.error('❌ API Error Details:', {
            error: err,
            status: err.status,
            message: err.message,
            errorData: err.error
          });
          return throwError(() => err);
        })
      );
  }
}
