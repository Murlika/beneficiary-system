import { Component, inject, OnInit } from '@angular/core';
import { ApiService } from './services/api.service'; 
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule], 
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent implements OnInit {
  private api = inject(ApiService);

  services = this.api.services;
  isLoading = this.api.isLoading;

  ngOnInit() {
    console.log('🦖 Диплодок проснулся и ищет данные...');
    this.api.fetchRegistry();
  }
}
