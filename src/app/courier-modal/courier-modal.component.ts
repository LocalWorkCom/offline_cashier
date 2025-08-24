import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Component, Input } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { catchError, Observable, throwError } from 'rxjs';
import { PlaceOrderService } from '../services/place-order.service';

@Component({
  selector: 'app-courier-modal',
  imports: [],
  templateUrl: './courier-modal.component.html',
  styleUrl: './courier-modal.component.css'
})
export class CourierModalComponent {
  couriers: any[] = [];
  isLoading = false;
  errorMessage: string | null = null;
  @Input() selectedCourierId: number | null = null;
  @Input() orderId!: number;

  constructor(public activeModal: NgbActiveModal, private placeOrder: PlaceOrderService) { }


  fetchCouriers() {
    this.isLoading = true;
    this.errorMessage = null;

    this.placeOrder.getCouriers().subscribe({
      next: (data) => {
        this.couriers = data.data;
        console.log('Fetched couriers:', data);
        this.isLoading = false;
      },
      error: (err) => {
        this.errorMessage = 'Failed to load couriers. Please try again.';
        console.error('Error fetching couriers:', err);
        this.isLoading = false;
      }
    });
  }



  ngOnInit(): void {
    this.fetchCouriers();
    this.loadSelectedCourier();
  }

  selectedCourier: { id: number, name: string } | null = null;
  // loadSelectedCourier() {
  //   const storedCourier = localStorage.getItem('selectedCourier');
  //   if (storedCourier) {
  //     this.selectedCourier = JSON.parse(storedCourier); // Load selected courier
  //   }
  // }
loadSelectedCourier() {
  const storedCourier = localStorage.getItem(`selectedCourier_${this.orderId}`);
  if (storedCourier) {
    this.selectedCourier = JSON.parse(storedCourier);
    if (this.selectedCourier) {
      this.selectedCourierId = this.selectedCourier.id; 
    }
  }
}


  selectCourier(courierId: number) {
    this.selectedCourierId = courierId;
  }


  // confirmSelection() {
  //   if (this.selectedCourierId) {
  //     const selectedCourier = this.couriers.find(courier => courier.id === this.selectedCourierId);
  //     if (selectedCourier) {
  //       this.activeModal.close({
  //         id: this.selectedCourierId,
  //         name: `${selectedCourier.first_name} ${selectedCourier.last_name}`
  //       });
  //     }
  //   } else {
  //     this.errorMessage = 'Please select a courier.';
  //   }
  // }
  confirmSelection() {
  if (this.selectedCourierId) {
    const selectedCourier = this.couriers.find(courier => courier.id === this.selectedCourierId);
    if (selectedCourier) {
      const courierData = {
        id: this.selectedCourierId,
        name: `${selectedCourier.first_name} ${selectedCourier.last_name}`
      };

      localStorage.setItem(`selectedCourier_${this.orderId}`, JSON.stringify(courierData));
      this.activeModal.close(courierData);
    }
  } else {
    this.errorMessage = 'Please select a courier.';
  }
}

clearSelectedCourier() {
  this.selectedCourier = null;
  localStorage.removeItem(`selectedCourier_${this.orderId}`);
  this.activeModal.close(); // optionally pass null
}

}
