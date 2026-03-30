import { inject, Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BeneficiaryRecord } from '@models/beneficiary.model';
import { environment } from '@env/environment';
import { Observable } from 'rxjs';


export interface PagedBeneficiaries {
  data: BeneficiaryRecord[];
  total: number;
}

@Injectable({
  providedIn: 'root'
})
export class BeneficiaryService {
  private http = inject(HttpClient);
  private readonly baseUrl = environment.apiUrl; 

  beneficiary = signal<BeneficiaryRecord[]>([]);
  isLoading = signal(false);
  totalElements = signal(0);

fetchRegistry(page: number = 1, limit: number = 10, search: string = '') {
    this.isLoading.set(true);

    // Строим запрос. Интерцептор сам подставит baseUrl и X-Trace-Id 🧬
    const url = `${this.baseUrl}/beneficiaries?page=${page}&limit=${limit}&search=${search}`;

    this.http.get<PagedBeneficiaries>(url).subscribe({
      next: (res) => {
        // Раскладываем кости по местам 🦴
        this.beneficiary.set(res.data);
        this.totalElements.set(res.total);
      },
      error: (err) => {
        // Ошибка улетит на бэк через интерцептор, тут просто логируем для себя
        console.error('Диплодок споткнулся при загрузке:', err);
      },
      complete: () => {
        this.isLoading.set(false);
      }
    });
  }

  delete(id: number) {
    return this.http.delete(`${this.baseUrl}/beneficiaries/${id}`);
  }

  getById(id: number): Observable<BeneficiaryRecord> {
  return this.http.get<BeneficiaryRecord>(`${this.baseUrl}/beneficiaries/${id}`);
}

create(data: any): Observable<any> {
  return this.http.post(`${this.baseUrl}/beneficiaries`, data);
}

update(id: number, data: any): Observable<any> {
  return this.http.put(`${this.baseUrl}/beneficiaries/${id}`, data);
}

search(term: string = ''): Observable<BeneficiaryRecord[]> {
  return this.http.get<BeneficiaryRecord[]>(`${this.baseUrl}/beneficiaries/search?term=${term}`);
}

}