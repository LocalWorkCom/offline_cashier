import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError, BehaviorSubject } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { AuthService } from './auth.service';
import { baseUrl } from '../environment'; 

export interface Country {
  code: string;
  flag: string;
  phoneLength:number
}

@Injectable({
  providedIn: 'root',
})
export class ProfileService {

  private apiUrl = `${baseUrl}api/cashier/update-profile`;
   private countriesUrl = `${baseUrl}api/country`;  
    

  constructor(private http: HttpClient, private authService: AuthService) { }




  private fullNameSubject = new BehaviorSubject<string | null>(null);
  fullName$ = this.fullNameSubject.asObservable();
  // Update this method to also update AuthService
  // setFullName(name: string) {
  //   this.fullNameSubject.next(name);
  //   localStorage.setItem('fullName', name);
  //   this.authService.setFullName(name); // Update AuthService
  // }
  setFullName(name: string) {
    this.fullNameSubject.next(name);
    localStorage.setItem('fullName', name);
    sessionStorage.setItem('fullName', name);
    this.authService.setFullName(name);
    // console.log('Full name updated in ProfileService:', name);
  }

  getFullName(): string | null {
    return localStorage.getItem('fullName');
  }





  // Get User Profile - No changes to this function
  getUserProfile(): Observable<{ fullName: string; country_code: string; phone_number: string }> {
    const token = localStorage.getItem('authToken');

    if (!token) {
      console.error('No auth token found. Please log in.');
      return throwError(() => new Error('No authentication token found.'));
    }

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });

    return this.http.get<any>(this.apiUrl, { headers }).pipe(
      map((response) => {
        console.log('API Response:', response);
        return {
          fullName: response.data.full_name || '', // Use full_name instead of combining first/last
          country_code: response.data.country_code,
          phone_number: response.data.phone_number,
        };
      }),
      catchError((error) => {
        console.error('Failed to fetch user profile:', error);
        return throwError(() => error);
      })
    );
  }
  updateUserProfile(updatedData: { full_name: string; country_code: string; phone_number: string }): Observable<any> {
    const token = localStorage.getItem('authToken');
    if (!token) {
      return throwError(() => new Error('No authentication token found.'));
    }

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });

    return this.http.post(this.apiUrl, updatedData, { headers }).pipe(
      map((response) => {
        this.setFullName(updatedData.full_name); // Update the full name
        return response;
      }),
      catchError((error) => {
        console.error('Error updating profile:', error);
        return throwError(() => error);
      })
    );
  }

  getCountries(): Observable<Country[]> {
    const token = localStorage.getItem('authToken'); // Get token

    if (!token) {
      console.error('No auth token found. Please log in.');
      return throwError(() => new Error('No authentication token found.'));
    }

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
    });

    // Send the GET request to fetch the country list
    return this.http.get<any>(this.countriesUrl, { headers }).pipe(
      map((response) => {
        // Assuming the response has an array of countries
        return response.data.map((country: { phone_code: string, image: string }) => ({
          code: country.phone_code,
          flag: country.image,
        }));
      }),
      catchError((error) => {
        console.error('Error fetching countries:', error);
        return throwError(() => error);
      })
    );
  }

}
