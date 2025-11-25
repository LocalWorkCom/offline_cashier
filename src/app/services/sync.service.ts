// import { Injectable } from '@angular/core';
// import { Subject } from 'rxjs';

// @Injectable({
//   providedIn: 'root'
// })
// export class SyncService {

//   // Subjects لكل نوع من الـ retry
//   private retryOrdersSubject = new Subject<void>();
//   private retryPillsSubject = new Subject<void>();
//   private retryInvoicesSubject = new Subject<void>();

//   // Observables يسمعها الكومبوننتس
//   retryOrders$ = this.retryOrdersSubject.asObservable();
//   retryPills$ = this.retryPillsSubject.asObservable();
//   retryInvoices$ = this.retryInvoicesSubject.asObservable();

//   constructor() {
//     // ✅ Auto-run لو عايزة تبدأ تسمع أول ما السيرفس يشتغل
//     window.addEventListener('online', () => {
//       console.log("🌐 Back online, running all sync functions...");
//       this.runAllSyncFunctions();
//     });
//   }

//   // Methods تنادي على الـ Subjects
//   callRetryOrders() {
//     console.log("🔄 Trigger retryOrders");
//     this.retryOrdersSubject.next();
//   }

//   callRetryPills() {
//     console.log("💊 Trigger retryPills");
//     this.retryPillsSubject.next();
//   }

//   callRetryInvoices() {
//     console.log("🧾 Trigger retryInvoices");
//     this.retryInvoicesSubject.next();
//   }

//   // Run all at once (مثلاً لما الإنترنت يرجع)
//   runAllSyncFunctions() {
//     this.callRetryOrders();
//     this.callRetryPills();
//     // this.callRetryInvoices();
//   }
// }


import { Injectable, OnDestroy } from '@angular/core';
import { Subject, timer } from 'rxjs';
import { IndexeddbService } from './indexeddb.service';
import { PillDetailsService } from './pill-details.service';
import { firstValueFrom } from 'rxjs';
import { PlaceOrderService } from '../services/place-order.service';

@Injectable({
  providedIn: 'root'
})
export class SyncService implements OnDestroy {

  private retryOrdersSubject = new Subject<void>();
  private retryPillsSubject = new Subject<void>();
  private retryInvoicesSubject = new Subject<void>();

  retryOrders$ = this.retryOrdersSubject.asObservable();
  retryPills$ = this.retryPillsSubject.asObservable();
  retryInvoices$ = this.retryInvoicesSubject.asObservable();

  private readonly handleOnline = () => {
    console.log('🌐 Back online, running all sync functions...');
    this.runAllSyncFunctions();
  };

  constructor(
    private dbService: IndexeddbService,
    private orderService: PillDetailsService,
    private plaseOrderService: PlaceOrderService,
  ) {
    window.addEventListener('online', this.handleOnline);

    if (navigator.onLine) {
      // In case the app starts while already online, sync immediately.
      this.runAllSyncFunctions();
    }
  }

  ngOnDestroy(): void {
    window.removeEventListener('online', this.handleOnline);
  }

  // 👇 Helper تضيف delay + retry
//   private triggerWithRetry(subject: Subject<void>, retries = 1, delayMs = 10000, startDelay = 5000) {
//   let attempt = 0;

//   const tryEmit = () => {
//     attempt++;
//     console.log(`🔁 Retry attempt ${attempt}/${retries}`);

//     subject.next(); // 🔥 يبعت إشارة للكومبوننت

//     if (attempt < retries) {
//       // ⏳ يستنى delay وبعدين يحاول تاني
//       timer(delayMs).subscribe(() => tryEmit());
//     } else {
//       console.log("✅ Finished retries");
//     }
//   };

//   // 👇 أول محاولة تبدأ بعد startDelay
//   timer(startDelay).subscribe(() => tryEmit());
// }


  // Methods تنادي على الـ Subjects مع retries
  callRetryOrders() {
    console.log("🔄 Trigger retryOrders with retries");
    // this.triggerWithRetry(this.retryOrdersSubject, 1, 5000);
    this.syncPendingOrders();
  }

  callRetryPills() {
    console.log("💊 Trigger retryPills with retries");
    // this.triggerWithRetry(this.retryPillsSubject, 1, 5000);
  }

  callRetryInvoices() {
    console.log("🧾 Trigger retryInvoices");
    this.retryInvoicesSubject.next();
    // Also directly sync pending invoice updates
    this.syncPendingInvoiceUpdates();
  }

  // Sync pending invoice updates when connection is restored
  async syncPendingInvoiceUpdates(): Promise<void> {
    if (!navigator.onLine) {
      console.log('📴 Offline - skipping invoice sync');
      return;
    }

    try {
      const pendingUpdates = await this.dbService.getPendingInvoiceUpdates();

      if (pendingUpdates.length === 0) {
        console.log('✅ No pending invoice updates to sync');
        return;
      }

      console.log(`🔄 Syncing ${pendingUpdates.length} pending invoice update(s)...`);

      for (const update of pendingUpdates) {
        try {
          // Remove metadata fields before sending to API
          const updateDataForAPI = {
            orderNumber: update.orderNumber,
            paymentStatus: update.paymentStatus,
            trackingStatus: update.trackingStatus,
            cashAmount: update.cashAmount,
            creditAmount: update.creditAmount,
            DeliveredOrNot: update.DeliveredOrNot,
            totalll: update.totalll,
            tip: update.tip,
            referenceNumber: update.referenceNumber
          };

          await new Promise<void>((resolve, reject) => {
            const timeoutPromise = new Promise((_, timeoutReject) =>
              setTimeout(() => timeoutReject(new Error('Request timeout')), 30000)
            );

            Promise.race([
              firstValueFrom(this.orderService.updateInvoiceStatus(
                updateDataForAPI.orderNumber,
                updateDataForAPI.paymentStatus,
                updateDataForAPI.trackingStatus,
                updateDataForAPI.cashAmount,
                updateDataForAPI.creditAmount,
                updateDataForAPI.DeliveredOrNot,
                updateDataForAPI.totalll,
                updateDataForAPI.tip,
                updateDataForAPI.referenceNumber
              )),
              timeoutPromise
            ]).then((response: any) => {
              if (response.status !== false && !response.errorData) {
                // Mark as synced and delete
                this.dbService.markPendingInvoiceUpdateAsSynced(update.id)
                  .then(() => this.dbService.deleteSyncedPendingInvoiceUpdate(update.id))
                  .then(() => {
                    console.log(`✅ Successfully synced invoice update for order ${update.orderNumber || 'N/A'}`);
                    resolve();
                  })
                  .catch(reject);
              } else {
                console.error(`❌ API returned error for invoice update:`, response);
                resolve(); // Continue with next update even if this one failed
              }
            }).catch((err) => {
              console.error(`❌ Error syncing invoice update:`, err);
              resolve(); // Continue with next update even if this one failed
            });
          });
        } catch (err) {
          console.error(`❌ Error processing pending invoice update ${update.id}:`, err);
          // Continue with next update
        }
      }

      console.log('✅ Finished syncing all pending invoice updates');
    } catch (err) {
      console.error('❌ Error in syncPendingInvoiceUpdates:', err);
    }
  }

   // Sync pending orders using raw orderData saved for API
   async syncPendingOrders(): Promise<void> {
    if (!navigator.onLine) {
      console.log('📴 Offline - skipping sync');
      return;
    }

    try {
      const pendingOrders = await this.dbService.getPendingOrders();
      if (pendingOrders.length === 0) {
        console.log('✅ No pending orders to sync');
        return;
      }

      console.log(`🔄 Syncing ${pendingOrders.length} pending order(s)...`);

      for (const pendingOrder of pendingOrders) {
        try {

          // localStorage.setItem('form_data', pendingOrder.formData);
          // let addressId = await this.getAddressId();
          // if (addressId) {
          //   localStorage.setItem('address_id', addressId.toString());
          // }

          console.log('pendingOrder', pendingOrder);
          // Remove metadata fields before sending to API
          const orderDataForAPI = { ...pendingOrder };
          delete orderDataForAPI.type_operation;
          delete orderDataForAPI.savedAt;
          delete orderDataForAPI.isSynced;
          delete orderDataForAPI.id;

          await new Promise<void>((resolve, reject) => {
            const timeoutPromise = new Promise((_, timeoutReject) =>
              setTimeout(() => timeoutReject(new Error('Request timeout')), 30000)
            );

            console.log('orderDataForAPI', orderDataForAPI);

            Promise.race([
              firstValueFrom(this.plaseOrderService.placeOrder_offline(orderDataForAPI)),
              timeoutPromise
            ]).then((response: any) => {
              if (response.status !== false && !response.errorData) {
                // Mark as synced and delete
                this.dbService.markPendingOrderAsSynced(pendingOrder.id)
                  .then(() => this.dbService.deleteSyncedPendingOrder(pendingOrder.id))
                  .then(() => {
                    console.log(`✅ Successfully synced order ${pendingOrder.orderId || 'N/A'}`);
                    resolve();
                  })
                  .catch(reject);
              } else {
                console.error(`❌ API returned error for order:`, response);
                resolve(); // Continue with next order even if this one failed
              }
            }).catch((err) => {
              console.error(`❌ Error syncing order:`, err);
              resolve(); // Continue with next order even if this one failed
            });
          });
        } catch (err) {
          console.error(`❌ Error processing pending order ${pendingOrder.id}:`, err);
          // Continue with next order
        }
      }

      console.log('✅ Finished syncing all pending orders');
    } catch (err) {
      console.error('❌ Error in syncPendingOrders:', err);
    }
  }

  // Run all at once (مثلاً لما الإنترنت يرجع)
  runAllSyncFunctions() {
    this.callRetryOrders();
    this.callRetryPills();
    this.callRetryInvoices();
  }
}
