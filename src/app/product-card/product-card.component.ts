import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';
import { ProductsService } from '../services/products.service';
import { ProductModalComponent } from '../product-modal/product-modal.component';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-product-card',
  imports: [],
  templateUrl: './product-card.component.html',
  styleUrl: './product-card.component.css'
})
export class ProductCardComponent  {

  @Input() item: any;
  @Output() sendToCategories = new EventEmitter(); // bywsl value mn component l component
  category: any;
  products: any[] = [];
  selectedProduct: any | null = null;
  // deleteProduct(id: string) {
  //   // console.log(id)
  //   this.sendToCategories.emit(id) //method byakhod l value de todyha ll parent
  // }
  constructor(private productService: ProductsService, public modalService: NgbModal) { }
 
  // openModal(item: any) : void{
  //   this.selectedProduct = item;
  //   console.log(item);
  // }


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
  
    // console.log(`Modal Size: ${modalSize}`, modalRef.componentInstance.src);
  }
  

}
