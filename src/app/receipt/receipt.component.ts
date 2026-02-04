import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PrintTimeService } from '../services/print-time.service';

@Component({
  selector: 'app-receipt',
  imports: [CommonModule],
  templateUrl: './receipt.component.html',
  styleUrl: './receipt.component.css'
})
export class ReceiptComponent {
  @Input() data: any;

  constructor(private printTime: PrintTimeService) {}

  ngOnInit() {
    console.log('Receipt data:', this.data);
    console.log('Cashier:', this.data?.cashier);
    console.log('Waiter:', this.data?.waiter);
    console.log('Make type:', this.data?.make_type);
  }

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

  /** وقت الطباعة بتنسيق 12 ساعة (نفس مصدر وقت الطلب للاتساق). */
  getPrintTimeNow(): { dateStr: string; timeStr: string } {
    return this.printTime.getPrintTimeNow();
  }

  /** وقت وتاريخ الطلب بتنسيق 12 ساعة دائماً؛ مصدر واحد: الخدمة. */
  getOrderDateTime(): string {
    const created_at =
      this.data?.created_at ??
      this.data?.branchDetails?.[0]?.created_at ??
      this.data?.invoices?.[0]?.created_at;
    if (created_at) {
      return this.printTime.formatOrderDateTime(created_at);
    }
    if (this.data?.date != null || this.data?.time != null) {
      return this.printTime.parseAndFormatOrderDateTime(this.data.date, this.data.time);
    }
    return '--/--/----   --:-- --';
  }

  getTableNumber(): string {
    const invoice = this.data?.invoices?.[0];
    if (invoice && invoice.branch_details) {
      // Handle if branch_details is an array or object
      const details = Array.isArray(invoice.branch_details) 
        ? invoice.branch_details[0] 
        : invoice.branch_details;
        
      return details?.table_number || '';
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

  /**
   * Get formatted addons for an order item
   * Ensures addons are properly displayed even if data structure varies
   */
  getAddons(orderItem: any): any[] {
    if (!orderItem) return [];
    
    // Handle different possible data structures
    if (Array.isArray(orderItem.addons)) {
      return orderItem.addons.filter((addon: any) => addon && addon.addon_name);
    }
    
    return [];
  }

  /**
   * Get formatted size for an order item
   * Returns size if available and not empty
   */
  getSize(orderItem: any): string | null {
    if (!orderItem) return null;
    
    const size = orderItem.size || orderItem.size_name || orderItem.dish_size;
    if (size && typeof size === 'string' && size.trim() !== '') {
      return size.trim();
    }
    
    return null;
  }

  /**
   * Check if order item has addons
   */
  hasAddons(orderItem: any): boolean {
    const addons = this.getAddons(orderItem);
    return addons.length > 0;
  }

  getDriverName(): string {
    const invoice = this.data?.invoices?.[0];
    if (!invoice) return 'N/A';
    // Direct property or inside delivery object
    return invoice.delivery_name || 
           (invoice.delivery ? (invoice.delivery.first_name + ' ' + invoice.delivery.last_name) : 'N/A');
  }

  getDriverPhone(): string {
    const invoice = this.data?.invoices?.[0];
    if (!invoice) return 'N/A';
    return invoice.delivery_phone || 
           (invoice.delivery ? invoice.delivery.phone_number : 'N/A');
  }

  getCustomerName(): string {
    const invoice = this.data?.invoices?.[0];
    if (!invoice) return 'N/A';
    console.log(invoice.address_details);
    // Backend provides pre-calculated client name in address_details for delivery
    if (invoice.address_details && invoice.address_details.client_name) {
      return invoice.address_details.client_name;
    }

    // Fallback for non-delivery or legacy structure
    if (invoice.client && invoice.client.flag !== 'unknown') {
      return invoice.client.name || 'N/A';
    }
    return invoice.address?.user_name || invoice.client_name || 'N/A';
  }

  getCustomerPhone(): string {
    const invoice = this.data?.invoices?.[0];
    if (!invoice) return 'N/A';
    
    if (invoice.address_details && invoice.address_details.client_phone) {
      return invoice.address_details.client_phone;
    }
    
    if (invoice.client && invoice.client.flag !== 'unknown') {
      return invoice.client.phone || 'N/A';
    }
    return invoice.address?.address_phone || invoice.client_phone || 'N/A';
  }

  getDeliveryAddress(): string {
    const invoice = this.data?.invoices?.[0];
    // Prioritize address_details from backend
    return invoice?.address_details?.client_address || invoice?.address?.address || 'N/A';
  }

  isDelivery(): boolean {
    const type = this.data?.invoices?.[0]?.order_type;
    return type === 'Delivery' || type === 'توصيل';
  }
    getTrackingStatusTranslationEn(status: string): string {
    const statusTranslations: { [key: string]: string } = {
      completed: 'Completed',
      pending: 'Pending',
      cancelled: 'Cancelled',
      packing: 'Packing',
      readyForPickup: 'Ready For Pickup',
      on_way: ' On the Way',
      in_progress: 'In progress',
      delivered: 'Delivered',
    };

    return statusTranslations[status] || status || 'N/A'; // Default to status if no translation found
  }

  getTrackingStatusTranslation(status: string): string {
    const statusTranslations: { [key: string]: string } = {
      completed: 'مكتمل',
      pending: 'في انتظار الموافقة',
      cancelled: 'ملغي',
      packing: 'يتم تجهيزها',
      readyForPickup: 'جاهز للاستلام',
      on_way: 'في الطريق',
      in_progress: 'يتم تحضير الطلب',
      delivered: 'تم التوصيل',
    };

    return statusTranslations[status] || status || 'غير محدد';
  }
}
