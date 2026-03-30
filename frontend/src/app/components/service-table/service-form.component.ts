import { Component, inject, OnInit, HostListener, ElementRef } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { ApiService } from '@services/api.service'; // Твой сервис услуг
import { BeneficiaryService } from '@services/beneficiary.service'; // Для списка бенефициаров
import { BeneficiaryRecord } from '@models/beneficiary.model';
import { Subject, debounceTime, distinctUntilChanged, switchMap } from 'rxjs';
import { ServiceStatus, STATUS_LABELS } from '@models/service.model'; // 🧬 Импортим

@Component({
  selector: 'app-service-form',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule],
  templateUrl: './service-form.component.html'
})
export class ServiceFormComponent implements OnInit {
  private fb = inject(FormBuilder);
  private api = inject(ApiService);
  private beneficiaryApi = inject(BeneficiaryService);
  private route = inject(ActivatedRoute);
  router = inject(Router);

  isEdit = false;
  id: number | null = null;
  
  // Кости для выпадашек 🦴
  serviceTypes: any[] = [];
  beneficiaries: any[] = [];

  searchSubject = new Subject<string>();
  selectedBeneficiaryName = ''; // Для отображения в инпуте
  showDropdown = false;
  serviceStatuses = ServiceStatus; 
  statusLabels = STATUS_LABELS;
  statusOptions = Object.values(ServiceStatus); 
  private eRef = inject(ElementRef); // 🧬 Нужен для проверки: попал ли клик "внутрь" нас

  

  form = this.fb.group({
    beneficiary_id: [null as number | null, Validators.required],
    type_id: [null as number | null, Validators.required],
    service_date: [new Date().toISOString().split('T')[0], Validators.required],
    amount: [0, [Validators.required, Validators.min(0)]],
    status: ['new', Validators.required],
    comment: ['']
  });

  ngOnInit() {
    this.loadDictionaries(); // Сначала грузим справочники
    
    this.id = Number(this.route.snapshot.paramMap.get('id'));
    if (this.id) {
      this.isEdit = true;
      this.loadService();
    }
      this.searchSubject.pipe(
        debounceTime(300),
        distinctUntilChanged(),
        switchMap(term => this.beneficiaryApi.search(term))
      ).subscribe(res => {
        this.beneficiaries = res;
        this.showDropdown = true;
      });
      
  }

  loadDictionaries() {
    // Грузим типы услуг (из твоего API)
    this.api.getServiceTypes().subscribe(res => this.serviceTypes = res);
    this.beneficiaryApi.search('').subscribe((res: BeneficiaryRecord[]) => this.beneficiaries = res);
  }

  loadService() {
    this.api.getById(this.id!).subscribe((res: any) => {
      // 🧬 Заполняем саму форму
      this.form.patchValue({
        beneficiary_id: Number(res.beneficiary_id),
        type_id: Number(res.type_id),
        service_date: res.service_date,
        amount: Number(res.amount),
        status: res.status,
        comment: res.comment
      });

      // 🦖 ОЖИВЛЯЕМ АВТОКОМПЛИТ: записываем имя в инпут
      this.selectedBeneficiaryName = res.beneficiary_name || 'Неизвестный бенефициар';
    });
  }

  onSubmit() {
    if (this.form.invalid) return;

    const payload = this.form.value;
    const request = this.isEdit 
      ? this.api.update(this.id!, payload) 
      : this.api.create(payload);

    request.subscribe(() => {
      this.router.navigate(['/services']); // Назад к реестру услуг
    });
  }

  selectBeneficiary(b: any) {
    this.form.patchValue({ beneficiary_id: b.id });
    this.selectedBeneficiaryName = b.full_name;
    this.showDropdown = false;
  }

  @HostListener('document:click', ['$event'])
  clickout(event: Event) {
    if (!this.eRef.nativeElement.contains(event.target)) {
      this.showDropdown = false; // Кликнули мимо — прячем список 🐾
    }
  }

  clearBeneficiary() {
  this.form.patchValue({ beneficiary_id: null });
  this.selectedBeneficiaryName = '';
  this.showDropdown = false;
  this.beneficiaries = []; // Очищаем старые следы 🐾
}

  onInputChange(event: Event) {
    // 1. Достаем текст из инпута 🧬
    const input = event.target as HTMLInputElement;
    const term = input.value;

    // 2. Обновляем визуальное имя (чтобы юзер видел, что пишет)
    this.selectedBeneficiaryName = term;

    // 3. Кидаем в поток поиска (тот самый debounceTime сработает тут) 🦖
    this.searchSubject.next(term);

    // 4. Если юзер всё стер — сбрасываем ID в форме
    if (!term) {
      this.form.patchValue({ beneficiary_id: null });
      this.beneficiaries = [];
      this.showDropdown = false;
    }
  }
}
