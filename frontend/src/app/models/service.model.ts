/**
 * Интерфейс записи реестра (Плоская таблица из ТЗ п.1)
 */
export interface ServiceRecord {
  id: number;
  
  service_date: string;
  status: string;
  amount: number;
  comment?: string;
  
  beneficiary_name: string; // ФИО из таблицы beneficiaries
  service_title: string;    // Название из таблицы service_types
  
  created_at?: string;
  deleted_at?: string;
}

/**
 * Константы статусов (чтобы не ошибиться в буквах)
 */
export enum ServiceStatus {
  NEW = 'new',
  PENDING = 'pending',
  IN_PROGRESS = 'in_progress',
  COMPLETED = 'completed',
  CANCELED = 'canceled'
}
