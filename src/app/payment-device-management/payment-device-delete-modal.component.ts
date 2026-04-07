import { CommonModule } from '@angular/common';
import { Component, Input } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-payment-device-delete-modal',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './payment-device-delete-modal.component.html'
})
export class PaymentDeviceDeleteModalComponent {
  @Input() deviceName = '';

  constructor(public activeModal: NgbActiveModal) {}

  confirmDelete(): void {
    this.activeModal.close(true);
  }
}

