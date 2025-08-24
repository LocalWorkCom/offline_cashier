import { Component, OnInit, OnDestroy, Input } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { OrderListDetailsService } from '../services/order-list-details.service';
import { CommonModule, Location } from '@angular/common';
import { Subject } from 'rxjs';
import { finalize, takeUntil } from 'rxjs/operators';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { baseUrl } from '../environment'; 


@Component({
  selector: 'app-order-details',
  templateUrl: './order-details.component.html',
  styleUrls: ['./order-details.component.css'],
  imports: [CommonModule, ShowLoaderUntilPageLoadedDirective],
})
export class OrderDetailsComponent implements OnInit, OnDestroy {
  orderId: any;
  orderDetails: any = {};
  orderSummary: any = {};
  orderItems: any[] = [];
  loading: boolean = true;
  error: string = '';
  paymenMethod!: string;
  currencySymbol!: string;
  @Input() selectedItem: any;
  private destroy$ = new Subject<void>();
  deliveryData: any;
  deliveryFees: any;
  isAllLoading: boolean = true;
  errorMessage: string = '';
  constructor(
    private route: ActivatedRoute,
    private orderListById: OrderListDetailsService,
    private http: HttpClient,
    private location: Location
  ) {}
  ngOnInit(): void {
    this.route.paramMap.subscribe({
      next: (params) => {
        // console.log(params,'params order details')
        this.orderId = params.get('id');
        if (this.orderId) {
          this.fetchOrderDetails();
        }
      },
      error: (err) => {
        this.error = 'Error retrieving order ID from route.';
        // console.error(this.error, err);
      },
    });
  }

  fetchOrderDetails(): void {
    this.loading = true;
    this.error = '';
    this.isAllLoading = false;
    this.orderListById
      .getOrderById(this.orderId)
      .pipe(
        finalize(() => {
          this.isAllLoading = true;
        })
      )
      .subscribe({
        next: (response) => {
          if (response) {
            const order = response.data.orderDetails[0];
            this.currencySymbol = order.currency_symbol;
            this.paymenMethod = order.transactions[0].payment_method;
            this.deliveryData = response.data.orderDetails[0].delivery_data;
            this.deliveryFees = order.order_summary.delivery_fees;

            console.log(response.data, 'test');
            this.orderDetails = order;
            this.orderSummary = order.order_summary;
            this.orderItems = order.order_details;
            if (this.deliveryData?.delivery_name == ' ') {
              this.deliveryData.delivery_name = 'لا يوجد';
            }
            console.log(' ordtterSummary :', this.orderDetails);

            console.log(' orderSummary :', this.orderSummary);
            this.loading = false;
          } else {
            this.error = 'No order details available.';
            this.loading = false;
          }
        },
        error: (error: any) => {
          console.error('Error fetching order details:', error);
          this.error = 'Failed to fetch order details.';
          this.loading = false;
        },
      });
  }
  get isCouponZero(): boolean {
    return Number(this.orderSummary.coupon_value) === 0;
  }
  get hasServiceFees(): boolean {
    return Number(this.orderSummary.service_percentage) > 0;
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
  status_order: any;
  cancelOrde(): void {
    if (!this.orderId) return;
 

   const cancelUrl = `${baseUrl}api/orders/cashier/order-cancel`;

    const token = localStorage.getItem('authToken');

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });
    const body = {
      order_id: this.orderId,
      type: 1, // 1 to delete all the dishes
    };

    this.http.post(cancelUrl, body, { headers }).subscribe({
      next: (response: any) => {
        console.log('Order cancelled successfully:', response);
        this.errorMessage = response.message;
        this.status_order = response.status;
        setTimeout(() => {
          this.errorMessage = '';
        }, 2000);
        this.fetchOrderDetails();
      },
      error: (error) => {
        console.error('Failed to cancel order:', error);
      },
    });
  }
    cancelOrder(): void {
    if (!this.orderId) return;
 

   const cancelUrl = `${baseUrl}api/orders/cashier/request-cancel`;

    const token = localStorage.getItem('authToken');

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });
    const body = {
      order_id: this.orderId,
      type: "full", // 1 to delete all the dishes
      items: this.orderItems,
      reason:"fff",
    };

    this.http.post(cancelUrl, body, { headers }).subscribe({
      next: (response: any) => {
        console.log('Order cancelled successfully:', response);
        this.errorMessage = response.message;
        this.status_order = response.status;
        setTimeout(() => {
          this.errorMessage = '';
        }, 2000);
        this.fetchOrderDetails();
      },
      error: (error) => {
        console.error('Failed to cancel order:', error);
      },
    });
  }
}
