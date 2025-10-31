import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { baseUrl } from '../environment';
import { IndexeddbService } from './indexeddb.service';

@Injectable({
  providedIn: 'root'
})
export class AddAddressService {


   private apiUrl = `${baseUrl}api/cashier/add/address`;
   private apiUrl2 = `${baseUrl}api/listHotels`;
  hotels:  any[] = [];
  allHotels: any[] = [];
  allAreas: any[] = [];
  areas: any[] = [];


  private token = localStorage.getItem('authToken');
  constructor(private http: HttpClient, private db: IndexeddbService) { }
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


  //start dalia

   fetchAndSave(): Observable<any> {
    return new Observable(observer => {
      this.getHotelsData().subscribe({
        next: async (response: any) => {
        if (response.data) {
            this.hotels = response.data;
            this.allHotels = response.data;

            // Save to IndexedDB
            this.db.saveData('hotels', response.data);
            // this.dbService.lastSync('hotels');

            console.log('Hotels loaded from API and saved to IndexedDB', this.hotels);
          }
        },
        error: (err) => {
          console.error('❌ Failed to fetch pils', err);
          observer.error(err);
        }
      });
    });
  }


    fetchAndSaveAreas() {
    const branchId = localStorage.getItem('branch_id');
    if (!branchId) {
      console.error('branch_id not found in localStorage');
      return;
    }
      const url = `${baseUrl}api/areas/${branchId}`;
      this.http.get<any>(url).subscribe({
        next: (res: { status: any; data: any }) => {
          if (res.status && res.data) {
            this.areas = res.data;
            this.allAreas = res.data;
            // Save to IndexedDB
            this.db.saveData('areas', res.data);
               this.db.saveData('branch_id', {
            id: 'current_branch_id',
            value: branchId,
            timestamp: new Date().toISOString()
          });
            console.log('Areas loaded from API and saved to IndexedDB', this.areas);
          }
        },
        error: (err) => {
          console.error('Error loading areas from API, using cached data:', err);
        },
      });

  }
  //end dalia
}
