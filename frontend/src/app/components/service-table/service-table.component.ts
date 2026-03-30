import { Component, inject, OnInit, signal } from '@angular/core';
import { ApiService } from '@services/api.service'; 
import { AiService } from '@services/ai.service'; 
import { ServiceStatus, STATUS_LABELS } from '@models/service.model';
import { DinoPaginatorComponent } from '@app/shared/components/dino-paginator/dino-paginator.component';
import { RouterLink } from '@angular/router'; // 🧬 Добавь импорт
import { CommonModule } from '@angular/common';
import { environment } from '@env/environment';

@Component({
  selector: 'app-service-table',
  standalone: true,
  imports: [CommonModule, RouterLink, DinoPaginatorComponent], 
  templateUrl: './service-table.component.html',
  styleUrl: './service-table.component.css'
})
export class ServiceTableComponent {
  private api = inject(ApiService);
  public ai = inject(AiService);

  services = this.api.services;
  isLoading = this.api.isLoading;
  statusLabels = STATUS_LABELS;
  statuses = ServiceStatus; 
  totalElements = this.api.totalElements;

  // Локальное состояние (для СДВГ-контроля 🧠)
  currentPage = 1;
  pageSize = 10;

  sortColumn = signal<string>('service_date');
  sortDirection = signal<'ASC' | 'DESC'>('DESC');

  currentFilters: { [key: string]: any } = {
    'status' : '', // 🧠 Сюда будем записывать выбор
    search : ''
  };

  readonly statusOptions = signal(
    Object.entries(STATUS_LABELS).map(([value, label]) => ({
      value: value as ServiceStatus,
      label: label
    }))
  );

  ngOnInit() {
    console.log('🦖 Диплодок проснулся и ищет данные...');
    this.api.fetchRegistry();
  }

  getStatusLabel(status: ServiceStatus): string {
    const s = status as ServiceStatus;
    return this.statusLabels[s] || status || '—';
  }
  
  exportToExcel() {
    const params = new URLSearchParams(this.currentFilters);
  
    const exportUrl = `${environment.apiUrl}/export?${params.toString()}`;
    window.location.href = exportUrl;
    
    // this.ai.lastMessage.set('🦕 Диплодок пакует чемоданы с данными!');
    console.log('🦕 Диплодок пакует чемоданы с данными!');
  }
  
  // Метод для поиска (ТЗ п.1)
  onSearch(term1: string) {
    this.currentFilters['search'] = term1;
    this.api.fetchRegistry({ search: term1 });
  }

onStatusChange(newStatus: string) {
  console.log('🦖 Диплодок фильтрует по статусу:', newStatus);
  this.currentFilters['status'] = newStatus;
  // Вызываем загрузку данных с новым фильтром
  // Мы передаем объект, который PHP-модель findWithAi ожидает увидеть
  this.api.fetchRegistry({ status: newStatus });
}

toggleSort(column: string) {
  if (this.sortColumn() === column) {
    // Если кликнули по той же колонке — меняем направление
    this.sortDirection.set(this.sortDirection() === 'ASC' ? 'DESC' : 'ASC');
  } else {
    // Если по новой — ставим её и сбрасываем на DESC
    this.sortColumn.set(column);
    this.sortDirection.set('DESC');
  }
  // Обновляем фильтры и грузим данные
  this.currentFilters = {
    ...this.currentFilters,
    sort: this.sortColumn(),
    order: this.sortDirection(),
    page: 1 // Сбрасываем на первую страницу при смене сортировки
  };
  
  this.loadData();
}

// Принимаем сразу и текст, и статус
loadData(term: string = this.currentFilters['search'], status: string = this.currentFilters['status']) {
  console.log('🦖 Диплодок выполняет глубокий поиск:', { term, status, page: this.currentPage, sort: this.sortColumn() });
  
  // 🚩 ДОБАВЛЯЕМ ПАГИНАЦИЮ В ЗАПРОС
  this.api.fetchRegistry({ 
    search: term, 
    status: status,
    page: this.currentPage, // 🦕 Передаем текущую страницу
    limit: this.pageSize,    // 📏 И размер страницы
    sort: this.sortColumn(),    // 🦕 Название колонки (напр. 'service_date')
    order: this.sortDirection() // 📈 Направление ('ASC' или 'DESC')    
  });
}

askAiToFilter(query: string) {
    if (!query.trim()) return;

    console.log('🦖 Диплодок анализирует запрос:', query);
    this.ai.isOpen.set(true); 

  this.ai.askAiToFilter(query).subscribe({
    next: (res) => {
      // 🦕 Диплодок говорит в бабл: "Понял, ищу Иванова за вчера!"
      this.ai.lastMessage.set(res.diplodoc); 
    console.log('%c🦕 ДИПЛОДОК РАСПАРСИЛ:', 'color: #6366f1; font-weight: bold; font-size: 12px;');
    console.log(res.filters);
    
      this.currentFilters = { 
        ...this.currentFilters, // 1. Берем ВСЁ, что Диплодок помнил (статус, поиск, лимит)
        ...res.filters,          // 2. Накладываем сверху то, что прислал ИИ (он перезапишет только свои поля)
        page: 1                 // 3. Всегда прыгаем на 1-ю страницу при новом поиске 🚩
      };

      // 2. 🚩 МАГИЯ: передаем полученный от ИИ ОБЪЕКТ фильтров 
      // в наш обычный метод загрузки таблицы
      this.api.fetchRegistry(this.currentFilters); 
      
      this.ai.isLoading.set(false);
    },
    error: () => {
      this.ai.lastMessage.set('🦖 Бро, я не распарсил эту фразу...');
      this.ai.isLoading.set(false);
    }
  });
  }


  totalPages() {
  return Math.ceil(this.totalElements() / this.pageSize) || 1;
}

onPageChange(page: number) {
  if (page >= 1 && page <= this.totalPages()) {
    this.currentPage = page;
    this.loadData();
  }
}

// Прыжок на конкретную страницу
onPageInput(page: any) {
  const num = parseInt(page, 10);
  if (num > 0 && num <= this.totalPages()) {
    this.currentPage = num;
    this.loadData();
  } else {
    // Если ввел дичь — возвращаем текущую в инпут
    this.loadData(); 
  }
}
onLimitChange(limit: number) {
  this.pageSize = limit;
  this.currentPage = 1; // Всегда сбрасываем на первую страницу при смене лимита
  this.loadData();
}

deleteService(id: number) {
  // 🦖 ТЗ п.6: Обязательное подтверждение
  const confirmed = window.confirm(`Бро, ты уверен, что хочешь удалить ${id}? Это серьезно! 🌋`);
  
  if (confirmed) {
    this.api.isLoading.set(true);
    this.api.delete(id).subscribe({
      next: () => {
        this.loadData();
        console.log('🐾 Услуга отправлена в архив');
      },
      error: (err) => {
        console.error('Удаление сорвалось:', err);
        this.api.isLoading.set(false);
      }
    });
  }
}

}
