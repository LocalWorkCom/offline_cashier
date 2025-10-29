import { Routes } from '@angular/router';
import { AuthGuard } from './auth.guard';
import { AuthLayoutComponent } from './auth-layout/auth-layout.component';
import { MainLayoutComponent } from './main-layout/main-layout.component';
import { LoginComponent } from './login/login.component';

export const routes: Routes = [
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
      {
        path: 'home',
        loadComponent: () => import('./home/home.component').then(m => m.HomeComponent)
      },
      {
        path: 'orders',
        loadComponent: () => import('./new-orders/new-orders.component').then(m => m.NewOrdersComponent)
        // loadComponent: () => import('./orders/orders.component').then(m => m.OrdersComponent)
      },
      {
        path: 'tables',
        loadComponent: () => import('./tables/tables.component').then(m => m.TablesComponent)
      },
      {
        path: 'pills',
        loadComponent: () => import('./pills/pills.component').then(m => m.PillsComponent)
      },
      {
        path: 'delivery-details',
        loadComponent: () => import('./delivery-details/delivery-details.component').then(m => m.DeliveryDetailsComponent)
      },
      {
        path: 'order-details',
        loadComponent: () => import('./order-details/order-details.component').then(m => m.OrderDetailsComponent),
        data: { renderMode: 'dynamic' },
      },
      {
        path: 'pill-details/:id',
        loadComponent: () => import('./pill-details/pill-details.component').then(m => m.PillDetailsComponent),
      },
      {
        path: 'returned-invoice/:id',
        loadComponent: () => import('./returned-invoice/returned-invoice.component').then(m => m.ReturnedInvoiceComponent),
      },
      {
        path: 'pill-edit/:id',
        loadComponent: () => import('./pill-edit/pill-edit.component').then(m => m.PillEditComponent),
      },
      {
        path: 'settings',
        loadComponent: () => import('./settings/settings.component').then(m => m.SettingsComponent)
      },
      {
        path: 'order-details/:id',
        loadComponent: () => import('./order-details/order-details.component').then(m => m.OrderDetailsComponent),
        data: { renderMode: 'dynamic' },
      },
      {
        path: 'cart/:id',
        loadComponent: () => import('./cart/cart.component').then(m => m.CartComponent)
      },
      {
        path: 'onhold-orders/:id',
        loadComponent: () => import('./onhold-order/onhold-order.component').then(m => m.OnholdOrderComponent)
      },
    ],
  },
  { path: '**', redirectTo: 'home' } // Redirect unknown routes to home
];
