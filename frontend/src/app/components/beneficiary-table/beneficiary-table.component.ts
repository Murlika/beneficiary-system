import { Component, inject, OnInit } from '@angular/core';
import { BeneficiaryService } from '@services/beneficiary.service'; 
import { CommonModule } from '@angular/common';
import { DinoPaginatorComponent } from '@app/shared/components/dino-paginator/dino-paginator.component';
import { RouterLink } from '@angular/router'; // 🧬 Добавь импорт

@Component({
  selector: 'app-beneficiary-table',
  standalone: true,
  imports: [CommonModule, DinoPaginatorComponent, RouterLink], 
  templateUrl: './beneficiary-table.component.html',
  styleUrl: './beneficiary-table.component.css'
})
export class BeneficiaryTableComponent implements OnInit {
  private api = inject(BeneficiaryService);

  // Сигналы из сервиса 📡
  beneficiary = this.api.beneficiary;
  isLoading = this.api.isLoading;
  totalElements = this.api.totalElements;

  // Локальное состояние (для СДВГ-контроля 🧠)
  currentPage = 1;
  pageSize = 10;
  currentSearch = '';

  ngOnInit() {
    this.loadData();
  }

  loadData() {
    this.api.fetchRegistry(this.currentPage, this.pageSize, this.currentSearch);
  }

  onSearch(event: Event) {
    this.currentSearch = (event.target as HTMLInputElement).value;
    this.currentPage = 1; // При поиске всегда прыгаем на первую страницу! 🦖
    this.loadData();
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

deleteBeneficiary(id: number, name: string) {
  // 🦖 ТЗ п.6: Обязательное подтверждение
  const confirmed = window.confirm(`Бро, ты уверен, что хочешь удалить ${name}? Это серьезно! 🌋`);
  
  if (confirmed) {
    this.api.isLoading.set(true);
    this.api.delete(id).subscribe({
      next: () => {
        this.loadData();
      },
      error: (err) => {
        console.error('Удаление сорвалось:', err);
        this.api.isLoading.set(false);
      }
    });
  }
}
}

