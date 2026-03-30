// excel-uploader.component.ts
import { Component, signal } from '@angular/core';
import { HttpClient, HttpEventType } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { environment } from '@env/environment';

@Component({
  selector: 'app-excel-uploader',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './excel-uploader.component.html'
})
export class ExcelUploaderComponent {
  isUploading = signal(false);
  progress = signal(0); 
  message = signal<string | null>(null);
  private readonly baseUrl = environment.apiUrl; 
  importErrors = signal<string[]>([]);
  aiChanges = signal<any[]>([]);
  isSuccess = signal(false);
  readonly maxFileSizeLabel = (window as any).APP_CONFIG?.limits?.display || '10MB';
  
  constructor(private http: HttpClient) {}

onFileSelected(event: Event) {
  const input = event.target as HTMLInputElement;
  if (!input.files?.length) return;

  const file = input.files[0];
  console.log(`🦖 Диплодок учуял новый файл: ${file.name} (${file.size} байт)`);
  
  const formData = new FormData();
  formData.append('excel_file', file);

  this.resetState();
  this.isUploading.set(true);

  this.http.post<any>(`${this.baseUrl}/import`, formData, {
    reportProgress: true,
    observe: 'events'
  }).subscribe({
    next: (event) => {
      if (event.type === HttpEventType.UploadProgress) {
        const percent = Math.round((100 * event.loaded) / (event.total || 100));
        this.progress.set(percent);
        // Не спамим в консоль, логируем только этапы
        if (percent % 25 === 0) console.log(`📦 Загрузка: ${percent}%`);
      } else if (event.type === HttpEventType.Response) {
        console.log('%c🦕 ДИПЛОДОК РАСПАРСИЛ EXCEL:', 'color: #10b981; font-weight: bold; font-size: 12px;');
        this.handleSuccess(event.body);
        input.value = '';
      }
    },
    error: (err) => {
      console.error('🌋 Вулкан взорвался, загрузка прервана:', err);
      this.handleError();
      input.value = ''; 
    }
  });
}

private resetState() {
  this.message.set(null);
  this.importErrors.set([]);
  this.progress.set(0);
  this.isSuccess.set(false);
}

private handleSuccess(res: any) {
  this.isUploading.set(false);
  this.isSuccess.set(true); 
  
  const successCount = res.imported ?? 0;
  this.message.set(res.message || `Импорт завершен! Раскопано записей: ${successCount}`);
  
  if (res.errors?.length) {
    this.importErrors.set(res.errors);
    this.isSuccess.set(false); // Частичный успех
    this.aiChanges.set(res.ai_changed || []);
    
    if (this.aiChanges().length > 0) {
        console.log(`✨ ИИ подправил строк: ${this.aiChanges().length}`);
    }

    console.warn(`⚠️ Найдено ошибок в строках: ${res.errors.length}`);
    console.table(res.errors); // Выведет ошибки красивой таблицей в консоль
  } else {
    console.log(`✅ Чистая победа! Все ${successCount} записей в базе.`);
  }
}

private handleError() {
  this.isUploading.set(false);
  this.message.set('🦖 Бро, сервер ответил рычанием (500). Проверь лог!');
}

}
