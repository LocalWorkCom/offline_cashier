import { CommonModule } from '@angular/common';
import { Component, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { baseUrl2 } from '../environment';

@Component({
  selector: 'app-payment-device-delete-modal',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './payment-device-delete-modal.component.html'
})
export class PaymentDeviceDeleteModalComponent {
  @Input() deviceName = '';
  @Input() deviceId?: number;
  @Input() deviceIp = '';
  @Input() deviceBalance = 0;
  @Input() deviceStatus: 'active' | 'inactive' = 'active';
  errorMessage = '';
  isSubmitting = false;

  constructor(public activeModal: NgbActiveModal, private httpClient: HttpClient) {}

  confirmDelete(): void {
    if (!this.deviceId) {
      return;
    }

    this.errorMessage = '';
    this.isSubmitting = true;

    // Business rule: devices with paid amounts must be preserved for reconciliation history.
    if (Number(this.deviceBalance) > 0) {
      this.httpClient
        .post(`${baseUrl2}/payment-device/update/${this.deviceId}`, {
          device_name: this.deviceName,
          IP: this.deviceIp,
          status: 'inactive',
          Status: 'inactive',
          is_active: 0
        })
        .subscribe({
          next: () => {
            this.activeModal.close({ deleted: false, inactivated: true });
          },
          error: (err: any) => {
            this.isSubmitting = false;
            this.errorMessage = err?.error?.message || 'تعذر تعطيل الجهاز. حاول مرة أخرى.';
          }
        });
      return;
    }

    this.httpClient.post<any>(`${baseUrl2}/payment-device/delete/${this.deviceId}`, {}).subscribe({
      next: (res) => {
        const payload = res?.data ?? {};
        if (payload?.inactivated) {
          this.activeModal.close({ deleted: false, inactivated: true });
          return;
        }
        this.activeModal.close({ deleted: !!payload?.deleted, inactivated: false });
      },
      error: (err: any) => {
        this.isSubmitting = false;
        this.errorMessage = err?.error?.message || 'تعذر حذف الجهاز. قد يكون مرتبطًا بمعاملات سابقة.';
      }
    });
  }
}

