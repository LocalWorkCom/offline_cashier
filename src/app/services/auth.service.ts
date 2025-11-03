import { Injectable } from '@angular/core';
import { Injector } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable, BehaviorSubject, of, throwError, forkJoin } from 'rxjs';
import { tap, catchError } from 'rxjs/operators';
import { baseUrl } from '../environment';
import { OrderListService } from './order-list.service';
import { PillsService } from './pills.service';
import { ProductsService } from './products.service';
import { TablesService } from './tables.service';
import { AddAddressService } from './add-address.service';

@Injectable({
  providedIn: 'root',
})
export class AuthService {

  private apiUrl = `${baseUrl}api/cashier-login`;
  private logoutUrl = `${baseUrl}api/cashier-logout`;
  loading: boolean = true;


  //---- 🔹 Observables for user data
  private usernameSubject = new BehaviorSubject<string>(
    this.getStorageItem('username', '')
  );
  username$ = this.usernameSubject.asObservable();

  private phoneNumberSubject = new BehaviorSubject<string>(
    this.getStorageItem('phone_number', '')
  );
  phoneNumber$ = this.phoneNumberSubject.asObservable();

  private countryCodeSubject = new BehaviorSubject<string>(
    this.getStorageItem('country_code', '')
  );
  countryCode$ = this.countryCodeSubject.asObservable();

  private shiftDataSubject = new BehaviorSubject<any>(
    this.getStorageItem('shiftData', null, true)
  );
  shiftData$ = this.shiftDataSubject.asObservable();

  private branchSubject = new BehaviorSubject<string>(
    this.getStorageItem('branch', '')
  );
  branch$ = this.branchSubject.asObservable();

  private modalShownSubject = new BehaviorSubject<boolean>(
    this.getStorageItem('modalShown', 'false') === 'true'
  );
  modalShown$ = this.modalShownSubject.asObservable();
  private branchIdSubject = new BehaviorSubject<number | null>(
    this.getStorageItem('branch_id', null, true)
  );
  branchId$ = this.branchIdSubject.asObservable();

  private scheduleIdSource = new BehaviorSubject<number | null>(
    Number(localStorage.getItem('employee_schedule_id')) || null
  );
  scheduleId$ = this.scheduleIdSource.asObservable();
  // user image
  private imageUrlSubject = new BehaviorSubject<string>(
    this.getStorageItem('imageUrl', '')
  );
  imageUrl$ = this.imageUrlSubject.asObservable();

  private fullNameSubject = new BehaviorSubject<string | null>(
    sessionStorage.getItem('fullName') ||
    localStorage.getItem('fullName') ||
    null
  );
  fullName$ = this.fullNameSubject.asObservable();

  private employeeDataSubject = new BehaviorSubject<any>(null);
  employeeData$ = this.employeeDataSubject.asObservable();

  private employeeIdSubject = new BehaviorSubject<number | null>(
    Number(localStorage.getItem('employee_id')) || null
  );
  employeeId$ = this.employeeIdSubject.asObservable();

  private cashierMachineIdSubject = new BehaviorSubject<number | null>(
    this.getStorageItem('cashier_machine_id', null, true)
  );
  cashierMachineId$ = this.cashierMachineIdSubject.asObservable();

  private visaBalanceSubject = new BehaviorSubject<number | null>(null);
  visaBalance$ = this.visaBalanceSubject.asObservable();

  // Add isOpenBalance subject
  private isOpenBalanceSubject = new BehaviorSubject<boolean>(
    this.getStorageItem('is_open_balance', false, true)
  );
  isOpenBalance$ = this.isOpenBalanceSubject.asObservable();

  private openedBalanceIdSubject = new BehaviorSubject<number | null>(
    this.getStorageItem('opened_balance_id', null, true)
  );
  openedBalanceId$ = this.openedBalanceIdSubject.asObservable();
  setOpenedBalanceId(id: number): void {
    this.openedBalanceIdSubject.next(id);
    this.setStorageItem('opened_balance_id', id, true);
  }

  getOpenedBalanceId(): number | null {
    return this.openedBalanceIdSubject.value;
  }
  setEmployeeData(employee: any): void {
    console.log('dd', employee);

    if (employee) {
      const fullName = `${employee.first_name} ${employee.last_name}`;
      this.fullNameSubject.next(fullName);
      sessionStorage.setItem('fullName', fullName);
      sessionStorage.setItem('flag', employee.flad);
      if (employee.id) {
        this.setEmployeeId(employee.id);
      }
      this.employeeDataSubject.next(employee);
    }
  }

  setEmployeeId(id: number): void {
    this.employeeIdSubject.next(id);
    localStorage.setItem('employee_id', id.toString());
    sessionStorage.setItem('employee_id', id.toString());
  }

  get currentEmployeeId(): number | null {
    return this.employeeIdSubject.value;
  }

  // In AuthService
  setOpenBalanceStatus(status: boolean): void {
    this.isOpenBalanceSubject.next(status);
    localStorage.setItem('is_open_balance', status.toString());
    localStorage.setItem('isBalanceOpened', status.toString()); // Keep both for compatibility
  }

  getOpenBalanceStatus(): boolean {
    return localStorage.getItem('is_open_balance') === 'true' ||
      this.isOpenBalanceSubject.value;
  }

  setCashierMachineId(id: number | null): void {
    this.cashierMachineIdSubject.next(id);
    this.setStorageItem('cashier_machine_id', id, true);
  }
  setVisaBalance(balance: number): void {
    this.visaBalanceSubject.next(balance);
    localStorage.setItem('visa_balance', balance.toString());
  }

  getVisaBalance(): number | null {
    return this.visaBalanceSubject.value;
  }

  getCashierMachineId(): number | null {
    return this.cashierMachineIdSubject.value;
  }
  getEmployeeData(): any {
    return this.employeeDataSubject.value;
  }
  getCurrentEmployee() {
    return (this.employeeData$ as BehaviorSubject<any>).value;
  }
  constructor(private http: HttpClient ,  private injector: Injector, private router: Router) {
    window.addEventListener('storage', (event: StorageEvent) => {
      if (event.key === 'authToken') {
        if (event.newValue) {
          this.fullNameSubject.next(this.getFullName());
          this.usernameSubject.next(this.getStorageItem('username', ''));
          this.phoneNumberSubject.next(this.getStorageItem('phone_number', ''));
          this.countryCodeSubject.next(this.getStorageItem('country_code', ''));
          this.shiftDataSubject.next(
            this.getStorageItem('shiftData', null, true)
          );
          this.branchSubject.next(this.getStorageItem('branch', ''));
          this.branchIdSubject.next(
            this.getStorageItem('branch_id', null, true)
          );
          this.scheduleIdSource.next(
            this.getStorageItem('employee_schedule_id', null, true)
          );
          this.imageUrlSubject.next(this.getStorageItem('imageUrl', ''));
          this.cashierMachineIdSubject.next(
            this.getStorageItem('cashier_machine_id', null, true)
          );
          this.isOpenBalanceSubject.next(
            this.getStorageItem('is_open_balance', false, true)
          );
        } else {
          this.clearSession();
        }
      }
    });
  }

  setFullName(fullName: string): void {
    this.fullNameSubject.next(fullName);
    localStorage.setItem('fullName', fullName);
    sessionStorage.setItem('fullName', fullName);
  }
  getFullName(): string | null {
    return (
      sessionStorage.getItem('fullName') ||
      localStorage.getItem('fullName') ||
      null
    );
  }
  // Simple email regex pattern
  private isEmail(input: string): boolean {

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(input);
  }
  // 🔹 LOGIN METHOD
  login(loginData: {

    country_code?: string;
    email_or_phone: string;
    password: string;
  }): Observable<any> {
    return this.http.post(this.apiUrl, loginData).pipe(
      tap((response: any) => {
        this.loading = true;
        console.log('🔹 Full Login Response:', response);
        if (response.status && response.data) {
          const employee = response.data.employee;
          this.setEmployeeData(employee);
          const fullName = `${employee.first_name} ${employee.last_name}`;
          this.setFullName(fullName);
          const branch = employee.branch;
          const registeredCountryCode = employee.country_code;
          const phoneNumber = employee.phone_number;
          const token = response.data.access_token;
          const branchId = employee.branch_id;
          const scheduleId = employee.employee_schedule_id;
          const userData = response.data.employee;
          const imageUrl = employee.image;
          this.setCashierMachineId(employee.cashier_machine_id);

          if (response.data.visa_total !== undefined) {
            this.setVisaTotal(response.data.visa_total);
          }
          // Only validate country code if login was with phone number
          const isEmailLogin = this.isEmail(loginData.email_or_phone);
          if (!isEmailLogin) {
            if (!loginData.country_code) {
              throw new Error('كود الدولة مطلوب لتسجيل الدخول برقم الهاتف');
            }
            if (loginData.country_code !== registeredCountryCode) {
              throw new Error('كود الدولة غير صحيح');
            }
          }

          // Set open balance status from response
          if (response.data.is_open_balance !== undefined) {
            console.log('Setting open balance status to:', response.data.is_open_balance);
            this.setOpenBalanceStatus(response.data.is_open_balance);
          }

          console.log('Stored User Data:', userData);

          const branchDetails = response.data.branch ?? null;
          if (branchDetails) {
            this.setStorageItem('branchData', branchDetails, true);
            const currency_symbol = branchDetails.currency_symbol;
            this.setStorageItem('currency_symbol', currency_symbol);


          } else {
            console.warn('⚠️ No Branch Data Found in Response');
          }

          // 🔹 Store token securely
          this.setStorageItem('authToken', token);

          this.logToken();
          this.logAllStoredData();

          // 🔹 Store user data
          this.setUsername(fullName);
          this.setPhoneNumber(phoneNumber);
          this.setCountryCode(registeredCountryCode);
          this.setBranch(branch);
          this.setBranchId(branchId);
          this.setScheduleId(scheduleId);
          this.setImageUrl(imageUrl);

          // 🔹 Store shift data
          const shiftData = {
            scheduleId: scheduleId,
            shift_start: employee.shift_start,
            shift_end: employee.shift_end,
            shift_type: employee.shift_type,
            day: response.data.day,
            time: response.data.time,
          };
          this.setShiftData(shiftData);

          this.markModalAsNotShown();

          // 🔹 Sync authentication state across tabs
          window.dispatchEvent(
            new StorageEvent('storage', { key: 'authToken', newValue: token })
          );
          console.log('✅ Login successful, session synced across tabs.');
        }


        // ==========================
        // 🚀 START DALIA - after login logic
        // ==========================
        this.loading = false;

        // ✅ Navigate immediately to home
        this.router.navigate(['/home']);

        // ✅ Load categories first
        const productService = this.injector.get(ProductsService);
        // productService.fetchAndSave().subscribe({
        //   next: () => {
        //     console.log('✅ Categories fetched and saved after login.');

        //     // ✅ After categories → load all other data in background
        //     const tablesService = this.injector.get(TablesService);
        //     const addAddressService = this.injector.get(AddAddressService);
        //     const orderListService = this.injector.get(OrderListService);
        //     const pillService = this.injector.get(PillsService);

        //     forkJoin({
        //       tables: tablesService.fetchAndSave(),
        //       hotels: addAddressService.fetchAndSave(),
        //       areas: addAddressService.fetchAndSaveAreas(),
        //       orders: orderListService.fetchAndSaveOrders(),
        //       pills: pillService.fetchAndSave(),
        //     }).subscribe({
        //       next: () => {
        //         console.log('✅ All background data fetched successfully.');
        //       },
        //       error: (err: any) => {
        //         console.error('❌ Error fetching background data:', err);
        //       },
        //     });
        //   },
        //   error: (err: any) => {
        //     console.error('❌ Error fetching categories after login:', err);
        //   },
        // });
        // ==========================
        // 🚀 END DALIA
        // ==========================

      }),
      catchError((error) => {
        console.error('❌ Login Error:', error);
        return throwError(() => error);
      })
    );
  }





  setImageUrl(imageUrl: string): void {
    this.imageUrlSubject.next(imageUrl);
    this.setStorageItem('imageUrl', imageUrl);
  }

  fetchUserDetails(): void {
    this.http.get('/api/user-details').subscribe({
      next: (data: any) => {
        this.scheduleIdSource.next(data.scheduleId);
        this.shiftDataSubject.next(data.shiftData);
      },
      error: (error) => console.error('❌ Failed to fetch user details', error),
    });
  }

  // 🔹 GET TOKEN FROM STORAGE
  getToken(): string | null {
    return this.getStorageItem('authToken', null);
  }
  // auth.service.ts
  get currentScheduleId(): number | null {
    return this.scheduleIdSource.value;
  }
  // 🔹 CHECK IF TOKEN IS EXPIRED
  isTokenExpired(): boolean {
    const token = this.getToken();
    if (!token) return true;

    try {
      const payload = JSON.parse(atob(token.split('.')[1]));
      const exp = payload.exp * 1000; // Convert to milliseconds
      return Date.now() > exp; // Token expired if current time is past expiration
    } catch (error) {
      console.error('⚠️ Invalid Token:', error);
      return true;
    }
  }

  // 🔹 CHECK IF USER IS AUTHENTICATED
  isAuthenticated(): boolean {
    return !!this.getToken() && !this.isTokenExpired();
  }

  logAllStoredData(): void {
    Object.keys(localStorage).forEach((key) => {
      try {
        const value = localStorage.getItem(key);
      } catch (error) {
        console.log(`🗂️ ${key}:`, localStorage.getItem(key));
      }
    });
  }

  private visaTotalSubject = new BehaviorSubject<number>(0);
  visaTotal$ = this.visaTotalSubject.asObservable();

  setVisaTotal(total: number): void {
    this.visaTotalSubject.next(total);
    this.setStorageItem('visa_total', total, true);
  }

  getVisaTotal(): number {
    return this.visaTotalSubject.value;
  }
  // 🔹 LOGOUT METHOD
  logout(): Observable<any> {
    const token = this.getToken();

    // 🔹 Debugging Token and Expiration Status
    console.log('⏳ Token Expired?', this.isTokenExpired());

    if (!token) {
      console.warn('🚨 No token found, user is already logged out.');
      return of({ status: false, message: 'Already logged out' });
    }

    if (this.isTokenExpired()) {
      console.warn('🔄 Token expired! Logging out locally.');
      localStorage.clear();
      this.clearSession();
      this.removeModalBackdrop(); // 🔹 Remove modal backdrop if it exists
      this.resetBodyOverflow(); // 🔹 Reset body overflow
      window.alert('Your session has expired. You have been logged out.');
      return of({ status: false, message: 'Session expired, logged out' });
    }

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    });

    return this.http.get(this.logoutUrl, { headers }).pipe(
      tap(() => {
        console.log('✅ Successfully logged out');
        this.clearSession();

        this.removeModalBackdrop(); // 🔹 Remove modal backdrop
        this.resetBodyOverflow(); // 🔹 Reset body overflow
      }),
      catchError((error) => {
        console.error('❌ Logout API Error:', error);
        this.removeModalBackdrop(); // 🔹 Remove modal backdrop on error
        this.resetBodyOverflow(); // 🔹 Reset body overflow on error
        return throwError(() => error);
      })
    );
  }

  // 🔹 Function to Remove Modal Backdrop
  private removeModalBackdrop(): void {
    setTimeout(() => {
      const backdrops = document.querySelectorAll('.modal-backdrop.show');
      backdrops.forEach((backdrop) => backdrop.remove());
    }, 500); // 🔹 Delay to ensure Bootstrap has time to close the modal
  }

  // 🔹 Function to Reset Body Overflow
  private resetBodyOverflow(): void {
    setTimeout(() => {
      document.body.style.overflow = 'auto'; // 🔹 Reset overflow to auto
    }, 500); // 🔹 Delay to ensure Bootstrap has time to close the modal
  }

  // 🔹 SESSION CLEAR
  clearSession(): void {
    const keysToRemove = [
      'authToken',
      'username',
      'fullName',
      'currency_symbol',
      'phone_number',
      'first_name',
      'last_name',
      'country_code',
      'shiftData',
      'modalShown',
      'employee_id',
      'address_id',
      'cart',
      'clickedTableId',
      'form_data',
      'savedOrder',
      'savedOrders',
      'selectedCountry',
      'selectedCourier',
      'selectedOrderType',
      'selected_table',
      'table_id',
      'table_number',
      'tables',
      'note',
      'branch',
      'branch_id',
      'employee_schedule_id',
      'imageUrl',
      'branchData',
      'balanceData',
      'openBalanceId',
      'isBalanceOpened',
      'cashier_machine_id',
      'visaBalance',
      'is_open_balance',
      'opened_balance_id',
      'appliedCoupon',
      'couponCode',
      'cash_amountt',
      'modalShown',
      'opened_balance_id',
      'visa_total',
      ' clientPhone',
      'selectedPaymentStatus',
      'isBalanceOpened',
      'is_open_balance',
    ];
    keysToRemove.forEach((key) => {
      localStorage.removeItem(key);
      sessionStorage.removeItem(key);
      window.dispatchEvent(
        new StorageEvent('storage', { key, newValue: null })
      );
    });

    // Reset BehaviorSubjects
    this.usernameSubject.next('');
    this.phoneNumberSubject.next('');
    this.countryCodeSubject.next('');
    this.shiftDataSubject.next(null);
    this.branchSubject.next('');
    this.branchIdSubject.next(null);
    this.scheduleIdSource.next(null);
    this.modalShownSubject.next(false);
    this.imageUrlSubject.next('');
    this.employeeIdSubject.next(null);
    this.isOpenBalanceSubject.next(false);

    console.log('✅ Session cleared successfully.');
  }

  // 🔹 MODAL STATUS HANDLING
  hasModalBeenShown(): boolean {
    return this.modalShownSubject.value;
  }
  markModalAsShown(): void {
    this.updateStorage('modalShown', 'true');
  }
  markModalAsNotShown(): void {
    this.updateStorage('modalShown', 'false');
  }
  getUsername(): string {
    return this.usernameSubject.value;
  }
  getShiftData(): any {
    return this.shiftDataSubject.value;
  }

  // 🔹 USER DATA SETTERS
  setUsername(username: string): void {
    this.usernameSubject.next(username);
    sessionStorage.setItem('username', username); // Store in sessionStorage
    this.setStorageItem('username', username);
  }

  setPhoneNumber(phoneNumber: string): void {
    this.updateStorage('phone_number', phoneNumber);
  }

  setCountryCode(countryCode: string): void {
    this.updateStorage('country_code', countryCode);
  }

  setBranch(branch: string): void {
    this.updateStorage('branch', branch);
  }

  // setShiftData(shiftData: any): void {
  //   this.shiftDataSubject.next(shiftData);
  //   this.setStorageItem('shiftData', shiftData, true);
  // }
  setShiftData(shiftData: any): void {
    this.shiftDataSubject.next(shiftData);
    this.setStorageItem('shiftData', shiftData, true);
    if (shiftData?.scheduleId) {
      this.scheduleIdSource.next(shiftData.scheduleId);
    }
  }

  setBranchId(branchId: number | null): void {
    this.branchIdSubject.next(branchId);
    this.setStorageItem('branch_id', branchId, true);
  }
  setScheduleId(scheduleId: number | null): void {
    this.scheduleIdSource.next(scheduleId);
    if (scheduleId !== null) {
      localStorage.setItem('employee_schedule_id', scheduleId.toString());
    } else {
      localStorage.removeItem('employee_schedule_id');
    }
  }
  getBranchId(): number | null {

    return this.branchIdSubject.value;
  }


  // 🔹 API TO FETCH COUNTRIES

  getCountries(): Observable<any> {
    return this.http.get<any>(`${baseUrl}api/country`);
  }



  // 🔹 LOG STORED TOKEN
  logToken(): void {
    // console.log('Stored Token:', this.getToken());
  }

  // 🔹 METHOD TO MAKE AUTHORIZED REQUESTS
  getAuthHeaders(): HttpHeaders {
    const token = this.getToken();
    console.log(localStorage.getItem('authToken'));

    if (!token) {
      console.error('🚨 No token found, user is unauthenticated.');
    }
    return new HttpHeaders({
      Authorization: `Bearer ${token}`, // ✅ Ensure correct format
      'Content-Type': 'application/json',
    });
  }

  // 🔹 UTILITY FUNCTIONS FOR LOCAL STORAGE HANDLING
  private updateStorage(key: string, value: any): void {
    switch (key) {
      case 'username':
        this.usernameSubject.next(value);
        break;
      case 'phone_number':
        this.phoneNumberSubject.next(value);
        break;
      case 'country_code':
        this.countryCodeSubject.next(value);
        break;
      case 'branch':
        this.branchSubject.next(value);
        break;
      case 'branch_id':
        this.branchIdSubject.next(Number(value)); // Ensure it's stored as a number
        break;
      case 'employee_schedule_id':
        this.scheduleIdSource.next(Number(value)); // Ensure it's stored as a number
        break;
      case 'modalShown':
        this.modalShownSubject.next(value === 'true');
        break;
    }
    this.setStorageItem(key, value, typeof value === 'object');
  }

  private getStorageItem(
    key: string,
    defaultValue: any,
    isJson: boolean = false
  ): any {
    if (typeof window === 'undefined') return defaultValue;

    // 🔹 Check sessionStorage first, then localStorage
    let item = sessionStorage.getItem(key) || localStorage.getItem(key);
    return item ? (isJson ? JSON.parse(item) : item) : defaultValue;
  }

  // In AuthService
  private setStorageItem(
    key: string,
    value: any,
    isJson: boolean = false
  ): void {
    if (typeof window !== 'undefined') {
      localStorage.setItem(key, isJson ? JSON.stringify(value) : value);
      // console.log(`Stored ${key} in localStorage:`, value);
    }
  }
}
