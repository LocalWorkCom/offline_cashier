import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, BehaviorSubject, throwError } from 'rxjs';
import { tap, catchError } from 'rxjs/operators';
import { AuthService } from './auth.service';
import { baseUrl } from '../environment'; 

@Injectable({
  providedIn: 'root',
}) 
export class BalanceService {
  private apiUrl = `${baseUrl}api/cashier/open-balance`;
  private balanceData = new BehaviorSubject<any>(null);
  balanceData$ = this.balanceData.asObservable();
  private currentBalanceId = new BehaviorSubject<number | null>(null);
   
  constructor(private http: HttpClient, private authService: AuthService) {
    this.initializeFromStorage();
  }

  private initializeFromStorage(): void {
    const storedBalanceData = localStorage.getItem('balanceData');
    if (storedBalanceData) {
      this.balanceData.next(JSON.parse(storedBalanceData));
    }
    
    const storedBalanceId = localStorage.getItem('openBalanceId');
    if (storedBalanceId) {
      this.currentBalanceId.next(Number(storedBalanceId));
    }
  }

submitOpeningBalance(cashAmount: number, visaAmount: number): Observable<any> {
    const scheduleId = this.authService.currentScheduleId;
    const shiftData = this.authService.getShiftData();
    const cashierMachineId = this.authService.getCashierMachineId();
    const existingBalanceId = this.authService.getOpenedBalanceId();
    const token = this.authService.getToken();

    if (!scheduleId || !shiftData?.shift_start) {
      return throwError(() => new Error('Missing required shift data'));
    }

    if (!cashierMachineId) {
      return throwError(() => new Error('Cashier machine ID is missing'));
    }

    if (!token) {
      return throwError(() => new Error('Authentication token is missing'));
    }

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    });

    const requestData = {
      employee_schedule_id: scheduleId,
      shift_start: shiftData.shift_start,
      shift_end: shiftData.shift_end,
      cashier_machine_id: cashierMachineId,
      open_cash: cashAmount,
      open_visa: visaAmount,
      ...(existingBalanceId && { balance_id: existingBalanceId })
    };

    console.log('Sending request to API with data:', requestData);

    return this.http.post<any>(this.apiUrl, requestData, { headers }).pipe(
    tap(response => {
      console.log('API response:', response);
      if (response?.status && response.data) {
        if (response.data.deficit_cash || response.data.deficit_visa) {
          console.log('Deficit amounts detected:', {
            cash: response.data.deficit_cash,
            visa: response.data.deficit_visa
          });
        }
        this.setBalanceData(response.data);
        if (response.data.id) {
          this.setCurrentBalanceId(response.data.id);
          this.authService.setOpenedBalanceId(response.data.id);
        }
      }
    }), );
  }

  fetchBalanceInfo(): Observable<any> {
    const token = this.authService.getToken();
    if (!token) {
      return throwError(() => new Error('Authentication token is missing'));
    }

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    });

    const scheduleId = this.authService.currentScheduleId;
    const shiftData = this.authService.getShiftData();
    const cashierMachineId = this.authService.getCashierMachineId();

    if (!cashierMachineId) {
      return throwError(() => new Error('Cashier machine ID is missing'));
    }

    const requestData = {
      employee_schedule_id: scheduleId,
      shift_start: shiftData?.shift_start,
      shift_end: shiftData?.shift_end,
      cashier_machine_id: cashierMachineId 
    };

    return this.http.post<any>(
        `${baseUrl}api/cashier/open-balance-info`,
      requestData,
      { headers }
    ).pipe(
      catchError(error => {
        console.error('Error fetching balance info:', error);
        return throwError(() => error);
      })
    );
  }

  setBalanceData(data: any): void {
    this.balanceData.next(data);
    localStorage.setItem('balanceData', JSON.stringify(data));
    
    if (data.id) {
      this.setCurrentBalanceId(data.id);
    }
  }

  getCurrentBalanceData() {
    return this.balanceData.value;
    console.log()
  }

  getCurrentBalanceId(): number | null {
    return this.currentBalanceId.value;
  }
  
  setCurrentBalanceId(id: number): void {
    this.currentBalanceId.next(id);
    localStorage.setItem('openBalanceId', id.toString());
    console.log('Saved Balance ID to localStorage:', id);
  }

  clearBalanceData(): void {
    this.balanceData.next(null);
    this.currentBalanceId.next(null);
    localStorage.removeItem('balanceData');
    localStorage.removeItem('openBalanceId');
    
  }


getCurrentBalance(): Observable<any> {
  const token = this.authService.getToken();
  if (!token) {
    return throwError(() => new Error('Authentication token is missing'));
  }

  const headers = new HttpHeaders({
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json',
  });

  const cashierMachineId = this.authService.getCashierMachineId();
  const scheduleId = this.authService.currentScheduleId;
  const shiftData = this.authService.getShiftData();

  if (!cashierMachineId || !scheduleId || !shiftData) {
    return throwError(() => new Error('Missing required data'));
  }

 const requestData = {
    cashier_machine_id: cashierMachineId,
    employee_schedule_id: scheduleId,
    shift_start: shiftData.shift_start,
    shift_end: shiftData.shift_end
  };

  return this.http.post<any>(
    `${baseUrl}api/cashier/get-current-balance`,
    requestData, 
    { headers } 
  ).pipe(
    catchError(error => {
      console.error('Error fetching current balance:', error);
      return throwError(() => error);
    })
  );
}
PrintBalance(id:number): Observable<any> {
  const token = this.authService.getToken();
  if (!token) {
    return throwError(() => new Error('Authentication token is missing'));
  }

  const headers = new HttpHeaders({
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json',
  });


  return this.http.get<any>(
     `${baseUrl}api/cashier/branch-safe/${id}`,
    { headers }
  ).pipe(
    catchError(error => {
      console.error('Error fetching current balance:', error);
      return throwError(() => error);
    })
  );
}
}