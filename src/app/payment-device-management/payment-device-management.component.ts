import { CommonModule } from '@angular/common';
import { Component, OnDestroy, OnInit } from '@angular/core';
import { NgbDropdownModule, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Subscription } from 'rxjs';
import { PaymentDeviceDeleteModalComponent } from './payment-device-delete-modal.component';
import { PaymentDeviceFormModalComponent, PaymentDeviceFormResult } from './payment-device-form-modal.component';
import { baseUrl2 } from '../environment';
import { HttpClient } from '@angular/common/http';
import { PaymentDeviceListRefreshService } from '../services/payment-device-list-refresh.service';

interface PaymentDevice {
  id: number;
  name: string;
  ip: string;
  balance: number;
  status: 'active' | 'inactive';
}

@Component({
  selector: 'app-payment-device-management',
  standalone: true,
  imports: [CommonModule, NgbDropdownModule],
  templateUrl: './payment-device-management.component.html',
  styleUrls: ['./payment-device-management.component.css']
})
export class PaymentDeviceManagementComponent implements OnInit, OnDestroy {
  devices: PaymentDevice[] = [];
  isLoading = false;
  private refreshSub?: Subscription;

  constructor(
    private modalService: NgbModal,
    private httpClient: HttpClient,
    private paymentDeviceListRefresh: PaymentDeviceListRefreshService
  ) {}

  ngOnInit(): void {
    this.loadDevices();
    this.refreshSub = this.paymentDeviceListRefresh.refresh$.subscribe(() => this.loadDevices());
  }

  ngOnDestroy(): void {
    this.refreshSub?.unsubscribe();
  }

  /**
   * إدارة الأجهزة (الإعدادات): نعرض رصيد الـ API الفعلي.
   * تعديل «خط الأساس» بعد تحويل النقدية مخصص لسياق الكاشير وليس لإخفاء أرصدة الماكينات هنا.
   */
  displayBalance(device: PaymentDevice): number {
    const n = Number(device?.balance);
    return Number.isFinite(n) ? n : 0;
  }

  openCreateModal(): void {
    const modalRef = this.modalService.open(PaymentDeviceFormModalComponent, {
      centered: true,
      backdrop: 'static',
      size: 'lg'
    });

    modalRef.componentInstance.mode = 'create';
    modalRef.componentInstance.title = 'إضافة جهاز دفع';

    modalRef.result
      .then((result?: PaymentDeviceFormResult) => {
        if (!result) {
          return;
        }
        // Always reload: server assigns the real primary key (never use Date.now() as id — it breaks update/delete URLs).
        this.loadDevices();
      })
      .catch(() => {});
  }

  openEditModal(device: PaymentDevice): void {
    const modalRef = this.modalService.open(PaymentDeviceFormModalComponent, {
      centered: true,
      backdrop: 'static',
      size: 'lg'
    });

    modalRef.componentInstance.mode = 'edit';
    modalRef.componentInstance.title = 'تعديل جهاز الدفع';
    modalRef.componentInstance.device = { ...device, balance: this.displayBalance(device) };

    modalRef.result
      .then((result?: PaymentDeviceFormResult) => {
        if (!result) {
          return;
        }
        this.loadDevices();
      })
      .catch(() => {});
  }

  openDeleteModal(device: PaymentDevice): void {
    const modalRef = this.modalService.open(PaymentDeviceDeleteModalComponent, {
      centered: true,
      size: 'sm'
    });

    modalRef.componentInstance.deviceName = device.name;
    modalRef.componentInstance.deviceId = device.id;
    modalRef.componentInstance.deviceIp = device.ip;
    modalRef.componentInstance.deviceBalance = Number(this.displayBalance(device)) || 0;
    modalRef.componentInstance.deviceStatus = device.status;

    modalRef.result
      .then((result?: { deleted?: boolean; inactivated?: boolean }) => {
        if (!result) {
          return;
        }
        if (result.deleted || result.inactivated) {
          this.loadDevices();
        }
      })
      .catch(() => {});
  }

  getStatusLabel(status: PaymentDevice['status']): string {
    return status === 'active' ? 'نشط' : 'غير نشط';
  }

  private loadDevices(): void {
    this.isLoading = true;
    this.httpClient.get<any>(`${baseUrl2}/payment-device/`, { observe: 'response' }).subscribe({
      next: (response) => {
        if (response.status !== 200) {
          this.devices = [];
          this.isLoading = false;
          return;
        }
        const res = response.body;
        const apiDevices = Array.isArray(res) ? res : Array.isArray(res?.data) ? res.data : [];
        this.devices = apiDevices
          .map((device: any) => ({
            id: Number(device?.id),
            name: String(device?.device_name ?? '').trim(),
            ip: String(device?.IP ?? '').trim(),
            balance: this.parseBalanceNumber(device?.Balance),
            status: device?.status === 'active' ? 'active' : 'inactive',
          }))
          .filter((d: PaymentDevice) => Number.isFinite(d.id) && d.id > 0);
        this.isLoading = false;
      },
      error: () => {
        if (this.devices.length === 0) {
          this.devices = [];
        }
        this.isLoading = false;
      }
    });
  }

  private parseBalanceNumber(value: unknown): number {
    if (value == null || value === '') {
      return 0;
    }
    if (typeof value === 'number' && Number.isFinite(value)) {
      return value;
    }
    const s = String(value).replace(/,/g, '').trim();
    const n = Number(s);
    return Number.isFinite(n) ? n : 0;
  }
}

