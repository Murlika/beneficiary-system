import { Component, inject, OnInit, signal, ElementRef  } from '@angular/core'; 
import { CommonModule } from '@angular/common';
import { ExcelUploaderComponent } from '@components/excel-uploader/excel-uploader.component';
import { BeneficiaryTableComponent } from '@components/beneficiary-table/beneficiary-table.component';
import { ServiceTableComponent } from '@components/service-table/service-table.component';
import { AiWidgetComponent } from '@components/ai-widget/ai-widget.component';
import { RouterOutlet, RouterLink } from '@angular/router'; 

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, ExcelUploaderComponent, BeneficiaryTableComponent, AiWidgetComponent, ServiceTableComponent, RouterOutlet, RouterLink], 
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent implements OnInit {
  currentPage = signal(''); 
  globalLoading = signal(false); 

  constructor(private el: ElementRef) {
    this.currentPage.set('yoyoyo'); 

    console.log('🦖 Бро, я проснулся и увидел страницу:', this.currentPage());
  }

  ngOnInit() {
    const page = this.el.nativeElement.getAttribute('data-page');
    this.currentPage.set(page || 'default');
  }
}