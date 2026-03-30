import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AiService } from '@services/ai.service'; // Проверь путь!

@Component({
  selector: 'app-ai-widget',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './ai-widget.component.html',
  styleUrl: './ai-widget.component.css'
})
export class AiWidgetComponent {
  ai = inject(AiService); // Внедряем сервис
  isOpen = this.ai.isOpen; ; // Сигнал для открытия/закрытия бабла

  toggleChat() {
    this.isOpen.update(v => !v);
    
    // Если открыли — сразу просим у бро подбодрить нас
    if (this.isOpen()) {
      this.ai.askBro('Кто сегодня молодец?');
    }
  }


    send(input: HTMLInputElement) {
    const val = input.value.trim();
    if (!val || this.ai.isLoading()) return;

    this.ai.askBro(val);
    input.value = ''; // Очищаем поле после отправки
  }
}
