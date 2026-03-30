import { Routes } from '@angular/router';
import { BeneficiaryFormComponent } from '@components/beneficiary-table/beneficiary-form.component';
import { BeneficiaryTableComponent } from '@components/beneficiary-table/beneficiary-table.component';
import { ExcelUploaderComponent } from '@components/excel-uploader/excel-uploader.component';
import { ServiceTableComponent } from '@components/service-table/service-table.component';
import { ServiceFormComponent } from '@components/service-table/service-form.component';

export const routes: Routes = [
  // 🦕 Главная (пусть сразу открывает реестр услуг)
  { path: '', redirectTo: 'services', pathMatch: 'full' },

  // 📦 Импорт Excel
  { path: 'import', component: ExcelUploaderComponent },


  // бенефициары
  { path: 'beneficiaries', component: BeneficiaryTableComponent },
  { path: 'beneficiaries/new', component: BeneficiaryFormComponent },
  { path: 'beneficiaries/edit/:id', component: BeneficiaryFormComponent },

  // 📜 Реестр Услуг
  { path: 'services', component: ServiceTableComponent },
  { path: 'services/new', component: ServiceFormComponent },
  { path: 'services/edit/:id', component: ServiceFormComponent },

  // 🌋 Метеорит (если путь не найден — на главную)
  { path: '**', redirectTo: 'services' }
];

