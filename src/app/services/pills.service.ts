import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { AuthService } from './auth.service'; 
import { baseUrl } from '../environment'; 

@Injectable({
  providedIn: 'root',
})
export class PillsService {
 private apiUrl =  `${baseUrl}api`;

  constructor(private http: HttpClient, private authService: AuthService) {}

  getPills(): Observable<any> {
    // Get the token from AuthService
    const token = this.authService.getToken();

    if (!token) {
      throw new Error('No authentication token found');
    }

    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);

    // Make the HTTP request
    return this.http.get(`${this.apiUrl}/invoices`, { headers });
  }
}