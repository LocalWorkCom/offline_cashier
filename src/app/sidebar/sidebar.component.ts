import { finalize, switchMap, take, tap } from 'rxjs/operators';
import { Component, OnInit, Inject, PLATFORM_ID, Input } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { CloseBalanceService } from '../services/close-balance.service';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { BalanceService } from '../services/balance.service';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import * as bootstrap from 'bootstrap';
import { HttpClientModule } from '@angular/common/http';
import { from, lastValueFrom, Observable } from 'rxjs';
import { baseUrl } from '../environment'; 

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [
    RouterLink,
    RouterLinkActive,
    CommonModule,
    FormsModule,
    HttpClientModule,
  ],
  templateUrl: './sidebar.component.html',
  styleUrls: ['./sidebar.component.css'],
})
export class SidebarComponent implements OnInit {
  @Input() balance: { cash: number; visa: number; total: number } | null = null;
  @Input() currencySymbol: string = '';
  @Input() title: string = 'رصيد الورديه';
  fullName: string | null = null;
  shiftData: any = null;
  closeCash: number = 0;
  closeVisa: number = 0;
  enteredCash: number | string | null = null;
  errorMessage: string | null = null;
  branch: string | null = null;
  branchData:any;
  currency_Symbol: string | null = null;
  imageUrl: string | null = null;
  showDeficitMessage = false;
  showDeficitMessage2 = false;
  deficitCash = 0;
  deficitVisa = 0;
  deficitMessage = '';
  enteredVisa: number | string | null = null;
  transferAmount: number | null = null;
  transferError: string | null = null;
  transferSuccess: string | null = null;
  isTransferring: boolean = false;
  visaTotal: any ;
  reason: any;
  alertError: any;
  reasonError: any;
  printingData: any;
  currentBalance: {
    cash: number;
    visa: number;
    total: number;
    deficitCash?: number;
    deficitVisa?: number;
  } | null = null;
  constructor(
    private authService: AuthService,
    private router: Router,
    private http: HttpClient,
    private balanceService: BalanceService,
    private closeBalanceService: CloseBalanceService,
    @Inject(PLATFORM_ID) private platformId: Object
  ) {}

  ngOnInit() { 
/*      this.printt(2)
 */      if (isPlatformBrowser(this.platformId)) {
      this.branch = localStorage.getItem('branch') || null;
      this.branchData = JSON.parse(localStorage.getItem('branchData')!) || null;
      this.currency_Symbol = localStorage.getItem('currency_symbol');
      this.imageUrl = localStorage.getItem('imageUrl');
    }
    this.authService.visaTotal$.subscribe((total) => {
      this.visaTotal = total;
    });
    if (isPlatformBrowser(this.platformId)) {
      this.fullName =
        localStorage.getItem('fullName') ||
        sessionStorage.getItem('fullName') ||
        null;
    }

    this.authService.employeeData$.subscribe((employee) => {
      if (employee) {
        this.fullName = `${employee.first_name} ${employee.last_name}`;
      }
    });
    this.authService.branch$.subscribe((branch) => {
      this.branch = branch;
    });

    this.authService.shiftData$.subscribe((shiftData) => {
      this.shiftData = shiftData;
    });

    if (sessionStorage.getItem('balanceoutModalOpen') === 'true') {
      setTimeout(() => this.showBalanceoutModal(), 500);
    }
  }

  async showWelcomeModal() {
    if (!isPlatformBrowser(this.platformId)) return;

    const { Modal } = await import('bootstrap');
    const modalElement = document.getElementById('welcomeModal');

    if (modalElement) {
      const modalInstance = new Modal(modalElement);
      modalInstance.show();
    } else {
      console.error('❌ Welcome modal element not found!');
    }
  }
  fetchCurrentBalance(): void {
    console.log('Fetching current balance...');
    this.closeBalanceService.getCurrentBalance().subscribe({
      next: (response) => {
        this.visaTotal = response.data[1].value;
        console.log('API Response:', this.visaTotal);

        if (response?.status && response.data) {
          console.log('Raw balance data:', response.data);

          // Adjust this based on actual API response structure
          // this.currentBalance = {
          //   cash: response.data.open_cash || response.data[0].value || 0,
          //   visa: response.data.open_visa || response.data[1].value || 0,
          //   total:
          //     (response.data[0].value || 0) + (response.data[1].value || 0),
          //   deficitCash:
          //     response.data.deficit_cash_close ||
          //     response.data.deficit_cash ||
          //     0,
          //   deficitVisa:
          //     response.data.deficit_visa_close ||
          //     response.data.deficit_visa ||
          //     0,
          // };
          this.currentBalance = {
  cash: response.data.open_cash || response.data[0].value || 0,
  visa: response.data.open_visa || response.data[1].value || 0,
  total: (response.data[0].value || 0) + (response.data[1].value || 0),
  deficitCash: response.data.deficit_cash_close ?? 0,
  deficitVisa: response.data.deficit_visa_close ?? 0,
};

          console.log('Processed balance:', this.currentBalance);

          // Set the visa total from the response
          this.visaTotal = response.data[1].value || 0;
        }
      },
      error: (err) => {
        console.error('Error fetching current balance:', err);
      },
    });
  }
  async showBalanceoutModal() {
    if (!isPlatformBrowser(this.platformId)) return;

    const { Modal } = await import('bootstrap');
    const modalElement = document.getElementById('balanceoutModal');

    if (modalElement) {
      await this.fetchCloseBalance();
      this.fetchCurrentBalance();
      const modalInstance = new Modal(modalElement);
      modalInstance.show();

      sessionStorage.setItem('balanceoutModalOpen', 'true');
    }
  }

  fetchCloseBalance(): void {
    const balanceData = this.balanceService.getCurrentBalanceData();
    const balanceId =
      this.authService.getOpenedBalanceId() ||
      this.balanceService.getCurrentBalanceId();

    console.log('🔹 Current balance data:', balanceData);
    console.log('🔹 Using balance ID:', balanceId);

    if (balanceData) {
      this.closeCash = Number(balanceData.open_cash);
      this.closeVisa = Number(balanceData.open_visa);
      this.currency_Symbol =
        balanceData.currency_symbol || this.currency_Symbol;
      this.deficitCash = balanceData.deficit_cash || 0;
      this.deficitVisa = balanceData.deficit_visa || 0;

      if (this.deficitCash !== 0 || this.deficitVisa !== 0) {
        this.showDeficitMessage = true;
        this.buildDeficitMessage();
      }
    } else {
      console.error('No current balance data available');
    }
  }

  private buildDeficitMessage(): void {
    if (this.deficitCash === 0 && this.deficitVisa === 0) {
      this.deficitMessage = 'لا يوجد فارق في الرصيد';
    } else {
      let messages = [];
      if (this.deficitCash !== 0) {
        messages.push(
          `يوجد فارق نقدي بقيمة  نقدي: ${this.deficitCash} ${this.currency_Symbol} في الوردية السابقه`
        );
      }
      if (this.deficitVisa !== 0) {
        messages.push(
          `فارق إلكتروني: ${this.deficitVisa} ${this.currency_Symbol}`
        );
      }
      this.deficitMessage = messages.join(' - ');
    }
  }

  formatTime(time: string | null): string {
    if (!time) return '--:--';

    const [hour, minute] = time.split(':').map(Number);

    if (isNaN(hour) || isNaN(minute)) return '--:--';

    const period = hour < 12 ? 'صباحًا' : 'مساءً';
    const formattedHour = hour % 12 || 12; // Convert 0 to 12 for AM/PM format

    return `${formattedHour}:${minute.toString().padStart(2, '0')} ${period}`;
  }

  onCashInput(event: any): void {
    const value = event.target.value;
    this.enteredCash = value;
  }
  //  onVisaInput(event: any): void {
  //     const value = event.target.value;
  //     this.enteredVisa = value === '' ? null : Number(value);
  //     this.errorMessage = null;
  //   }

  // async validateAndSave(): Promise<void> {
  //     // Validate both inputs
  //     if (this.enteredCash === null || this.enteredCash === '') {
  //       this.errorMessage = "يرجى إدخال المبلغ النقدي الحالي.";
  //       return;
  //     }

  //     // if (this.enteredVisa === null || this.enteredVisa === '') {
  //     //   this.errorMessage = "يرجى إدخال المبلغ الإلكتروني الحالي.";
  //     //   return;
  //     // }

  //     const cashAmount = Number(this.enteredCash);
  //     const visaAmount = Number(this.enteredVisa);

  //     if (isNaN(cashAmount) || cashAmount < 0 || isNaN(visaAmount) || visaAmount < 0) {
  //       this.errorMessage = "المبالغ المدخلة غير صالحة";
  //       return;
  //     }

  //     try {
  //       // Get the opened_balance_id from auth service
  //       const balanceId = this.authService.getOpenedBalanceId();

  //       // Only pass balanceId if it exists
  //       const requestParams = {
  //         cashAmount,
  //         visaAmount,
  //         ...(balanceId && { balanceId }) // Only include if balanceId exists
  //       };

  //       const response = await this.closeBalanceService.submitCloseBalance(
  //         requestParams.cashAmount,
  //         requestParams.visaAmount,
  //         requestParams.balanceId
  //       ).toPromise();

  //       console.log('Close Balance Response:', response);

  //       if (response?.status && response.data) {
  //         // Check for deficit amounts
  //         this.deficitCash = response.data.deficit_cash || 0;
  //         this.deficitVisa = response.data.deficit_visa || 0;

  //         if (this.deficitCash !== 0 || this.deficitVisa !== 0) {
  //           // Show deficit message
  //           this.showDeficitMessage = true;
  //           this.buildDeficitMessage();
  //         } else {
  //           // No deficit, proceed with logout
  //           this.proceedToLogout();
  //         }
  //       } else {
  //         this.errorMessage = response?.message || "فشل في إغلاق الرصيد";
  //       }
  //     } catch (error: any) {
  //       console.error('Error submitting close balance:', error);
  //       this.handleCloseBalanceError(error);
  //     }
  //   }
  // Add this property to your component class
  formSubmitted = false;

  async validateAndSave(logout:boolean=false): Promise<void> {
    // Reset deficit state on new submission
    this.showDeficitMessage = false;
    this.errorMessage = null;

    // Validate cash input
    if (this.enteredCash === null || this.enteredCash === '') {
      this.errorMessage = 'يرجى إدخال المبلغ النقدي الحالي.';
      return;
    }

    const cashAmount = Number(this.enteredCash);
    const visaAmount = this.visaTotal || 0;

    if (isNaN(cashAmount) || cashAmount < 0) {
      this.errorMessage = 'المبلغ النقدي المدخل غير صالح';
      return;
    }

    try {
      const balanceId = this.authService.getOpenedBalanceId();
      console.log('Submitting close balance with:', {
        cashAmount,
        visaAmount,
        balanceId,
      });

      const response = await this.closeBalanceService
        .submitCloseBalance(cashAmount, visaAmount, balanceId ?? undefined)
        .toPromise();

      console.log('Close Balance Response:', response);

      if (response?.status && response.data) {
        // Set form as submitted
        this.formSubmitted = true;

        // Update current balance with new deficit values
        // Note: Adjust this based on actual API response structure
        const deficitCash =
          response.data.deficit_cash_close ; 
        const deficitVisa =
          response.data.deficit_visa_close ;

          console.log( response.data.deficit_cash_close,"alaaaaaa");
          
        if (this.currentBalance) {
          this.currentBalance.deficitCash = deficitCash;
          this.currentBalance.deficitVisa = deficitVisa;
           console.log( this.currentBalance.deficitCash ,"alaaaaaa");
           this.showDeficitMessage2=true
        }

        // Always show deficit message after submission
        this.showDeficitMessage = true;
        this.buildDeficitMessage();

        // Only auto-proceed if no deficit
        if (deficitCash === 0 && deficitVisa === 0) {
          this.proceedToLogout();
        }
      } else {
        this.errorMessage = response?.message || 'فشل في إغلاق الرصيد';
      }
    } catch (error: any) {
      console.error('Error submitting close balance:', error);
      this.handleCloseBalanceError(error);
    }
  }
proceedToLogout(): void {
    // Clear balance data and modal state
    this.balanceService.clearBalanceData();
    sessionStorage.removeItem('balanceoutModalOpen');
    this.hideBalanceoutModal();
    
    // Clear the open balance status
    this.authService.setOpenBalanceStatus(false);
    
    // Perform logout
    this.authService.logout().subscribe({
      next: () => {
        this.router.navigate(['/login']);
      },
      error: (error) => {
        console.error('Logout error:', error);
        this.router.navigate(['/login']);
      },
    });
}
  private hideBalanceoutModal(): void {
    const modalElement = document.getElementById('balanceoutModal');
    if (modalElement) {
      import('bootstrap').then(({ Modal }) => {
        const modalInstance = Modal.getInstance(modalElement);
        if (modalInstance) {
          modalInstance.hide();
        }
      });
    }
  }

  private handleCloseBalanceError(error: any): void {
    if (error?.errors?.length) {
      this.errorMessage = error.errors[0];
    } else if (error?.message) {
      this.errorMessage = error.message;
    } else {
      this.errorMessage = 'حدث خطأ في الاتصال بالخادم';
    }
  }

  async confirmLogout(): Promise<void> {
    if (!isPlatformBrowser(this.platformId)) return;

    const logoutModalElement = document.getElementById('logoutModal');
    if (!logoutModalElement) return;

    const { Modal } = await import('bootstrap');
    const logoutModalInstance =
      Modal.getInstance(logoutModalElement) || new Modal(logoutModalElement);
    logoutModalInstance.hide();

    this.showBalanceoutModal();
  }

  //  async confirmLogout(): Promise<void> {
  //   if (!isPlatformBrowser(this.platformId)) return;

  //   // Hide the logout modal
  //   const logoutModalElement = document.getElementById('logoutModal');
  //   if (logoutModalElement) {
  //     const { Modal } = await import('bootstrap');
  //     const logoutModalInstance = Modal.getInstance(logoutModalElement) || new Modal(logoutModalElement);
  //     logoutModalInstance.hide();
  //   }

  //   // Directly call logout without showing balance modal
  //   this.authService.logout().subscribe({
  //     next: () => {
  //       this.router.navigate(['/login']);
  //     },
  //     error: (error) => {
  //       console.error('Logout error:', error);
  //       this.router.navigate(['/login']);
  //     }
  //   });
  // }

  async openTransferMoneyModal() {
    if (!isPlatformBrowser(this.platformId)) return;

    const { Modal } = await import('bootstrap');
    const modalElement = document.getElementById('transferMoneyModal');

    if (modalElement) {
      this.transferAmount = null;
      this.transferError = null;
      this.reasonError = null;
      this.reason = null;
      this.alertError = null;
      const modalInstance = new Modal(modalElement);
      modalInstance.show();
    }
  }

  async transferMoney() {
    if (!this.transferAmount || this.transferAmount <= 0) {
      this.transferError = 'يرجى إدخال مبلغ    ';
      setTimeout(() => {
        this.transferError = null;
      }, 2000);
      return;
    }
    if (!this.reason || this.reason == '') {
      this.reasonError = 'يرجى إدخال السبب';
      setTimeout(() => {
        this.reasonError = null;
      }, 2000);

      return;
    }
    setTimeout(() => {
      this.transferError = null;
      this.reasonError = null;
    }, 2000);

    this.isTransferring = true;
    this.transferError = null;
    this.reasonError = null;
    this.transferSuccess = null;

    try {
      const branchId = this.authService.getBranchId();
      const cashierMachineId = this.authService.getCashierMachineId();
      const token = this.authService.getToken();

      if (!branchId || !cashierMachineId || !token) {
        throw new Error('بيانات المصادقة غير متوفرة');
      }

      const requestBody = {
        branch_id: branchId,
        cashier_machine_id: cashierMachineId,
        cash_amount: this.transferAmount,
        reason: this.reason,
      };

      const headers = new HttpHeaders({
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
      });

      const response = await lastValueFrom(
        this.http.post<any>(
           `${baseUrl}api/cashier/send-branch-safe`,
          requestBody,
          { headers }
        )
      );

      setTimeout(() => {
        this.transferError = null;
      }, 1000);
      if (response?.status && response.code == 200) {
        console.log(response,"alaa");
        this.transferSuccess = 'تم تحويل المبلغ بنجاح';
        this.alertError = response?.data?.alert[0];
        if(this.alertError == undefined){
          setTimeout(()=>{
          this.CloseTheModalAndClear();  

          },1000)
         
        }
       this.print(response.data.newBranchSafe.id);
      } else {
        this.transferError = response?.message || 'فشل في عملية التحويل';
      }
      console.log('Transfer response:', response);
      this.transferError = response?.errorData?.reason[0] || response?.message;
      if(this.transferError == "طلب صحيح"){
        this.transferError=""
      }
      console.log(this.transferError, 'aaaaaaaaaaaaaaaa');
    } catch (error: any) {
      console.error('Transfer error:', error);
      this.transferError =
        error.error?.message || error.message || 'حدث خطأ أثناء التحويل';
    } finally {
      this.isTransferring = false;
    }
  }
  closeTransferModal() {
    const modalElement = document.getElementById('transferMoneyModal');
    if (modalElement) {
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      if (modalInstance) {
        modalInstance.hide();
      }
    }
  }

  CloseTheModalAndClear() {
    this.closeTransferModal();
    // Clear the amount after successful transfer
    this.transferAmount = null;
    this.transferSuccess = null;
    this.reason = null;
    this.alertError = null;
    this.reasonError = null;
  }
  setLog(){
    this.proceedToLogout();
  }
//   print(id:number){
// console.log(id);
// return this.balanceService.PrintBalance(id).pipe
// (   finalize(() => {
//     })).subscribe({
//   next:(res)=>{console.log(res.data , "balanceprint");
//     this.printingData=res.data 
//     if(this.printingData)
//       this.ss() 
  
     
//   },
//   error:(err)=>{console.log(err);
//   },
  
//   })

//   }
TotalPriceOFPrint:number=0;
waitForImagesInSection(selector: string): Promise<void> {
  return new Promise((resolve) => {
    const section = document.querySelector(selector);
    if (!section) return resolve();

    const images = Array.from(section.querySelectorAll('img'));
    if (images.length === 0) return resolve();

    let loadedCount = 0;
    const checkAllLoaded = () => {
      loadedCount++;
      if (loadedCount === images.length) {
        resolve();
      }
    };

    images.forEach(img => {
      if (img.complete) {
        checkAllLoaded();
      } else {
        img.addEventListener('load', checkAllLoaded);
        img.addEventListener('error', checkAllLoaded); // Handle broken images
      }
    });
  });
}
printTime:any
print(id: number): void {
  this.balanceService.PrintBalance(id).pipe(
    tap((res) => {
      if (!res?.data) {
        throw new Error('No data received for printing');
      }
      this.printingData = res.data;
       this.printTime = new Date().toLocaleString();
    }),
    switchMap(() => from(this.waitForRender('#print-section'))),
    switchMap(() => from(this.waitForImagesInSection('#print-section'))),
    take(1)
  ).subscribe({
    next: () => this.executePrint(),
    error: (err) => console.error('Print error:', err)
  });
}

// print(id: number): void {
//   this.balanceService.PrintBalance(id).pipe(
//     tap((res) => {
//       console.log('fatema',res);
      
//       if (!res?.data) {
//         throw new Error('No data received for printing');
//       }
//       this.printingData = res.data;      
//     }), 
//     switchMap(() => { 
//       return this.waitForRender('#print-section') && this.waitForRender('#print-section img');;
//     }),
//     take(1)  
//   ).subscribe({
//     next: () => this.executePrint(),
//     error: (err) => console.error('Print error:', err)
//   });
// }
private waitForRender(selector: string): Observable<Element> {
  return new Observable<Element>(observer => {
    const element = document.querySelector(selector);
    
    if (element) {
      observer.next(element);
      observer.complete();
      return;
    }

    const mutationObserver = new MutationObserver(() => {
      const el = document.querySelector(selector);
      if (el) {
        observer.next(el);
        observer.complete();
        mutationObserver.disconnect();
      }
    });

    mutationObserver.observe(document.body, {
      childList: true,
      subtree: true
    });

    return () => mutationObserver.disconnect();
  });
}
  executePrint(){
      this.CloseTheModalAndClear()
      this.closeModal()

  const printContents = document.getElementById('print-section')?.innerHTML;     
  if (!printContents) return;

  const originalContents = document.body.innerHTML;

  document.body.innerHTML = printContents;

  window.print();

  document.body.innerHTML = originalContents;

 location.reload(); 
  }
  printt(id: number): void {
  this.balanceService.PrintBalance(id).subscribe({
    next: (res) => {console.log(res,"aaaaaaaaaaaaaaaaaaaa") 
      this.printingData=res.data
      this.printingData.categories.forEach((element:any) => {
        console.log(element.price);
        this.TotalPriceOFPrint += element.price
        
      });
      console.log(this.TotalPriceOFPrint); this.printTime = new Date().toLocaleString();
      
    },
    error: (err) => console.error('Print error:', err)
  });
} 
  closeModal(): void {
    const modal = document.getElementById('transferMoneyModal');
    if (modal) {
      const backdrop = document.querySelector('.modal-backdrop');
      if (backdrop) {
        backdrop.remove();
      }
      modal.classList.remove('show');
      modal.style.display = 'none';
      document.body.classList.remove('modal-open');
    }
  }

}
