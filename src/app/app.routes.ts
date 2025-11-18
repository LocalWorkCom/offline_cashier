import { Routes } from '@angular/router';
import { OrdersComponent } from './orders/orders.component';
import { NewOrdersComponent } from './new-orders/new-orders.component'
import { HomeComponent } from './home/home.component';
import { LoginComponent } from './login/login.component';
import { TablesComponent } from './tables/tables.component';
import { PillsComponent } from './pills/pills.component';
import { DeliveryDetailsComponent } from './delivery-details/delivery-details.component';
import { OrderDetailsComponent } from './order-details/order-details.component';
import { AuthLayoutComponent } from './auth-layout/auth-layout.component';
import { MainLayoutComponent } from './main-layout/main-layout.component';
import { PillDetailsComponent } from './pill-details/pill-details.component';
import { SettingsComponent } from './settings/settings.component';
import { AuthGuard } from './auth.guard';
import { CartComponent } from './cart/cart.component';
import { PillEditComponent } from './pill-edit/pill-edit.component';
import { OnholdOrderComponent } from './onhold-order/onhold-order.component';
import { ReturnedInvoiceComponent } from './returned-invoice/returned-invoice.component';
import { TestComponent } from './testWebSocket/test/test.component';

export const routes: Routes = [
  {
    path:"tessttt",
    component:TestComponent
  },
  {
    path: '',
    component: AuthLayoutComponent,
    children: [
      { path: 'login', component: LoginComponent },
      { path: '', pathMatch: 'full', canActivate: [AuthGuard], component: LoginComponent }
    ],
  },
  {
    path: '',
    component: MainLayoutComponent,
    canActivate: [AuthGuard],
    canActivateChild: [AuthGuard],
    children: [
      { path: 'home', component: HomeComponent },
      // { path: 'orders', component: OrdersComponent },
      { path: 'orders', component: NewOrdersComponent },
      { path: 'tables', component: TablesComponent },
      { path: 'pills', component: PillsComponent },
      { path: 'delivery-details', component: DeliveryDetailsComponent },
      {
        path: 'order-details',
        component: OrderDetailsComponent,
        data: { renderMode: 'dynamic' },
      },
      {
        path: 'pill-details/:id',
        component: PillDetailsComponent,
      },
      {
        path: 'returned-invoice/:id',
        component: ReturnedInvoiceComponent,
      },
      {
        path: 'pill-edit/:id',
        component: PillEditComponent,
      },
      { path: 'settings', component: SettingsComponent },
      {
        path: 'order-details/:id',
        component: OrderDetailsComponent,
        data: { renderMode: 'dynamic' },
      },
      { path: 'cart/:id', component: CartComponent },
      { path: 'onhold-orders/:id', component: OnholdOrderComponent },
    ],
  },
  { path: '**', redirectTo: 'home' } // Redirect unknown routes to home
];
