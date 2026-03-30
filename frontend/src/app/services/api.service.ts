import { inject, Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ServiceRecord } from '@models/service.model';
import { environment } from '@env/environment';
import { Observable } from 'rxjs';

export interface PagedServices {
  data: ServiceRecord[];
  total: number;
}

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private http = inject(HttpClient);
  
  private readonly baseUrl = environment.apiUrl; 

  services = signal<ServiceRecord[]>([]);
  isLoading = signal(false);
  totalElements = signal(0);


fetchRegistry(filters: any = {}) {
    this.isLoading.set(true);

    // Вместо ручного перебора ключей, используем HttpParams
    // Он сам превратит { name: 'Иванов', amount_min: 5000 } в ?name=Иванов&amount_min=5000
    let params: { [key: string]: any } = {};
    
    if (filters) {
      // Очищаем от пустых значений, чтобы не слать мусор
      Object.keys(filters).forEach(key => {
        if (filters[key] !== null && filters[key] !== undefined && filters[key] !== '') {
          params[key] = filters[key];
        }
      });
    }
    this.http.get<PagedServices>(`${this.baseUrl}/services`, { params })
      .subscribe({
        next: (res) => {
          this.services.set(res.data);
          this.totalElements.set(res.total);
          this.isLoading.set(false);
        },
        error: (err) => {
          console.error('Бро, бэкенд на 8080 приуныл:', err);
          this.isLoading.set(false);
        }
      });
}

  delete(id: number) {
    return this.http.delete(`${this.baseUrl}/services/${id}`);
  }

  getById(id: number): Observable<ServiceRecord> {
  return this.http.get<ServiceRecord>(`${this.baseUrl}/services/${id}`);
}

create(data: any): Observable<any> {
  return this.http.post(`${this.baseUrl}/services`, data);
}

update(id: number, data: any): Observable<any> {
  return this.http.put(`${this.baseUrl}/services/${id}`, data);
}

getServiceTypes(): Observable<any[]> {
  return this.http.get<any[]>(`${this.baseUrl}/service-types`);
}
}
