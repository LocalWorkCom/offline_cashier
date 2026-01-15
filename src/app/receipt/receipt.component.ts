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

}
