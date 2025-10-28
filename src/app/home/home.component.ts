import { Component, OnInit, Inject, PLATFORM_ID, OnDestroy, HostListener, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
// import { CategoriesComponent } from "../categories/categories.component";
import { SideDetailsComponent } from "../side-details/side-details.component";
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { BalanceService } from '../services/balance.service';
import { ModalStateService } from '../services/modal-state.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { TotalsCardComponent } from "../totals-card/totals-card.component";
import { NewcategoriesComponent } from '../newcategories/newcategories.component';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    // CategoriesComponent,
    NewcategoriesComponent,
    SideDetailsComponent,
    TotalsCardComponent
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class HomeComponent implements OnInit, OnDestroy {
  cashInputDisabled = false;
  showModal: boolean = false;
  enteredCash: number | string | null = null;
  errorMessage: string | null = null;
  fullName: string | null = null;
  shiftData: any = null;
  openCash!: number;
  openVisa!: number;
  branch: string | null = null;
  currency_Symbol: string | null = null;
  empScheduleId: string | null = null;
  shiftStart: string | null = null;
  // cashierMachineId: string = "1";
  cashierMachineId: string | null = null;
  imageUrl: string | null = null;
  visaBalance: number | null = null;
  apiFieldErrors: any = null;
  showDeficitMessage = false;
  deficitCash = 0;
  deficitVisa = 0;
  deficitMessage = '';
  visaTotal: number = 0;

  private readonly BALANCE_OPENED_KEY = 'isBalanceOpened';
  enteredVisa: number | string | null | any = null;
  constructor(
    public modalStateService: ModalStateService,
    private authService: AuthService,
    private router: Router,
    private balanceService: BalanceService,
    private cdr: ChangeDetectorRef,
    @Inject(PLATFORM_ID) private platformId: Object
  ) { }

  ngOnInit() {
    console.log('this.platformId', this.platformId);
    if (isPlatformBrowser(this.platformId)) {
      this.initializeUserData();

      // First check localStorage directly
      const isBalanceOpened = localStorage.getItem('isBalanceOpened') === 'true' ||
        localStorage.getItem('is_open_balance') === 'true';

      // Then subscribe to auth service
      this.authService.isOpenBalance$.subscribe(isOpen => {
        console.log('Balance status from service:', isOpen);
        console.log('Initial balance check:', {
          localStorage: {
            isBalanceOpened: localStorage.getItem('isBalanceOpened'),
            is_open_balance: localStorage.getItem('is_open_balance')
          },
          authService: this.authService.getOpenBalanceStatus()
        });
        //
        if (!isOpen && !isBalanceOpened) {
        console.log('Showing balance modal');
        this.showModal = true;
        this.modalStateService.setModalOpen(true);
        setTimeout(() => this.showWelcomeModal(), 10);
        } else {
          console.log('Balance already opened, hiding modal');
          localStorage.setItem('isBalanceOpened', 'true');
          localStorage.setItem('is_open_balance', 'true');
          this.hideModal(true);
        }
      });

      // Immediate check
      if (!isBalanceOpened) {
        this.showModal = true;
        this.modalStateService.setModalOpen(true);
        setTimeout(() => this.showWelcomeModal(), 500);
      }
    }
    this.authService.visaTotal$.subscribe(total => {
      this.visaTotal = total;
    });
  }

  @HostListener('window:popstate', ['$event'])
  onPopState(event: any) {
    if (this.modalStateService.isModalOpen()) {
      setTimeout(() => this.showWelcomeModal(), 100);
    }
  }

  ngOnDestroy(): void {
    if (isPlatformBrowser(this.platformId)) {
      // Only clean up if we're not keeping the modal open
      if (!this.modalStateService.isModalOpen()) {
        this.removeModalBackdrop();
      }
    }
  }

  hideModal(forceClose = false): void {
    if (forceClose) {
      this.showModal = false;
      this.modalStateService.setModalOpen(false);
      this.hideBootstrapModal();
      this.removeModalBackdrop();
    }
  }

  formatTime(time: string | null): string {
    if (!time) return '--:--';
    const [hour, minute] = time.split(':').map(Number);
    if (isNaN(hour) || isNaN(minute)) return '--:--';
    const period = hour < 12 ? 'صباحًا' : 'مساءً';
    const formattedHour = hour % 12 || 12;
    return `${formattedHour}:${minute.toString().padStart(2, '0')} ${period}`;
  }

  currentBalance: {
    cash: number;
    visa: number;
    total: number;
  } | null = null;

  // fetchCurrentBalance(): void {
  //   this.balanceService.getCurrentBalance().subscribe({
  //     next: (response) => {
  //       console.log('fetchCurrentBalance response:', response);
  //       this.visaTotal = response.data[1].value
  //       console.log('API Response:', response); // Debug log
  //       if (response?.status && response.data) {
  //         console.log('Raw balance data:', response.data); // Debug log
  //         const balanceData = {
  //           cash: 0,
  //           visa: 0,
  //           total: 0
  //         };

  //         response.data.forEach((item: any) => {
  //           if (item.name === 'cash') balanceData.cash = item.value;
  //           if (item.name === 'visa') balanceData.visa = item.value;
  //           if (item.name === 'total') balanceData.total = item.value;
  //         });

  //         console.log('Processed balance:', balanceData); // Debug log
  //         this.currentBalance = balanceData;
  //       }
  //     },
  //     error: (err) => {
  //       console.error('Failed to fetch balance:', err);
  //       // Show user-friendly message
  //       this.errorMessage = 'Failed to load balance information';
  //     }
  //   });
  // }
  // async showWelcomeModal() {
  //   if (!isPlatformBrowser(this.platformId)) return;

  //   console.log('Showing modal...'); // Debug log
  //   if (!this.showModal) {
  //     this.removeModalBackdrop();
  //     return;
  //   }

  //   const { Modal } = await import('bootstrap');
  //   const modalElement = document.getElementById('welcomeModal');
  //   if (modalElement) {
  //     console.log('Modal element found, fetching data...'); // Debug log
  //     // this.fetchBalanceInfo();
  //     this.fetchCurrentBalance(); // Make sure this line exists

  //     const modalInstance = new Modal(modalElement, {
  //       backdrop: 'static',
  //       keyboard: false
  //     });
  //     modalInstance.show();

  //     modalElement.addEventListener('hidden.bs.modal', () => {
  //       this.onModalHidden();
  //     });
  //   }
  // }

  // fetchBalanceInfo() {
  //   this.balanceService.fetchBalanceInfo().subscribe({
  //     next: (response) => {
  //       this.openCash = Number(response.data.open_cash || 0);
  //       this.openVisa = Number(response.data.open_visa || 0);
  //       this.currency_Symbol = response.data.currency_symbol || this.currency_Symbol;
  //       this.errorMessage = null;
  //       this.cashInputDisabled = false;
  //     },
  //     error: (err) => {
  //       console.error('Error fetching balance info:', err);
  //     }
  //   });
  // }

  async showWelcomeModal() {
  if (!isPlatformBrowser(this.platformId)) return;

  console.log('Showing modal...');
  if (!this.showModal) {
    this.removeModalBackdrop();
    return;
  }

  const { Modal } = await import('bootstrap');
  const modalElement = document.getElementById('welcomeModal');
  if (modalElement) {
    console.log('Modal element found, fetching data...');

    // ✅ استدعي الـ API الأول
    await this.fetchCurrentBalance();

    // بعد ما البيانات تتجاب، اعرض المودال
    const modalInstance = new Modal(modalElement, {
      backdrop: 'static',
      keyboard: false
    });
    modalInstance.show();

    modalElement.addEventListener('hidden.bs.modal', () => {
      this.onModalHidden();
    });
  }
}

fetchCurrentBalance(): Promise<void> {
  return new Promise((resolve, reject) => {
    this.balanceService.getCurrentBalance().subscribe({
      next: (response) => {
        console.log('fetchCurrentBalance response:', response);
        this.visaTotal = response.data[1].value;

        if (response?.status && response.data) {
          const balanceData = { cash: 0, visa: 0, total: 0 };
          response.data.forEach((item: any) => {
            if (item.name === 'cash') balanceData.cash = item.value;
            if (item.name === 'visa') balanceData.visa = item.value;
            if (item.name === 'total') balanceData.total = item.value;
          });
          this.currentBalance = balanceData;
        }
        resolve();
      },
      error: (err) => {
        console.error('Failed to fetch balance:', err);
        this.errorMessage = 'Failed to load balance information';
        reject(err);
      }
    });
  });
}


  onModalHidden(): void {
    if (!localStorage.getItem(this.BALANCE_OPENED_KEY)) {
      this.modalStateService.setModalOpen(true);
    }
  }

  private initializeUserData(): void {
    this.fullName = localStorage.getItem('fullName') || sessionStorage.getItem('fullName') || null;
    this.branch = localStorage.getItem('branch');
    this.currency_Symbol = localStorage.getItem('currency_symbol');
    this.imageUrl = localStorage.getItem('imageUrl');

    this.authService.visaBalance$.subscribe(balance => {
      this.visaBalance = balance;
    });
    this.authService.employeeData$.subscribe(employee => {
      if (employee) {
        this.fullName = `${employee.first_name} ${employee.last_name}`;
        this.cashierMachineId = employee.cashier_machine_id || null;
      }
    });

    this.authService.employeeData$.subscribe(employee => {
      if (employee) {
        this.fullName = `${employee.first_name} ${employee.last_name}`;
      }
    });

    this.authService.branch$.subscribe(branch => {
      this.branch = branch;
    });

    this.authService.scheduleId$.subscribe(scheduleId => {
      this.empScheduleId = scheduleId?.toString() || null;
    });

    this.authService.shiftData$.subscribe(shiftData => {
      this.shiftData = shiftData;
      this.shiftStart = shiftData?.shift_start || null;
    });
  }

  fetchOpenBalance(): void {
    this.balanceService.submitOpeningBalance(0, 0).subscribe({
      next: (response: any) => {
        this.apiFieldErrors = null;
        if (response?.status && response.data) {
          this.openCash = Number(response.data.open_cash);
          this.openVisa = Number(response.data.open_visa);
          this.currency_Symbol = response.data.currency_symbol || this.currency_Symbol;
          this.errorMessage = null;
          this.cashInputDisabled = false;
          this.modalStateService.setModalOpen(true);
        } else {
          this.handleInvalidResponse(response);
        }
      },
      error: (error: any) => {
        this.handleErrorResponse(error);
      }
    });
  }

  private handleInvalidResponse(response: any): void {
    this.apiFieldErrors = response?.errorData || null;
    const errorMessage = Array.isArray(response?.errorData?.error)
      ? response.errorData.error[0]
      : null;

    if (errorMessage?.includes('تم فتح الوردية')) {
      this.handleAlreadyOpenedShift();
    } else {
      this.modalStateService.setModalOpen(true);
    }
  }

  private handleErrorResponse(error: any): void {
    this.apiFieldErrors = error?.error?.errorData || null;
    this.errorMessage = Array.isArray(error?.error?.errorData?.error)
      ? error.error.errorData.error[0]
      : 'حدث خطأ في النظام';

    if (this.errorMessage?.includes('تم فتح الوردية')) {
      this.handleAlreadyOpenedShift();
    } else {
      this.modalStateService.setModalOpen(true);
    }
  }

  private handleAlreadyOpenedShift(): void {
    const existingBalanceData = this.balanceService.getCurrentBalanceData();
    if (existingBalanceData) {
      this.balanceService.setBalanceData(existingBalanceData);
    }
    localStorage.setItem(this.BALANCE_OPENED_KEY, 'true');
    this.hideModal(true);
  }

  hideBootstrapModal(): void {
    const modalElement = document.getElementById('welcomeModal');
    if (modalElement) {
      import('bootstrap').then(({ Modal }) => {
        const modalInstance = Modal.getInstance(modalElement);
        if (modalInstance) {
          modalInstance.hide();
        }
      });
    }
  }

  removeModalBackdrop(): void {
    const backdrops = document.querySelectorAll('.modal-backdrop.show');
    backdrops.forEach(backdrop => backdrop.remove());
  }
  // ده اللي هيرجع تاني

  onCashInput(event: any): void {
    const value = event.target.value;
    this.enteredCash = value === '' ? null : Number(value);
    this.errorMessage = null;
    this.apiFieldErrors = null;
  }

  /*  onCashInput(event: any): void {
     const testValue: string = "50";
     const value = testValue;
     console.log(' test :', value);
     this.enteredCash = value === '' ? null : Number(value);
     this.errorMessage = null;
     this.apiFieldErrors = null;
 } */

  // onVisaInput(event: any): void {
  //   const value = event.target.value;
  //   this.enteredVisa = value === '' ? null : Number(value);
  //   this.errorMessage = null;
  //   this.apiFieldErrors = null;
  // }

  async validateAndSave(): Promise<void> {
    // Validate both inputs
    if (this.enteredCash === null || this.enteredCash === undefined || this.enteredCash === '') {
      this.errorMessage = "يرجى إدخال المبلغ النقدي قبل الحفظ.";
      return;
    }

    // if (this.enteredVisa === null || this.enteredVisa === undefined || this.enteredVisa === '') {
    //   this.errorMessage = "يرجى إدخال المبلغ الإلكتروني قبل الحفظ.";
    //   return;
    // }
    this.enteredVisa = this.currentBalance?.visa
    const cashAmount = Number(this.enteredCash);
    const visaAmount = Number(this.enteredVisa);

    if (isNaN(cashAmount) || cashAmount < 0 || isNaN(visaAmount) || visaAmount < 0) {
      this.errorMessage = "المبالغ المدخلة لا تتوافق مع سجلات النظام او الإجماليات المتوقعة.";
      return;
    }

    try {
      const response = await this.balanceService.submitOpeningBalance(cashAmount, visaAmount).toPromise();
      console.log('API Response:', response);
      /*       this.currentBalance={
              cash:response.data.open_cash,
              visa:response.data.open_visa,
              total:response.data.total_opening,
            } */

      if (response?.status && response.data) {
        // Check for deficit amounts
        this.deficitCash = response.data.deficit_cash || 0;
        this.deficitVisa = response.data.deficit_visa || 0;

        if (this.deficitCash !== 0 || this.deficitVisa !== 0) {
          // Show deficit message
          this.showDeficitMessage = true;
          this.buildDeficitMessage();
        } else {
          // No deficit, close modal immediately
          localStorage.setItem(this.BALANCE_OPENED_KEY, 'true');
          this.hideModal(true);
        }
      } else {
        this.errorMessage = response?.message || "فشل في فتح الرصيد. يرجى المحاولة مرة أخرى.";
        this.modalStateService.setModalOpen(true);
      }
    } catch (error: any) {
      console.error('API Error:', error); // Log the error
      if (Array.isArray(error?.error?.errorData?.error)) {
        this.errorMessage = error.error.errorData.error[0];
      } else if (error.error?.message) {
        this.errorMessage = error.error.message;
      } else {
        this.errorMessage = "حدث خطأ في الاتصال بالخادم.";
      }

      if (this.errorMessage?.includes('تم فتح الوردية مسبقاً')) {
        localStorage.setItem(this.BALANCE_OPENED_KEY, 'true');
        this.hideModal(true);
      } else {
        this.modalStateService.setModalOpen(true);
      }
    }
  }
  private buildDeficitMessage(): void {
    let messages = [];

    if (this.deficitCash !== 0) {
      messages.push(`فارق نقدي بقيمة ${this.deficitCash} ${this.currency_Symbol}`);
    }

    if (this.deficitVisa !== 0) {
      messages.push(`فارق في البطاقات بقيمة ${this.deficitVisa} ${this.currency_Symbol}`);
    }

    this.deficitMessage = messages.join(' و ');
  }

  startShift(): void {
    localStorage.setItem(this.BALANCE_OPENED_KEY, 'true');
    this.hideModal(true);
  }

}
