import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { AuthService } from './auth.service';
import { baseUrl } from '../environment';
import { IndexeddbService } from './indexeddb.service';


@Injectable({
  providedIn: 'root',
})
export class PillsService {
  private apiUrl = `${baseUrl}api`;
  pills: any[] = [];

  constructor(private http: HttpClient, private authService: AuthService, private db: IndexeddbService) { }
  getPills(): Observable<any> {
    // Get the token from AuthService
    // const token = this.authService.getToken();
    const token = localStorage.getItem('authToken');

    if (!token) {
      throw new Error('No authentication token found');
    }

    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);

    // Make the HTTP request
    return this.http.get(`${this.apiUrl}/invoices`, { headers });
  }

  //start dalia
  fetchAndSave(): Observable<any> {
    return new Observable(observer => {
      this.getPills().subscribe({
        next: async (response: any) => {
          if (response.status && response.data.invoices) {
            try {
              this.pills = response.data.invoices
                .map((pill: any) => {
                  if (pill.invoice_type === 'credit_note') {
                    return {
                      ...pill,
                      invoice_print_status: 'returned',
                      invoice_number: pill.invoice_number || `temp_${Date.now()}_${Math.random()}`
                    };
                  }
                  return {
                    ...pill,
                    invoice_number: pill.invoice_number || `temp_${Date.now()}_${Math.random()}`
                  };
                });

              this.db.saveData('pills', this.pills)
                .then(() => {
                  console.log('Pills data saved to IndexedDB');
                })
                .catch(error => console.error('Error saving to IndexedDB:', error));
            } catch (err) {
              console.error('❌ Error saving pills to IndexedDB:', err);
            }
          }
          observer.next(response);
          observer.complete();
        },
        error: (err) => {
          console.error('❌ Failed to fetch pils', err);
          observer.error(err);
        }
      });
    });
  }
  //end dalia



}
