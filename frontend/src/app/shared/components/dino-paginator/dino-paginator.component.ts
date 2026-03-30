
import { Component, Input, Output, EventEmitter, computed } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-dino-paginator',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './dino-paginator.component.html'
})
export class DinoPaginatorComponent {
  @Input({ required: true }) total = 0;
  @Input({ required: true }) pageSize = 10;
  @Input({ required: true }) currentPage = 1;

  @Output() pageChange = new EventEmitter<number>();
  @Output() pageSizeChange = new EventEmitter<number>();

  // Считаем эпохи (страницы) 📜
  get totalPages(): number {
    const pages = Math.ceil(this.total / this.pageSize);
    return pages > 0 ? pages : 1;
  }

  onPageInput(event: Event) {
    const val = (event.target as HTMLInputElement).valueAsNumber;
    if (val > 0 && val <= this.totalPages) {
      this.pageChange.emit(val);
    } else {
      // 🌋 ОТКАТ: Если ввел 9999, возвращаем текущую страницу в поле
      (event.target as HTMLInputElement).value = this.currentPage.toString();
      
      console.warn(`🚧 Страница ${val} еще не раскопана! Максимум: ${this.totalPages}`);
    }
  }

  changePage(delta: number) {
    const newPage = this.currentPage + delta;
    if (newPage >= 1 && newPage <= this.totalPages) {
      this.pageChange.emit(newPage);
    }
  }

  onLimitChange(event: Event) {
    const limit = Number((event.target as HTMLSelectElement).value);
    this.pageSizeChange.emit(limit);
  }

}
