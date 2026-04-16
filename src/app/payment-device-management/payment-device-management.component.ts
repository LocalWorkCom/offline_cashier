import { CommonModule } from '@angular/common';
import { Component, OnDestroy, OnInit } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
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
  imports: [CommonModule],
  templateUrl: './payment-device-management.component.html',
  styleUrls: ['./payment-device-management.component.css']
})
export class PaymentDeviceManagementComponent implements OnInit, OnDestroy {
  devices: PaymentDevice[] = [];
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

  displayBalance(device: PaymentDevice): number {
    return this.paymentDeviceListRefresh.displayBalanceAfterBaseline(device.id, device.balance);
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

  getStatusClass(status: PaymentDevice['status']): string {
    return status === 'active' ? 'text-success' : 'text-danger';
  }

  private loadDevices(): void {
    this.httpClient.get<any>(`${baseUrl2}/payment-device/`).subscribe({
      next: (res) => {
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
      },
      error: () => {
        this.devices = [];
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

