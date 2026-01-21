import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { baseUrl } from '../environment';

export interface PrinterResponse {
  status: boolean;
  message?: string;
  data?: any;
}

@Injectable({
  providedIn: 'root'
})
export class PrinterManagementService {
  private apiUrl = `${baseUrl}api`;

  constructor(private http: HttpClient) {}

  private getHeaders(): HttpHeaders {
    const token = localStorage.getItem('authToken');
    return new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json'
    });
  }

  getPrinters(): Observable<PrinterResponse> {
    return this.http.get<PrinterResponse>(`${this.apiUrl}/printers`, { headers: this.getHeaders() });
  }

  getBranchCategories(branchId: string | number): Observable<PrinterResponse> {
    return this.http.get<PrinterResponse>(`${this.apiUrl}/menu-categories-lite?branchId=${branchId}`, { headers: this.getHeaders() });
  }

  createPrinter(payload: any): Observable<PrinterResponse> {
    return this.http.post<PrinterResponse>(`${this.apiUrl}/printers`, payload, { headers: this.getHeaders() });
  }

  updatePrinter(id: number, payload: any): Observable<PrinterResponse> {
    return this.http.post<PrinterResponse>(`${this.apiUrl}/printers/${id}`, payload, { headers: this.getHeaders() });
  }

  deletePrinter(id: number): Observable<PrinterResponse> {
    return this.http.delete<PrinterResponse>(`${this.apiUrl}/printers/${id}`, { headers: this.getHeaders() });
  }
}
