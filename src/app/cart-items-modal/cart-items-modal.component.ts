import { Component, Input } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-cart-items-modal',
  imports: [],
  templateUrl: './cart-items-modal.component.html',
  styleUrl: './cart-items-modal.component.css'
})
export class CartItemsModalComponent {
  @Input() cartItems: any[] = [];
  @Input() updateParentCart!: (updatedCart: any[]) => void;

  totalNew: number = 0; // Store total price separately

  constructor(public activeModal: NgbActiveModal) {}

  getItemTotal(item: any): number {
    return item.quantity * item.dish.price;
  }

  increaseQuantity(index: number) {
    this.cartItems[index].quantity++;
    this.syncWithParent();
  }

  decreaseQuantity(index: number) {
    if (this.cartItems[index].quantity > 1) {
      this.cartItems[index].quantity--;
    } else {
      this.cartItems.splice(index, 1); // Remove item if quantity reaches 0
    }
    this.syncWithParent();
  }

  syncWithParent() {
    this.updateParentCart(this.cartItems);
  }

  closeModal() {
    this.activeModal.close();
  }
}