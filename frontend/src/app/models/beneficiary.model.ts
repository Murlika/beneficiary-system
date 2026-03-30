/**
 * Интерфейс записи получателей (Без чувствительной информации)
 */
export interface BeneficiaryRecord {
  id: number;           // ID для трекинга в @for
  full_name: string;    // ФИО или название компании
  type: 'person'| 'company'; // Физлицо или Юрлицо
  created_at: string;   // Дата регистрации в системе
  extra_data?: {
    inn?: string;
    phone?: string;
    [key: string]: any; // Позволяем хранить любые другие кости 🦴
  };
}
