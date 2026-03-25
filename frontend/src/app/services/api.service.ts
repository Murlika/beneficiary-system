import { inject, Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ServiceRecord } from '../models/service.model';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private http = inject(HttpClient);
  
  private apiUrl = 'http://localhost:8080/api/services';


  services = signal<ServiceRecord[]>([]);
  isLoading = signal(false);

  fetchRegistry(search: string = '') {
    this.isLoading.set(true);

    this.http.get<ServiceRecord[]>(`${this.apiUrl}?search=${search}`)
      .subscribe({
        next: (data) => {
          this.services.set(data);
        },
        error: (err) => {
          console.error('Бро, бэкенд на 8080 приуныл:', err);
        },
        complete: () => {
          this.isLoading.set(false);
        }
      });
  }
}
