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

  constructor(public activeModal: NgbActiveModal, private httpClient: HttpClient) {}

  confirmDelete(): void {
    if (!this.deviceId) {
      return;
    }

    this.httpClient.post(`${baseUrl2}/payment-device/delete/${this.deviceId}`, {}).subscribe({
      next: () => {
        this.activeModal.close(true);
      },
      error: () => {
        this.activeModal.dismiss();
      }
    });
  }
}

