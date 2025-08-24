import { Component } from '@angular/core';
import { CategoriesComponent } from '../categories/categories.component';
import { SideHoldCartComponent } from '../side-hold-cart/side-hold-cart.component';

@Component({
  selector: 'app-onhold-order',
  imports: [SideHoldCartComponent],
  templateUrl: './onhold-order.component.html',
  styleUrl: './onhold-order.component.css'
})
export class OnholdOrderComponent {

}
