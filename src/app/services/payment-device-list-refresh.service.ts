import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

const STORAGE_KEY = 'offline_cashier_payment_device_balance_baseline_v2';

type BaselinePayload = {
  employeeId: string;
  cashierMachineId: string;
  /** رصيد كل جهاز كما عاد من الـ API لحظة نجاح «تحويل النقدية» */
  balances: Record<string, number>;
};

/**
 * بعد نجاح تحويل النقدية: يُحفظ خط أساس لأرصدة الماكينات (عرض فقط).
 * عمود الرصيد = رصيد الـ API الحالي − رصيد اللحظة بعد التحويل (لا يقل عن 0)، فيزيد مع كل دفع جديد خلال الشيفت.
 */
@Injectable({ providedIn: 'root' })
export class PaymentDeviceListRefreshService {
  private readonly refreshSubject = new Subject<void>();

  readonly refresh$ = this.refreshSubject.asObservable();

  notify(): void {
    this.refreshSubject.next();
  }

  private readPayload(): BaselinePayload | null {
    if (typeof sessionStorage === 'undefined') {
      return null;
    }
    try {
      const raw = sessionStorage.getItem(STORAGE_KEY);
      if (!raw) {
        return null;
      }
      const o = JSON.parse(raw) as BaselinePayload;
      if (!o || typeof o !== 'object' || !o.balances || typeof o.balances !== 'object') {
        return null;
      }
      return o;
    } catch {
      return null;
    }
  }

  private currentScope(): { employeeId: string; cashierMachineId: string } | null {
    if (typeof localStorage === 'undefined') {
      return null;
    }
    const employeeId = String(localStorage.getItem('employee_id') ?? '').trim();
    const cashierMachineId = String(localStorage.getItem('cashier_machine_id') ?? '').trim();
    if (!employeeId || !cashierMachineId) {
      return null;
    }
    return { employeeId, cashierMachineId };
  }

  /** يُستدعى بعد نجاح send-branch-safe عندما تكون أرقام الـ API جاهزة (نفس لحظة التحويل). */
  recordBaselineFromDevices(devices: Array<{ id: number; balance: number }>): void {
    if (typeof sessionStorage === 'undefined') {
      return;
    }
    const scope = this.currentScope();
    if (!scope) {
      return;
    }
    const balances: Record<string, number> = {};
    for (const d of devices) {
      if (Number.isFinite(d.id) && d.id > 0) {
        balances[String(d.id)] = Number(d.balance) || 0;
      }
    }
    const payload: BaselinePayload = {
      employeeId: scope.employeeId,
      cashierMachineId: scope.cashierMachineId,
      balances,
    };
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
  }

  clearBaseline(): void {
    if (typeof sessionStorage !== 'undefined') {
      sessionStorage.removeItem(STORAGE_KEY);
    }
  }

  /** رصيد الجدول: فرق عن خط الأساس بعد آخر تحويل؛ بدون خط أساس = رصيد الـ API كما هو. */
  displayBalanceAfterBaseline(deviceId: number, apiBalance: number): number {
    const api = Number(apiBalance) || 0;
    const payload = this.readPayload();
    const scope = this.currentScope();
    if (!payload || !scope) {
      return api;
    }
    if (payload.employeeId !== scope.employeeId || payload.cashierMachineId !== scope.cashierMachineId) {
      return api;
    }
    const base = payload.balances[String(deviceId)];
    if (base === undefined || base === null) {
      return api;
    }
    const delta = api - (Number(base) || 0);
    return delta > 0 ? delta : 0;
  }
}
