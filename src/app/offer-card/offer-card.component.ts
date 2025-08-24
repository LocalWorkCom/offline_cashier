import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';
import { ProductsService } from '../services/products.service';
import { ProductModalComponent } from '../product-modal/product-modal.component';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
@Component({
  selector: 'app-offer-card',
  imports: [],
  templateUrl: './offer-card.component.html',
  styleUrl: './offer-card.component.css'
})
export class OfferCardComponent {
  @Input() offer: any;
  @Output() sendToCategories = new EventEmitter(); 


  constructor(private productService: ProductsService, public modalService: NgbModal) { }

    openModal(item: any): void {
      this.productService.setProduct(item);
      let processedData;
      if (Array.isArray(item)) {
        processedData = item
          .filter((d) => d?.dish)
          .map((d) => ({
            ...d.dish,
            sizes: Array.isArray(d.sizes) ? d.sizes : [],
            addon_categories: Array.isArray(d.addon_categories) ? d.addon_categories : [],
          }));
      } else {
        processedData = {
          ...item.dish,
          sizes: Array.isArray(item.sizes) ? item.sizes : [],
          addon_categories: Array.isArray(item.addon_categories) ? item.addon_categories : [],
        };
      }
    
      const hasExtraData = Array.isArray(processedData)
        ? processedData.some((d) => d.sizes.length > 0 || d.addon_categories.length > 0)
        : processedData.sizes.length > 0 || processedData.addon_categories.length > 0;
    
      const modalSize = hasExtraData ? 'lg' : 'md'; // 'md' is default, change as needed
    
      const modalRef = this.modalService.open(ProductModalComponent, {
        size: modalSize,
      });
    
      modalRef.componentInstance.src = processedData;
    
    }
}
