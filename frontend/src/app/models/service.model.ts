/**
 * Интерфейс записи реестра (Плоская таблица из ТЗ п.1)
 */
export interface ServiceRecord {
  id: number;
  // 🔗 Связи (ID для формы)
  beneficiary_id: number; 
  type_id: number;
  
  service_date: string;
  status: ServiceStatus;
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

declare global {
  interface Window {
    APP_CONFIG: {
      statuses: Record<ServiceStatus, string>;
    };
  }
}

// Вытаскиваем человекочитаемые названия из PHP
export const STATUS_LABELS = window.APP_CONFIG?.statuses || {} as Record<ServiceStatus, string>;