import { ApplicationConfig, ErrorHandler } from '@angular/core';
import { provideRouter } from '@angular/router'; 
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { apiInterceptor } from '@interceptors/api.interceptor';
import { GlobalErrorHandler } from './error-handler/global-error-handler';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async'; 
import { provideAnimations } from '@angular/platform-browser/animations';
import { routes } from './app.routes'; // 👈 Твой файл с путями

export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(routes),
    provideAnimations(),
    { provide: ErrorHandler, useClass: GlobalErrorHandler },
    provideHttpClient(
      withInterceptors([apiInterceptor]) // 🦖 Следим!
    ), provideAnimationsAsync(),
    
  ]
};
