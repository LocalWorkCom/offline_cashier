import { Component, OnInit, OnDestroy, Input } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { OrderListDetailsService } from '../services/order-list-details.service';
import { CommonModule, Location } from '@angular/common';
import { Subject } from 'rxjs';
import { finalize, takeUntil } from 'rxjs/operators';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { baseUrl } from '../environment';
import { IndexeddbService } from '../services/indexeddb.service';


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
    private location: Location,
    private dbService: IndexeddbService
  ) {}
  ngOnInit(): void {
    this.route.paramMap.subscribe({
      next: (params) => {
        // console.log(params,'params order details')
        this.orderId = params.get('id');
        if (this.orderId) {
          this.fetchOrderDetails();
            // start dalia
          //  this.searchOrderInIndexedDB();
           //end dalia
        }
      },
      error: (err) => {
        this.error = 'Error retrieving order ID from route.';
        // console.error(this.error, err);
      },
    });
  }


  // start dalia
  // Search for order in IndexedDB by ID
  searchOrderInIndexedDB(): void {
    this.loading = true;
    this.error = '';
    // Convert orderId to number
    const numericOrderId = parseInt(this.orderId, 10);
    if (isNaN(numericOrderId)) {
      this.error = 'Invalid order ID';
      this.loading = false;
      return;
    }
    this.dbService.getOrderById(numericOrderId).then(order => {
      if (order) {
        console.log('Order found in IndexedDB:', order);
        this.displayOrderDetails(order);
      } else {
        console.log('Order not found in IndexedDB, fetching from API');
        // this.fetchOrderDetailsFromAPI();
        this.fetchOrderDetails();
      }
    }).catch(err => {
      console.error('Error searching order in IndexedDB:', err);
      this.fetchOrderDetailsFromAPI();
    });
  }

  // Display order details from IndexedDB
  private displayOrderDetails(order: any): void {


    try {
      // Extract order details
      this.currencySymbol = order.details_order.currency_symbol || 'ج.م';

      this.paymenMethod =  order.details_order.transactions[0].payment_method;;

      this.deliveryData = order.details_order?.delivery_data || "";
      this.deliveryFees = order.details_order.order_summary?.delivery_fees ||
                         order.details_order.order_summary?.delivery_fees || 0;

      // Set the main order details
      this.orderDetails = order.details_order ;
      this.orderSummary = order.details_order?.order_summary || order.details_order.order_summary || {};
      this.orderItems = order.details_order?.order_details;

      console.log("orderitems",this.orderItems);

      // Fix delivery name if empty
      if (this.deliveryData?.delivery_name === ' ' || !this.deliveryData?.delivery_name) {
        this.deliveryData.delivery_name = "test";
      }

      console.log('Order details from IndexedDB:', this.orderDetails);
      console.log('Order items from IndexedDB:', this.orderItems);
      console.log('Order summary from IndexedDB:', this.orderSummary);

      this.loading = false;

    } catch (error) {
      console.error('Error processing order data from IndexedDB:', error);
      this.error = 'Error displaying order details from storage';
      this.loading = false;
    }
  }

   fetchOrderDetailsFromAPI(): void {
    this.loading = true;
    this.error = '';

    this.orderListById.getOrderById(this.orderId)
      .pipe(
        finalize(() => {
          this.isAllLoading = true;
        }),
        takeUntil(this.destroy$)
      )
      .subscribe({
        next: (response) => {
          if (response && response.data) {
            const order = response.data.orderDetails[0];
            this.processOrderData(order);

            // Save to IndexedDB for future access
            this.saveOrderToIndexedDB(order);
          } else {
            this.error = 'No order details available.';
            this.loading = false;
          }
        },
        error: (error: any) => {
          console.error('Error fetching order details from API:', error);
          this.error = 'Failed to fetch order details.';
          this.loading = false;
        },
      });
  }

  // Process order data from API
  private processOrderData(order: any): void {
    this.currencySymbol = order.currency_symbol;
    this.paymenMethod = order.transactions?.[0]?.payment_method || 'Unknown';
    this.deliveryData = order.delivery_data;
    this.deliveryFees = order.order_summary?.delivery_fees || 0;

    this.orderDetails = order;
    this.orderSummary = order.order_summary || {};
    this.orderItems = order.order_details || [];

    if (this.deliveryData?.delivery_name === ' ') {
      this.deliveryData.delivery_name = 'لا يوجد';
    }

    this.loading = false;
  }

  // Save order to IndexedDB
  private saveOrderToIndexedDB(order: any): void {
    // Prepare the order data in the same format as stored in IndexedDB
    const orderToSave = {
      ...order,
      order_items: order.order_details || [], // Store items in order_items field
      total_price: order.order_summary?.total || 0,
      currency_symbol: order.currency_symbol,
      savedAt: new Date().toISOString(),
      isSynced: true
    };

    this.dbService.saveOrder(orderToSave).then(() => {
      console.log('Order saved to IndexedDB:', order.order_id);
    }).catch(err => {
      console.error('Error saving order to IndexedDB:', err);
    });
  }

  //end dalia

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
