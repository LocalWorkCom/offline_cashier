import { CommonModule } from '@angular/common';
import { Component, Input, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

export interface PaymentDeviceFormResult {
  name: string;
  ip: string;
  balance: number;
  status: 'active' | 'inactive';
}

@Component({
  selector: 'app-payment-device-form-modal',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './payment-device-form-modal.component.html',
  styleUrl: './payment-device-form-modal.component.css'
})
export class PaymentDeviceFormModalComponent implements OnInit {
  @Input() mode: 'create' | 'edit' = 'create';
  @Input() title = 'إضافة جهاز دفع';
  @Input() device?: PaymentDeviceFormResult;

  form: PaymentDeviceFormResult = {
    name: '',
    ip: '',
    balance: 0,
    status: 'active'
  };

  constructor(public activeModal: NgbActiveModal) {}

  ngOnInit(): void {
    if (this.device) {
      this.form = { ...this.device };
    }
  }

  submit(): void {
    if (!this.form.name.trim() || !this.form.ip.trim()) {
      return;
    }

    this.activeModal.close(this.form);
  }
}

