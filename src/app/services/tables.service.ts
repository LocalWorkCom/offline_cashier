import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { baseUrl } from '../environment';
import { IndexeddbService } from './indexeddb.service';

@Injectable({
  providedIn: 'root',
})
export class TablesService {
  private apiUrl = `${baseUrl}api`;
  tables: any[] = [];

  private token = localStorage.getItem('authToken');
  // private token =
  //   'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiNWZkY2Q0ZGJlOTY3ZTA5NWVmZDZhODUwZDFiNjE4Y2Y2OTIyNzUyZGUzNTNmYTk5NDE2N2UyODVjNTA4ZDFiYjg4ZjRhMTVmNmM4OTJkZDgiLCJpYXQiOjE3MzkzNTYzMzMuODQzMjQ3ODkwNDcyNDEyMTA5Mzc1LCJuYmYiOjE3MzkzNTYzMzMuODQzMjUxOTQzNTg4MjU2ODM1OTM3NSwiZXhwIjoxNzcwODkyMzMzLjgzMDI1NjkzODkzNDMyNjE3MTg3NSwic3ViIjoiOSIsInNjb3BlcyI6W119.SD1ijYmUDnfxzFXTb7wy0ddygfR70jib0Q6TNoEusD7_PgzQKtKOu5U0MBp_MK2T4zYBibh4jWiSJJ10OjbW7oOs14ev7ZNaYx5HU-cupIr0Qtt_T99rMZPVE_3SZAOohnKBqIoXQZvANgerAYfNUfFg3VP6-YBqUrpsGqzIn0WE3f5tnk65V84ZiZUUX9jnF9z_4qGGZ7ZKSuv94Akc-O4KTT_DAVSFdwqKZt5pzyG5qI-f6TsJGpa0vuUmGZ17gLYCwlb94jm8bbLilt0DWDK65tUiwvEZojljDRm8HwxfrpOy9Z0DPnwRNrxDF77aFC5N_wfTwnsDN6OCz9ZoLUYqJEu10Bw66KnIGOlBfSFjTbTukrSyRxlr52zEa0IrValuR_DZy9aSJ97--4MeW38EMB0_TyZ-4ySe0hk_-Qprtz8D7y35eKYs-YPjgGxHPAJpOyDubx9vEeLWBqtsHPlGRn3Y76YIt6uv14cl_vc0SIktRtobSaH6toeJvriNNLshYppw0N3RBjWVbQKrMpfl931lw7jZSiYgLVRFd4qlQqYKodTqtCHEOlwGPRyJOLLXM3N_1NYEw1L7HtwIbPxVrr2sab-1Ur7i7q6BcbX2KDamEgqDm6CIpEjHEMBnXAXcAt0QELZ5E0hx_Vsv6GR5DrA3E61_tVWAvbbs1xY'; // 🔹 Replace with the actual token
  constructor(private http: HttpClient, private db: IndexeddbService) { }
  getTables(): Observable<any> {
    const headers = new HttpHeaders().set(
      'Authorization',
      `Bearer ${this.token}`
    );
    return this.http.get(`${this.apiUrl}/tables/index`, { headers });
  }
  //start dalia
  fetchAndSave(): Observable<any> {
    return new Observable(observer => {
      this.getTables().subscribe({
        next: async (response: any) => {
          if (response.status) {
            this.tables = response.data.map((table: any) => ({
              ...table,
              status: Number(table.status),
            }));
            try {
              console.log('Tables to save:', this.tables);

              this.db.saveData('tables', this.tables);
              console.log('Tables saved to IndexedDB');
            } catch (error) {
              console.error('Error saving tables to IndexedDB:', error);
            }
          }
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
