// // close-balance.service.ts
// import { Injectable } from '@angular/core';
// import { HttpClient, HttpHeaders } from '@angular/common/http';
// import { Observable, throwError } from 'rxjs';
// import { catchError } from 'rxjs/operators';
// import { AuthService } from './auth.service';
// import { BalanceService } from './balance.service';

// @Injectable({
//   providedIn: 'root',
// })
// export class CloseBalanceService {
//   //private apiUrl = 'https://alkoot-restaurant.com/api/cashier/close-balance';
//   private apiUrl = 'https://erpsystem.testdomain100.online/api/cashier/close-balance';

//   constructor(
//     private http: HttpClient,
//     private authService: AuthService,
//     private balanceService: BalanceService
//   ) {}

//   private getCashierMachineId(): number {
//     // First try from balanceData
//     const balanceData = localStorage.getItem('balanceData');
//     if (balanceData) {
//       try {
//         const parsed = JSON.parse(balanceData);
//         if (parsed.cashier_machine_id) {
//           return Number(parsed.cashier_machine_id);
//         }
//       } catch (e) {
//         console.error('Error parsing balanceData', e);
//       }
//     }
    
//     // Fallback to direct localStorage
//     const directId = localStorage.getItem('cashier_machine_id');
//     return directId ? Number(directId) : 0;
//   }

// // In CloseBalanceService
// submitCloseBalance(cashAmount: number): Observable<any> {
//   // First try to get balance ID from AuthService
//   const balanceId = this.authService.getOpenedBalanceId() || this.balanceService.getCurrentBalanceId();
  
//   const shiftData = this.authService.getShiftData();
//   const scheduleId = this.authService.currentScheduleId;
//   const cashierMachineId = this.getCashierMachineId();

//   if (!scheduleId || !shiftData?.shift_start || !shiftData?.shift_end) {
//     return throwError(() => new Error('Missing required shift data'));
//   }

//   if (!cashierMachineId) {
//     return throwError(() => new Error('Cashier machine ID is missing'));
//   }

//   const token = this.authService.getToken();
//   if (!token) {
//     return throwError(() => new Error('Authentication token is missing'));
//   }

//   const headers = new HttpHeaders({
//     Authorization: `Bearer ${token}`,
//     'Content-Type': 'application/json',
//   });

//   const requestData: any = {
//     cashier_machine_id: cashierMachineId,
//     employee_schedule_id: scheduleId,
//     shift_start: shiftData.shift_start,
//     shift_end: shiftData.shift_end,
//     close_cash: cashAmount,
//     close_visa: 0,
//     ...(balanceId && { balance_id: balanceId }) // Include if exists
//   };

//   return this.http.post<any>(this.apiUrl, requestData, { headers }).pipe(
//     catchError(error => {
//       console.error('Error in close balance API:', error);
//       return throwError(() => ({
//         message: error?.error?.message || 'Unknown error',
//         errors: Array.isArray(error?.error?.errorData?.error)
//           ? error.error.errorData.error
//           : [error.message || 'Unknown error']
//       }));
//     })
//   );
// }
  
// }

import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { AuthService } from './auth.service';
import { BalanceService } from './balance.service';
import { baseUrl } from '../environment'; 

@Injectable({
  providedIn: 'root',
})
export class CloseBalanceService {
    private apiUrl = `${baseUrl}api/cashier/close-balance`;
   private currentBalanceUrl = `${baseUrl}api/cashier/get-current-balance`; 
   
  constructor(
    private http: HttpClient,
    private authService: AuthService,
    private balanceService: BalanceService
  ) {}

  private getCashierMachineId(): number {
    const balanceData = localStorage.getItem('balanceData');
    if (balanceData) {
      try {
        const parsed = JSON.parse(balanceData);
        if (parsed.cashier_machine_id) {
          return Number(parsed.cashier_machine_id);
        }
      } catch (e) {
        console.error('Error parsing balanceData', e);
      }
    }
    
    const directId = localStorage.getItem('cashier_machine_id');
    return directId ? Number(directId) : 0;
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

    const cashierMachineId = this.getCashierMachineId();
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

    return this.http.post<any>(this.currentBalanceUrl, requestData, { headers }).pipe(
      catchError(error => {
        console.error('Error fetching current balance:', error);
        return throwError(() => error);
      })
    );
  }

  submitCloseBalance(cashAmount: number, visaAmount: number, balanceId?: number): Observable<any> {
    const shiftData = this.authService.getShiftData();
    const scheduleId = this.authService.currentScheduleId;
    const cashierMachineId = this.getCashierMachineId();

    if (!scheduleId || !shiftData?.shift_start || !shiftData?.shift_end) {
      return throwError(() => new Error('Missing required shift data'));
    }

    if (!cashierMachineId) {
      return throwError(() => new Error('Cashier machine ID is missing'));
    }

    const token = this.authService.getToken();
    if (!token) {
      return throwError(() => new Error('Authentication token is missing'));
    }

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    });

    const requestData: any = {
      cashier_machine_id: cashierMachineId,
      employee_schedule_id: scheduleId,
      shift_start: shiftData.shift_start,
      shift_end: shiftData.shift_end,
      close_cash: cashAmount,
      close_visa: visaAmount,
      ...(balanceId && { balance_id: balanceId }) 
    };

    return this.http.post<any>(this.apiUrl, requestData, { headers }).pipe(
      catchError(error => {
        console.error('Error in close balance API:', error);
        return throwError(() => ({
          message: error?.error?.message || 'Unknown error',
          errors: Array.isArray(error?.error?.errorData?.error)
            ? error.error.errorData.error
            : [error.message || 'Unknown error']
        }));
      })
    );
  }
}