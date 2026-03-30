// frontend/src/app/services/ai.service.ts
import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '@env/environment';

@Injectable({ providedIn: 'root' })
export class AiService {
  private http = inject(HttpClient);
  public isOpen = signal(false);
  private readonly baseUrl = environment.apiUrl; 

  // Сигнал для хранения последнего совета от бро
  lastMessage = signal<string>('🦕 Диплодок готов к труду и обороне!');
  isLoading = signal(false);
  messageCount = signal(0);

// ai.service.ts
askBro(query: string) {
  this.isLoading.set(true);
  
  // Шлем объект { q: "текст" } в теле запроса
  this.http.post<{answer: string}>(`${this.baseUrl}/bro`, { q: query }).subscribe({
    next: (res) => {
      const finalQuery = query === "Кто сегодня молодец?" ? `${query} ${res.answer}` : res.answer;

      this.lastMessage.set(finalQuery);
      this.isLoading.set(false);
      this.messageCount.update(n => n + 1);

    },
    error: (err) => {
      console.error('Ошибка Диплодока:', err);
      this.lastMessage.set('🦖 Бро, связь с мезозоем прервалась...');
      this.isLoading.set(false);
    }
  });
}

// 2. НОВЫЙ Метод для ПАРСИНГА (натуральный поиск в базу)
askAiToFilter(query: string) {
  this.isLoading.set(true);
  
  // Возвращаем поток, чтобы компонент мог подписаться
  return this.http.post<any>(`${this.baseUrl}/ask`, { q: query });
}

}
