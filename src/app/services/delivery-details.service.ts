import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { baseUrl } from '../environment'; 

@Injectable({
  providedIn: 'root',
})
export class DeliveryDetailsService {
  private apiUrl = `${baseUrl}api`;
  
  private  token = localStorage.getItem('authToken');
  constructor(private http: HttpClient) {}
  getDeliveryDetails(): Observable<any> {
    const token = localStorage.getItem('authToken');
    const headers = new HttpHeaders().set(
      'Authorization',
      `Bearer ${this.token}`
    );
    console.log('drivers in delivery-details.service');
    return this.http.get(`${this.apiUrl}/orders/deliveries/drivers`, { headers });
  }
}
