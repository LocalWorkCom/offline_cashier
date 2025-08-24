import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { baseUrl } from '../environment'; 

@Injectable({
  providedIn: 'root'
})
export class AddAddressService {


   private apiUrl = `${baseUrl}api/cashier/add/address`; 
   private apiUrl2 = `${baseUrl}api/listHotels`;  
  

  private token = localStorage.getItem('authToken');
  constructor(private http: HttpClient) { }
  submitForm(formData: any): Observable<any> {
    // Set the Authorization header with the Bearer token
    const headers = new HttpHeaders().set(
      'Authorization',
      `Bearer ${this.token}`
    );

    // Send the POST request with the form data and headers
    return this.http.post(this.apiUrl, formData, { headers });
  }


  getHotelsData() {
    const headers = new HttpHeaders().set(
      'Authorization',
      `Bearer ${this.token}`
    );
    return this.http.get(this.apiUrl2, { headers });

  }
}
