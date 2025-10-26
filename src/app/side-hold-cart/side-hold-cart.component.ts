import {
  Component,
  Input,
  OnInit,
  ChangeDetectorRef,
  DoCheck,
  AfterViewInit,
  ElementRef,
  ViewChild,
  ɵsetAllowDuplicateNgModuleIdsForTest,
} from '@angular/core';
import { ProductsService } from '../services/products.service';
import { PlaceOrderService } from '../services/place-order.service';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { catchError, finalize, Observable, of, tap } from 'rxjs';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute, RouterLink, RouterLinkActive } from '@angular/router';
import { CommonModule, DecimalPipe } from '@angular/common';
import { DatePipe } from '@angular/common';
import { CartItemsModalComponent } from '../cart-items-modal/cart-items-modal.component';

declare var bootstrap: any;

@Component({
  selector: 'app-side-hold-cart',
  imports: [FormsModule, CommonModule],
  providers: [DatePipe],

  templateUrl: './side-hold-cart.component.html',
  styleUrl: './side-hold-cart.component.css'
})
export class SideHoldCartComponent implements OnInit {
  order: any;
  orderId: string | null = null;
  tableNumber: string | null = null;
  FormDataDetails: any;
  address: string = '';
  addressPhone: string = '';
  appliedCoupon: any;
  couponCode: any;
  discountAmount: any;
  additionalNote: any;
  savedNote: any;
  successMessage: any;
  errorMessage: any;
  totalPrice: number = 100;
  isLoading: any;
  currencySymbol = localStorage.getItem('currency_symbol');
  branchData: any = null;

  ngOnInit(): void {
    this.orderId = this.route.snapshot.paramMap.get('id');
    const savedOrders = JSON.parse(localStorage.getItem('savedOrders') || '[]');
    this.order = savedOrders.find((o: any) => o.orderId === this.orderId);
  }

  constructor(private route: ActivatedRoute,
    private productsService: ProductsService,
    private http: HttpClient,
    private modalService: NgbModal,

  ) { }
  selectOrderType(type: string) {
    // this.clearOrderTypeData();
    const typeMapping: { [key: string]: string } = {
      'في المطعم': 'dine-in',
      'خارج المطعم': 'Takeaway',
      توصيل: 'Delivery',
      'طلبات': 'talabat',

    };
    this.order.type = typeMapping[type] || type;

    localStorage.setItem('selectedOrderType', this.order.type);
  }
  setCameFromOnholdCart(): void {
    localStorage.setItem('cameFromOnHoldCart', 'true');
  }

  // clearOrderTypeData() {
  //   // Clear data based on the previously selected order type
  //   switch (this.selectedOrderType) {
  //     case 'dine-in':
  //       // Clear table number and table ID
  //       this.tableNumber = null;
  //       localStorage.removeItem('table_number');
  //       localStorage.removeItem('table_id');
  //       break;

  //     case 'Delivery':
  //       // Clear delivery address, courier, and form data
  //       this.address = '';
  //       this.addressPhone = '';
  //       this.selectedCourier = null;
  //       localStorage.removeItem('form_data');
  //       localStorage.removeItem('selectedCourier');
  //       localStorage.removeItem('address_id');
  //       break;

  //     case 'Takeaway':
  //       // No specific data to clear for Takeaway
  //       break;

  //     default:
  //       break;
  //   }
  // }
  getTotalItemCount(): number {
    return this.order.items.reduce(
      (total: any, item: { quantity: any }) => total + item.quantity,
      0
    );
  }


  saveCart() {
    localStorage.setItem('holdCart', JSON.stringify(this.order.items));
  }

  // loadCart() {
  //   const storedCart = localStorage.getItem('holdCart');
  //   this.order.items = storedCart ? JSON.parse(storedCart) : [];
  // }






}

