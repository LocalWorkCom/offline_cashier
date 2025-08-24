import { CloseBalanceService } from './../services/close-balance.service';
import { Component, OnInit, Inject, PLATFORM_ID } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { isPlatformBrowser } from '@angular/common';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { BalanceService } from '../services/balance.service';
import { NgbDropdownModule } from '@ng-bootstrap/ng-bootstrap';
import { NotificationComponent } from '../shared/ui/component/notification/notification.component';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [
    RouterLink,
    RouterLinkActive,
    CommonModule,
    NgbDropdownModule,
    NotificationComponent,
  ],
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.css'],
})
export class NavbarComponent implements OnInit {
  shiftData: any = null;
  fullName: string | null = null;
  phoneNumber: string | null = null;
  closeCash: number = 0;
  closeVisa: number = 0;
  enteredCash: number | string | null = null;
  enteredVisa: number | string | null = null;
  errorMessage: string | null = null;
  branch: string | null = null;
  imageUrl: string | null = null;
  currency_Symbol: string | null = null;
  showCloseBalanceModal: boolean = false;
  
  // Deficit handling properties
  showDeficitMessage = false;
  deficitCash = 0;
  deficitVisa = 0;
  deficitMessage = '';

  constructor(
    public authService: AuthService,
    private router: Router,
    private closeBalanceService: CloseBalanceService,
    private balanceService: BalanceService,
    @Inject(PLATFORM_ID) private platformId: Object
  ) {}

  ngOnInit() {
    this.initializeUserData();
    this.subscribeToAuthChanges();
  }

  private initializeUserData(): void {
    if (isPlatformBrowser(this.platformId)) {
      this.fullName =
        localStorage.getItem('fullName') ||
        sessionStorage.getItem('fullName') ||
        null;
      this.branch = localStorage.getItem('branch');
      this.phoneNumber = localStorage.getItem('phone_number');
      this.imageUrl = localStorage.getItem('imageUrl');
      this.currency_Symbol = localStorage.getItem('currency_symbol');
    }
  }

  private subscribeToAuthChanges(): void {
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

    this.authService.imageUrl$.subscribe((imageUrl) => {
      this.imageUrl = imageUrl;
    });
  }

  async showBalanceoutModal() {
    if (!isPlatformBrowser(this.platformId)) return;

    this.showCloseBalanceModal = true;
    await this.fetchCloseBalanceData();

    setTimeout(() => {
      const modalElement = document.getElementById('balanceoutModal');
      if (modalElement) {
        import('bootstrap').then(({ Modal }) => {
          const modalInstance = new Modal(modalElement);
          modalInstance.show();
        });
      }
    }, 0);
  }

  private async fetchCloseBalanceData(): Promise<void> {
    try {
      const balanceData = this.balanceService.getCurrentBalanceData();
      if (balanceData) {
        this.closeCash = Number(balanceData.open_cash) || 0;
        this.closeVisa = Number(balanceData.open_visa) || 0;
        this.currency_Symbol = balanceData.currency_symbol || this.currency_Symbol;
        
        // Check for existing deficits
        this.deficitCash = balanceData.deficit_cash || 0;
        this.deficitVisa = balanceData.deficit_visa || 0;

        if (this.deficitCash !== 0 || this.deficitVisa !== 0) {
          this.showDeficitMessage = true;
          this.buildDeficitMessage();
        }
      } else {
        this.errorMessage = 'لا يوجد رصيد مفتوح حالياً';
      }
    } catch (error) {
      console.error('Error fetching balance data:', error);
      this.errorMessage = 'حدث خطأ في جلب بيانات الرصيد';
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

  formatTime(time: string | null): string {
    if (!time) return '--:--';
    const [hour, minute] = time.split(':').map(Number);
    if (isNaN(hour) || isNaN(minute)) return '--:--';
    const period = hour < 12 ? 'صباحًا' : 'مساءً';
    const formattedHour = hour % 12 || 12;
    return `${formattedHour}:${minute.toString().padStart(2, '0')} ${period}`;
  }

  onCashInput(event: any): void {
    const value = event.target.value;
    this.enteredCash = value === '' ? null : Number(value);
    this.errorMessage = null;
  }

  onVisaInput(event: any): void {
    const value = event.target.value;
    this.enteredVisa = value === '' ? null : Number(value);
    this.errorMessage = null;
  }

  async validateAndSave(): Promise<void> {
    if (!isPlatformBrowser(this.platformId)) return;

    // Validate both inputs
    if (this.enteredCash === null || this.enteredCash === '') {
      this.errorMessage = 'يرجى إدخال المبلغ النقدي';
      return;
    }

    if (this.enteredVisa === null || this.enteredVisa === '') {
      this.errorMessage = 'يرجى إدخال المبلغ الإلكتروني';
      return;
    }

    const cashAmount = Number(this.enteredCash);
    const visaAmount = Number(this.enteredVisa);

    if (isNaN(cashAmount) || cashAmount < 0 || isNaN(visaAmount) || visaAmount < 0) {
      this.errorMessage = 'المبالغ المدخلة غير صالحة';
      return;
    }

    try {
      const response = await this.closeBalanceService
        .submitCloseBalance(cashAmount, visaAmount)
        .toPromise();

      if (response?.status && response.data) {
        // Check for new deficits in response
        this.deficitCash = response.data.deficit_cash || 0;
        this.deficitVisa = response.data.deficit_visa || 0;

        if (this.deficitCash !== 0 || this.deficitVisa !== 0) {
          this.showDeficitMessage = true;
          this.buildDeficitMessage();
        } else {
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
    this.balanceService.clearBalanceData();
    this.hideBalanceoutModal();
    this.authService.logout().subscribe(() => {
      this.router.navigate(['/login']);
    });
  }

  private hideBalanceoutModal(): void {
    this.showCloseBalanceModal = false;
    const modalElement = document.getElementById('balanceoutModal');
    if (modalElement) {
      import('bootstrap').then(({ Modal }) => {
        const modalInstance = Modal.getInstance(modalElement);
        modalInstance?.hide();
      });
    }
  }

  private handleCloseBalanceError(error: any): void {
    if (error?.error?.errorData?.error) {
      this.errorMessage = Array.isArray(error.error.errorData.error)
        ? error.error.errorData.error[0]
        : error.error.errorData.error;
    } else if (error?.message) {
      this.errorMessage = error.message;
    } else {
      this.errorMessage = 'حدث خطأ في الاتصال بالخادم';
    }
  }

  async confirmLogout(): Promise<void> {
    if (!isPlatformBrowser(this.platformId)) return;
    this.showBalanceoutModal();
  }
}