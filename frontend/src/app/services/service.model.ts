export interface ServiceRecord {
  id: number;
  beneficiary_name: string; 
  service_title: string;    // Название типа услуги
  service_date: string;     // Дата оказания
  status: string;           // Текущий статус (new, completed и т.д.)
  amount: number;           
  comment?: string;  
}