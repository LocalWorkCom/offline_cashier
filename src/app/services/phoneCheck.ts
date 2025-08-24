import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { baseUrl } from '../environment'; 

@Injectable({
  providedIn: 'root'
})
export class PhoneCheckService {
  private apiUrl = `${baseUrl}api/customer-service/address/check`; // Replace with your actual URL

  constructor(private http: HttpClient) {}

  checkPhone(addressData: phone): Observable<any> {
    
    return this.http.post<any>(this.apiUrl, addressData );
  }
}
interface phone{  
    "country_code":string,
    "address_phone":string  
  }