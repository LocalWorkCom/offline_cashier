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
  getOrderDateTimeSeperated(): { dateStr: string; timeStr: string } {
    const created_at =
      this.data?.created_at ??
      this.data?.branchDetails?.[0]?.created_at ??
      this.data?.invoices?.[0]?.created_at;
    if (created_at) {
      return this.printTime.formatForPrint(created_at);
    }
    if (this.data?.date != null || this.data?.time != null) {
      const formatted = this.printTime.parseAndFormatOrderDateTime(this.data.date, this.data.time);
      const parts = formatted.split('   ');
      return {
        dateStr: parts[0] || '--/--/----',
        timeStr: parts[1] || '--:-- --'
      };
    }
    return { dateStr: '--/--/----', timeStr: '--:-- --' };
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
   * Get formatted addons for an order item.
   * Supports order print and invoice print: addons, dish_addons, addon_categories[].addons.
   * Normalizes addon_name and addon_name_en from any common API keys.
   */
  getAddons(orderItem: any): any[] {
    if (!orderItem) return [];
    const rawAddons: any[] = [];
    if (Array.isArray(orderItem.addons)) rawAddons.push(...orderItem.addons);
    if (Array.isArray(orderItem.dish_addons)) rawAddons.push(...orderItem.dish_addons);
    if (Array.isArray(orderItem.addon_categories)) {
      for (const cat of orderItem.addon_categories) {
        if (Array.isArray(cat.addons)) rawAddons.push(...cat.addons);
      }
    }
    if (rawAddons.length === 0) return [];

    return rawAddons
      .filter((addon: any) => addon && (addon.addon_name || addon.name || addon.addon_name_ar || (addon.addon && (addon.addon.addon_name || addon.addon.name))))
      .map((addon: any) => this.normalizeAddonForDisplay(addon));
  }

  /**
   * Normalize a single addon so addon_name and addon_name_en are set from any common API keys.
   */
  private normalizeAddonForDisplay(addon: any): any {
    const inner = addon.addon;
    const name =
      addon.addon_name ??
      addon.name ??
      addon.addon_name_ar ??
      (inner && (inner.addon_name ?? inner.name ?? inner.addon_name_ar)) ??
      '';
    const nameEn =
      addon.addon_name_en ??
      addon.name_en ??
      addon.name_english ??
      addon.addon_name_english ??
      (inner && (inner.addon_name_en ?? inner.name_en ?? inner.name_english ?? inner.addon_name_english)) ??
      '';
    const resolvedEn = typeof nameEn === 'string' && nameEn.trim() !== '' ? nameEn.trim() : (addon.addon_name_en ?? '');
    return {
      ...addon,
      addon_name: name,
      addon_name_en: resolvedEn,
      quantity: addon.quantity ?? addon.addon_quantity ?? 1,
    };
  }

  /**
   * Get dish English name from any common API key (invoice/order print).
   */
  getDishNameEn(orderItem: any): string {
    if (!orderItem) return '';
    const en =
      orderItem.dish_name_en ??
      orderItem.name_en ??
      orderItem.dish_name_english ??
      orderItem.name_english ??
      orderItem.dish?.dish_name_en ??
      orderItem.dish?.name_en ??
      (orderItem.dish && (orderItem.dish as any).name_english) ??
      '';
    return typeof en === 'string' ? en.trim() : '';
  }

  /**
   * Get formatted size for an order item (Arabic/main).
   * Handles string or object with name/name_ar.
   */
  getSize(orderItem: any): string | null {
    if (!orderItem) return null;
    const size = orderItem.size ?? orderItem.size_name ?? orderItem.dish_size;
    if (size != null) {
      if (typeof size === 'string' && size.trim() !== '') return size.trim();
      if (typeof size === 'object' && (size.name_ar ?? size.name ?? size.size_name)) {
        return String(size.name_ar ?? size.name ?? size.size_name ?? '').trim() || null;
      }
    }
    return null;
  }

  /**
   * Get size English name from any common API key (invoice/order print).
   */
  getSizeEn(orderItem: any): string {
    if (!orderItem) return '';
    const size = orderItem.size ?? orderItem.size_name ?? orderItem.dish_size;
    const en =
      orderItem.size_en ??
      orderItem.size_name_en ??
      orderItem.dish_size_en ??
      orderItem.size_english ??
      (typeof size === 'object' && size && (size.name_en ?? (size as any).name_english ?? (size as any).size_name_en)) ??
      '';
    return typeof en === 'string' ? en.trim() : '';
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

  /** عدد المنتجات = مجموع الكميات (مثلاً 1 + 2 = 3) وليس عدد الأسطر. */
  getTotalProductCount(): number {
    const details = this.data?.invoices?.[0]?.orderDetails ?? this.data?.orderDetails ?? [];
    if (!Array.isArray(details)) return 0;
    return details.reduce((sum, item) => sum + (Number(item?.quantity) || 0), 0);
  }
}
