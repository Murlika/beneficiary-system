import { Component, inject, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { BeneficiaryService } from '@services/beneficiary.service';

@Component({
  selector: 'app-beneficiary-form',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule],
  templateUrl: './beneficiary-form.component.html'
})
export class BeneficiaryFormComponent implements OnInit {
  private fb = inject(FormBuilder);
  private api = inject(BeneficiaryService);
  private route = inject(ActivatedRoute);
  router = inject(Router);

  isEdit = false;
  id: number | null = null;

  // 📋 Гены формы
  form = this.fb.group({
    type: ['person', Validators.required],
    full_name: ['', [Validators.required, Validators.minLength(3)]],
    inn: [''], // Пойдет в extra_data
    phone: [''] // Пойдет в extra_data
  });

  ngOnInit() {
    this.id = Number(this.route.snapshot.paramMap.get('id'));
    if (this.id) {
      this.isEdit = true;
      this.loadBeneficiary();
    }
  }

  loadBeneficiary() {
    this.api.getById(this.id!).subscribe(res => {
      // Предзаполняем кости данными 🦴
      this.form.patchValue({
        type: res.type,
        full_name: res.full_name,
        inn: res.extra_data?.inn,
        phone: res.extra_data?.phone
      });
    });
  }

  onSubmit() {
    if (this.form.invalid) return;

    const raw = this.form.value;
    const payload = {
      type: raw.type,
      full_name: raw.full_name,
      extra_data: { inn: raw.inn, phone: raw.phone } // Склеиваем JSONB 🧬
    };

    const request = this.isEdit 
      ? this.api.update(this.id!, payload) 
      : this.api.create(payload);

    request.subscribe(() => {
      this.router.navigate(['/beneficiaries']); // Возврат в стадо
    });
  }
}
