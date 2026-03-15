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
    tip?: any,
    referenceNumber?: string,
    couponData?: {
      coupon_code?: string;
      coupon_value?: number;
      coupon_type?: string;
      coupon_title?: string;
    }
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
    // ملاحظة: الـ API يجب أن يقبل payment_status = 'paid' أو 'unpaid' (راجع docs/API_PAYMENT_STATUS_FIX.md عند خطأ 400)
    // ✅ في حالة كوبون 100% على الطلب يجعل إجمالي الفاتورة = 0 نعتبر الفاتورة "مدفوعة" حتى لو لم يتم إدخال مبالغ نقدية/فيزا.
    let finalPaymentStatus = paymentStatus || null;

    const normalizedTotal = total !== undefined && total !== null ? Number(total) : null;
    const normalizedCouponValue =
      couponData && couponData.coupon_value !== undefined && couponData.coupon_value !== null
        ? Number(couponData.coupon_value)
        : null;

    const isFullOrderCoupon =
      couponData != null &&
      (couponData.coupon_type === 'percentage' ||
        couponData.coupon_type === 'Percentage' ||
        couponData.coupon_type === 'percent') &&
      normalizedCouponValue === 100 &&
      normalizedTotal === 0;

    if (isFullOrderCoupon && finalPaymentStatus !== 'paid') {
      finalPaymentStatus = 'paid';
    }

    let payload: any = {
      order_number: orderNumber,
      payment_status: finalPaymentStatus,
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
      
      // إضافة رقم المرجع إذا كان موجودًا
      if (referenceNumber && referenceNumber.trim()) {
        payload.reference_number = referenceNumber.trim();
      }
      
      // تحقق من أن المبلغين ليسا صفر معًا إذا كانت الفاتورة مدفوعة
      if (payload.cash_amount === 0 && payload.credit_amount === 0) {
        console.warn('Warning: Both cash and credit amounts are zero for paid invoice');
      }
    }
  
    // 3. إضافة الحقول المشتركة الإضافية
    // إرسال total دائماً حتى لو كان 0 أو null للتأكد من أن الـ backend يستخدمه
    payload.total = total !== null && total !== undefined ? Number(total) : null;
    
    // 4. إضافة بيانات الكوبون إذا كانت موجودة
    if (couponData) {
      if (couponData.coupon_code) payload.coupon_code = couponData.coupon_code;
      if (couponData.coupon_value !== undefined && couponData.coupon_value !== null) {
        payload.coupon_value = Number(couponData.coupon_value);
      }
      if (couponData.coupon_type) payload.coupon_type = couponData.coupon_type;
      if (couponData.coupon_title) payload.coupon_title = couponData.coupon_title;
    }
  
    console.log('📤 Sending Payload to API:', {
      orderNumber,
      paymentStatus,
      cash_amount: payload.cash_amount,
      credit_amount: payload.credit_amount,
      total: payload.total,
      couponData: payload.coupon_code ? {
        coupon_code: payload.coupon_code,
        coupon_value: payload.coupon_value,
        coupon_type: payload.coupon_type,
        coupon_title: payload.coupon_title
      } : null,
      fullPayload: JSON.stringify(payload, null, 2)
    });
  
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
