import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';
import { ProductsService } from '../services/products.service';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-offer-modal',
  imports: [],
  templateUrl: './offer-modal.component.html',
  styleUrl: './offer-modal.component.css'
})
export class OfferModalComponent {
 @Input() item: any;
  offer: any;
  selectedProduct: any;
  @Input() src: any; 
  selectedSize: any = null;
  selectedAddons: any[] = [];
  quantity: number = 1;
  finalPrice: number = 0;
  constructor(public activeModal: NgbActiveModal,private productService: ProductsService) { }


  ngOnInit(): void {
    this.productService.product$.subscribe(product => {
      this.selectedProduct = product;
      console.log(this.selectedProduct);
    });
    this.finalPrice = this.selectedProduct?.price || 0;

  }
  selectSize(size: any): void {
    this.selectedSize = size;
    this.updatePrice();
  }

  toggleAddon(addon: any, event: any): void {
    if (event.target.checked) {
      this.selectedAddons.push(addon);
    } else {
      this.selectedAddons = this.selectedAddons.filter(a => a.id !== addon.id);
    }
    this.updatePrice();
  }

  increaseQuantity(): void {
    this.quantity++;
    this.updatePrice();
  }

  decreaseQuantity(): void {
    if (this.quantity > 1) {
      this.quantity--;
      this.updatePrice();
    }
  }

  updatePrice(): void {
    let basePrice = this.selectedProduct?.price || 0;
    if (this.selectedSize) {
      basePrice = this.selectedSize.price;
    }
    const addonPrice = this.selectedAddons.reduce((sum, addon) => sum + addon.price, 0);
    this.finalPrice = (basePrice + addonPrice) * this.quantity;
  }


  addToCart(): void {
    console.log("🚀 addToCart() called with:", {
      product: this.selectedProduct,
      selectedSize: this.selectedSize,
      selectedAddons: this.selectedAddons,
      quantity: this.quantity,
      finalPrice: this.finalPrice
    });
  
    const productToAdd = {
      id: this.selectedProduct.id,
      name: this.selectedProduct.name,
      image: this.selectedProduct.image,
      description: this.selectedProduct.description,
      selectedSize: this.selectedSize,
      selectedAddons: this.selectedAddons,
      quantity: this.quantity,
      price: this.finalPrice,
    };
  
    this.productService.addToCart(productToAdd);
    this.activeModal.dismiss(); // Close modal
  }
  
}
