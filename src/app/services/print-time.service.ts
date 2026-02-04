import { Injectable } from '@angular/core';

/**
 * خدمة موحدة للوقت على كل المطبوعات (كاشير، مطبخ، بار، موزع).
 * تضمن:
 * - مصدر وقت واحد (وقت الجهاز المحلي = وقت الفرع عند ضبط توقيت الجهاز على فرع).
 * - تنسيق 12 ساعة (hh:mm AM/PM) في كل مكان.
 * - عدم وجود فرق زمني بين تطبيق الكاشير وطباعة الكاشير وطباعة المطبخ.
 */
@Injectable({
  providedIn: 'root',
})
export class PrintTimeService {

  /** تنسيق التاريخ للمطبوعات: dd/MM/yyyy */
  readonly dateFormat = 'dd/MM/yyyy';
  /** تنسيق الوقت للمطبوعات: 12 ساعة مع AM/PM (بدون ثوانٍ) */
  readonly timeFormat = 'hh:mm a';

  /**
   * تنسيق تاريخ للطباعة: وقت 12 ساعة وتاريخ موحد.
   * يستخدم التوقيت المحلي للجهاز (يفترض أن الجهاز مضبوط على توقيت الفرع).
   */
  formatForPrint(dateInput: Date | string | null | undefined): { dateStr: string; timeStr: string } {
    const fallback = { dateStr: '--/--/----', timeStr: '--:-- --' };
    if (dateInput == null) return fallback;
    const date = typeof dateInput === 'string' ? new Date(dateInput) : dateInput;
    if (isNaN(date.getTime())) return fallback;

    const dateStr = this.toLocalDateString(date);
    const timeStr = this.toLocalTime12(date);
    return { dateStr, timeStr };
  }

  /**
   * الوقت الحالي منسق للطباعة (مثل "وقت الطباعة" على التذاكر).
   * نفس المصدر المستخدم لوقت الطلب لضمان الاتساق.
   */
  getPrintTimeNow(): { dateStr: string; timeStr: string } {
    return this.formatForPrint(new Date());
  }

  /**
   * سطر واحد لوقت وتاريخ الطلب (مثل "15/01/2026  02:00 PM").
   */
  formatOrderDateTime(dateInput: Date | string | null | undefined): string {
    const { dateStr, timeStr } = this.formatForPrint(dateInput);
    return `${dateStr}   ${timeStr}`;
  }

  /**
   * تحويل تاريخ ووقت قادمين من الـ API (أي تنسيق) إلى سطر واحد بتنسيق 12 ساعة.
   * يدعم: ISO (2026-01-15, 15:59:48)، أو (dd-MM-yyyy, HH:mm)، أو تنسيقات جاهزة.
   */
  parseAndFormatOrderDateTime(dateStr: string | null | undefined, timeStr: string | null | undefined): string {
    if (dateStr == null && timeStr == null) return '--/--/----   --:-- --';
    const combined = this.tryParseAsDate(dateStr, timeStr);
    if (combined) return this.formatOrderDateTime(combined);
    if (dateStr != null && timeStr != null) return `${String(dateStr).trim()}   ${String(timeStr).trim()}`;
    if (dateStr != null) return `${String(dateStr).trim()}   --:-- --`;
    return `--/--/----   ${timeStr != null ? String(timeStr).trim() : '--:-- --'}`;
  }

  /** محاولة تحويل dateStr + timeStr إلى كائن Date (يدعم تنسيقات شائعة من الـ API). */
  private tryParseAsDate(dateStr: string | null | undefined, timeStr: string | null | undefined): Date | null {
    const d = String(dateStr ?? '').trim();
    const t = String(timeStr ?? '').trim();
    if (!d && !t) return null;
    // ISO: 2026-01-15 أو 15-01-2026 أو 15/01/2026
    // وقت: 15:59:48 أو 15:59 أو 03:59 PM
    let iso = '';
    if (d.includes('-') && d.length >= 10) {
      const [a, b, c] = d.split('-');
      if (a.length === 4) iso = `${a}-${b}-${c}`; // yyyy-MM-dd
      else if (c.length === 4) iso = `${c}-${b}-${a}`; // dd-MM-yyyy
    } else if (d.includes('/')) {
      const parts = d.split('/');
      if (parts.length >= 3 && parts[2].length === 4) {
        const [a, b, c] = parts;
        iso = `${c}-${a.padStart(2, '0')}-${b.padStart(2, '0')}`; // dd/MM/yyyy
      }
    }
    if (!iso) return null;
    let timePart = 'T00:00:00';
    if (t) {
      const match24 = t.match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?/);
      const match12 = t.match(/(\d{1,2}):(\d{2})\s*(AM|PM)/i);
      if (match24) {
        const h = parseInt(match24[1], 10);
        const m = match24[2];
        const s = (match24[3] ?? '00');
        timePart = `T${String(h).padStart(2, '0')}:${m}:${s}`;
      } else if (match12) {
        let h = parseInt(match12[1], 10);
        if (match12[3].toUpperCase() === 'PM' && h !== 12) h += 12;
        if (match12[3].toUpperCase() === 'AM' && h === 12) h = 0;
        timePart = `T${String(h).padStart(2, '0')}:${match12[2]}:00`;
      }
    }
    const date = new Date(iso + timePart);
    return isNaN(date.getTime()) ? null : date;
  }

  private toLocalDateString(d: Date): string {
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
  }

  private toLocalTime12(d: Date): string {
    const h = d.getHours();
    const m = d.getMinutes();
    const period = h >= 12 ? 'PM' : 'AM';
    const hour12 = h % 12 || 12;
    const hourStr = String(hour12).padStart(2, '0');
    const minStr = String(m).padStart(2, '0');
    return `${hourStr}:${minStr} ${period}`;
  }
}
