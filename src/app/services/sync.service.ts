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


import { Injectable } from '@angular/core';
import { Subject, timer } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SyncService {

  private retryOrdersSubject = new Subject<void>();
  private retryPillsSubject = new Subject<void>();
  private retryInvoicesSubject = new Subject<void>();

  retryOrders$ = this.retryOrdersSubject.asObservable();
  retryPills$ = this.retryPillsSubject.asObservable();
  retryInvoices$ = this.retryInvoicesSubject.asObservable();

  constructor() {
    // ✅ Auto-run لو عايزة تبدأ تسمع أول ما السيرفس يشتغل
    window.addEventListener('online', () => {
      console.log("🌐 Back online, running all sync functions...");
      this.runAllSyncFunctions();
    });
  }

  // 👇 Helper تضيف delay + retry
  private triggerWithRetry(subject: Subject<void>, retries = 2, delayMs = 10000, startDelay = 5000) {
  let attempt = 0;

  const tryEmit = () => {
    attempt++;
    console.log(`🔁 Retry attempt ${attempt}/${retries}`);

    subject.next(); // 🔥 يبعت إشارة للكومبوننت

    if (attempt < retries) {
      // ⏳ يستنى delay وبعدين يحاول تاني
      timer(delayMs).subscribe(() => tryEmit());
    } else {
      console.log("✅ Finished retries");
    }
  };

  // 👇 أول محاولة تبدأ بعد startDelay
  timer(startDelay).subscribe(() => tryEmit());
}


  // Methods تنادي على الـ Subjects مع retries
  callRetryOrders() {
    console.log("🔄 Trigger retryOrders with retries");
    this.triggerWithRetry(this.retryOrdersSubject, 1, 5000);
  }

  callRetryPills() {
    console.log("💊 Trigger retryPills with retries");
    this.triggerWithRetry(this.retryPillsSubject, 1, 5000);
  }

  callRetryInvoices() {
    console.log("🧾 Trigger retryInvoices with retries");
    this.triggerWithRetry(this.retryInvoicesSubject, 1, 5000);
  }

  // Run all at once (مثلاً لما الإنترنت يرجع)
  runAllSyncFunctions() {
    this.callRetryOrders();
    this.callRetryPills();
    this.callRetryInvoices();
  }
}
