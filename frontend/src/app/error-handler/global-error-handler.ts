import { ErrorHandler, Injectable, inject } from '@angular/core';
import { HttpErrorResponse, HttpClient, HttpBackend } from '@angular/common/http'; 
import { environment } from '@env/environment';

@Injectable()
export class GlobalErrorHandler implements ErrorHandler {
  private http = inject(HttpClient);

  handleError(error: any): void {
    const baseUrl = environment.apiUrl;
    
    if (error instanceof HttpErrorResponse || error.rejection instanceof HttpErrorResponse) {
      console.warn('🐾 Глобальный хендлер пропустил HTTP ошибку (интерцептор разберется)');
      return;
    }

    // 🧬 Извлекаем ДНК ошибки
    const message = error.message ? error.message : error.toString();
    const stack = error.stack ? error.stack : 'Следы размыты... 🦴';

    const logPayload = {
      level: 'critical',
      tag: 'ANGULAR_CRASH',
      behavior: 'GLOBAL_HANDLER',
      message: `[RUNTIME_ERROR] ${message}`,
      context: { 
        stack: stack,
        url: window.location.href 
      }
    };

    this.http.post(`${baseUrl}/clientLog`, logPayload).subscribe();

    console.error('Бро, метеорит упал в Angular! ☄️🦖', error);
  }
}
