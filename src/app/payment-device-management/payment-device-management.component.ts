import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { PaymentDeviceDeleteModalComponent } from './payment-device-delete-modal.component';
import { PaymentDeviceFormModalComponent, PaymentDeviceFormResult } from './payment-device-form-modal.component';

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
export class PaymentDeviceManagementComponent implements OnInit {
  private readonly storageKey = 'payment_devices';
  devices: PaymentDevice[] = [];

  constructor(private modalService: NgbModal) {}

  ngOnInit(): void {
    this.loadDevices();
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

        const newDevice: PaymentDevice = {
          id: Date.now(),
          name: result.name.trim(),
          ip: result.ip.trim(),
          balance: Number(result.balance) || 0,
          status: result.status
        };

        this.devices = [newDevice, ...this.devices];
        this.persistDevices();
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
    modalRef.componentInstance.device = { ...device };

    modalRef.result
      .then((result?: PaymentDeviceFormResult) => {
        if (!result) {
          return;
        }

        this.devices = this.devices.map((item) =>
          item.id === device.id
            ? {
                ...item,
                name: result.name.trim(),
                ip: result.ip.trim(),
                balance: Number(result.balance) || 0,
                status: result.status
              }
            : item
        );
        this.persistDevices();
      })
      .catch(() => {});
  }

  openDeleteModal(device: PaymentDevice): void {
    const modalRef = this.modalService.open(PaymentDeviceDeleteModalComponent, {
      centered: true,
      size: 'sm'
    });

    modalRef.componentInstance.deviceName = device.name;

    modalRef.result
      .then((confirmed?: boolean) => {
        if (!confirmed) {
          return;
        }

        this.devices = this.devices.filter((item) => item.id !== device.id);
        this.persistDevices();
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
    const savedDevices = localStorage.getItem(this.storageKey);
    if (!savedDevices) {
      return;
    }

    try {
      this.devices = JSON.parse(savedDevices);
    } catch {
      this.devices = [];
    }
  }

  private persistDevices(): void {
    localStorage.setItem(this.storageKey, JSON.stringify(this.devices));
  }
}

