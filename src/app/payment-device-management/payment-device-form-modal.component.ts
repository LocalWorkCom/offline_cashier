import { CommonModule } from '@angular/common';
import { Component, Input, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { baseUrl2 } from '../environment';

export interface PaymentDeviceFormResult {
  id?: number;
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

  isSubmitting = false;

  constructor(public activeModal: NgbActiveModal, private httpClient: HttpClient) {}

  ngOnInit(): void {
    if (this.device) {
      this.form = { ...this.device };
    }
  }

  submit(): void {
    if (!this.form.name.trim() || !this.form.ip.trim()) {
      return;
    }

    if (this.mode === 'edit') {
      if (!this.device?.id) {
        return;
      }

      this.isSubmitting = true;
      this.httpClient
        .post(`${baseUrl2}/payment-device/update/${this.device.id}`, {
          device_name: this.form.name.trim(),
          IP: this.form.ip.trim(),
          // Balance: Number(this.form.balance) || 0,
          status: this.form.status
        })
        .subscribe({
          next: () => {
            this.activeModal.close(this.form);
          },
          error: () => {
            this.isSubmitting = false;
          }
        });
      return;
    }

    this.isSubmitting = true;
    this.httpClient
      .post(`${baseUrl2}/payment-device/store`, {
        device_name: this.form.name.trim(),
        IP: this.form.ip.trim(),
        Balance: Number(this.form.balance) || 0,
        status: this.form.status
      })
      .subscribe({
        next: () => {
          this.activeModal.close(this.form);
        },
        error: () => {
          this.isSubmitting = false;
        }
      });
  }
}

