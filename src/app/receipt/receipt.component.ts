import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-receipt',
  imports: [CommonModule],
  templateUrl: './receipt.component.html',
  styleUrl: './receipt.component.css'
})
export class ReceiptComponent {
  @Input() data: any;

  getOrderTypeLabel(type: string): string {
    const map: any = {
      'dine-in': 'في المطعم',
      'Takeaway': 'استلام',
      'talabat': 'طلبات',
      'Delivery': 'توصيل'
    };

    return map[type] || type;
  }

  getCurrencySymbol(): string {
    if (this.data?.invoices?.[0]?.currency_symbol) {
      return this.data.invoices[0].currency_symbol;
    }
    return '';
  }

  getPaymentMethod(): string {
    if (this.data?.invoices?.[0]?.transactions?.[0]?.payment_method) {
      const method = this.data.invoices[0].transactions[0].payment_method;
      const methodMap: any = {
        'cash': 'نقدي',
        'card': 'بطاقة',
        'deferred': 'آجل',
        'online': 'أونلاين'
      };
      return methodMap[method] || method;
    }
    return 'غير محدد';
  }

  now(): Date {
    return new Date();
  }

  getTableNumber(): string {
    if (this.data?.table_number) {
      return this.data.table_number;
    }
    return '';
  }

  hasServiceFees(serviceFees: any): boolean {
    if (serviceFees == null || serviceFees === undefined) {
      return false;
    }
    const value = typeof serviceFees === 'string' ? parseFloat(serviceFees) : Number(serviceFees);
    return !isNaN(value) && value > 0;
  }

  getTotalTipAmount(): number {
    if (!this.data?.invoice_tips || !Array.isArray(this.data.invoice_tips) || this.data.invoice_tips.length === 0) {
      return 0;
    }
    const totalTip = this.data.invoice_tips.reduce((sum: number, tip: any) => {
      const tipAmount = typeof tip.tip_amount === 'string' ? parseFloat(tip.tip_amount) : Number(tip.tip_amount || 0);
      return sum + (isNaN(tipAmount) ? 0 : tipAmount);
    }, 0);
    return totalTip;
  }

}
