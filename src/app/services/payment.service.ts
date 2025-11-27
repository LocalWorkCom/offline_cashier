import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class PaymentService {
  private paymentResetSource = new Subject<void>();
  paymentReset$ = this.paymentResetSource.asObservable();

  resetAllPaymentCalculations(): void {
    console.log('🔄 إعادة تعيين جميع حسابات الدفع...');

    try {
      // 1. إعادة تعيين حالة الدفع لغير مدفوع
      localStorage.setItem('selectedPaymentStatus', 'unpaid');

      // 2. مسح مبالغ الدفع من localStorage
      localStorage.removeItem('cash_amountt');
      localStorage.removeItem('credit_amountt');

      // 3. مسح بيانات الإكرامية من localStorage
      localStorage.removeItem('finalTipSummary');

      // إرسال حدث إعادة التعيين
      this.paymentResetSource.next();

      console.log('✅ تم إعادة تعيين جميع حسابات الدفع بنجاح');
    } catch (error) {
      console.error('❌ خطأ في إعادة تعيين حسابات الدفع:', error);
    }
  }
}