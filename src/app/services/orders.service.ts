import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { AuthService } from './auth.service';
import { baseUrl } from '../environment';

@Injectable({
  providedIn: 'root',
})
export class OrdersService {
  private apiUrl =  `${baseUrl}api`;
  constructor(private http: HttpClient, private authService: AuthService) {}

  getOrders(): Observable<any> {
    // const token = this.authService.getToken();
    const token = localStorage.getItem('authToken');

    if (!token) {
      throw new Error('No authentication token found');
    }

    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);

    return this.http.get(`${this.apiUrl}/orders`, { headers });
  }

  
}
