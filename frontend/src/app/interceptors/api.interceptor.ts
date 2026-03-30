// src/app/interceptors/api.interceptor.ts
import { HttpInterceptorFn } from '@angular/common/http';
import { HttpClient, HttpBackend } from '@angular/common/http';
import { catchError } from 'rxjs/operators';
import { throwError } from 'rxjs';
import { inject } from '@angular/core';
import { environment } from '@env/environment';

//TODO добавить логирование в проде в базу в таблицу логов поведения
export const apiInterceptor: HttpInterceptorFn = (req, next) => {
  console.log('🦕 Отправляю на:', req.url);

  const traceId = Math.random().toString(36).substring(2, 9).toUpperCase();

  // Если нужно добавить заголовок (например, для CORS или Auth)
  const clonedRequest = req.clone({
    setHeaders: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-Trace-Id': traceId 
    }
  });

  const baseUrl = environment.apiUrl; 

  const backend = inject(HttpBackend);
  const silentHttp = new HttpClient(backend);

  return next(clonedRequest).pipe(
    catchError((error) => {
      // Отправляем отчет о беде на бэк
      const logPayload = {
        level: 'error',
        message: `Ошибка на ${req.url}: ${error.message}`,
        context: { status: error.status, body: error.error }
      };

      // Шлем "тихий" запрос, чтобы не зациклиться
      if (!req.url.includes('clientLog')) {
        silentHttp.post(`${baseUrl}/clientLog`, logPayload).subscribe();
      }

      console.error('🦖 Бро, я приуныл и отправил лог на бэк...', error);
      return throwError(() => error);
    })
  );
};
