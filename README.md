# 🦕 BSR: Beneficiary Service Registry

**BSR (Beneficiary Service Registry)** — это высокопроизводительная система учета социальных услуг, оказанных благополучателям. Спроектирована для работы с большими объемами данных (1000+ записей) с упором на безопасность ПДн и отзывчивость интерфейса.

## 🚀 Стек технологий

*   **Backend:** PHP 8.2+ (Framework: **CodeIgniter 4**)
*   **Frontend:** **Angular 17** (Standalone Components, Signals, HttpClient)
*   **Database:** **PostgreSQL** (с использованием JSONB, GIN-индексов и Soft Deletes)
*   **Styling:** Tailwind CSS + Shimmer Loading Effects

## 🏗️ Архитектурные особенности

### 1. Гибридная отрисовка (Hybrid Rendering)
Система использует PHP для отрисовки основного каркаса (Layout) и "шиммер-скелетонов". Это обеспечивает мгновенный LCP (Largest Contentful Paint). Основная логика реестра "оживает" через Angular-компоненты после загрузки JS.

### 2. Реактивное управление состоянием
Вместо классических Observables на фронтенде используются **Angular Signals**, что гарантирует точечное обновление DOM при фильтрации 1000+ записей без лишних перерисовок.

### 3. Безопасность и ПДн (GDPR/ФЗ-152 Ready)
*   **Минимизация данных:** Метод `findForRegistry` на бэкенде исключает передачу чувствительных полей (телефон, ИНН) в общий список.
*   **JSONB Storage:** Дополнительные данные благополучателей хранятся в гибком формате JSONB с поддержкой GIN-индексации для мгновенного поиска.
*   **Soft Delete:** Реализовано каскадное мягкое удаление для предотвращения потери истории операций.

### 4. Производительность БД
*   Индексация внешних ключей (Foreign Keys) и дат.
*   B-Tree индексы на ФИО для Live-поиска.
*   Отсутствие `ON DELETE CASCADE` в пользу `RESTRICT` для обеспечения целостности данных.

## 🛠️ Установка и запуск

1. **Бэкенд:**
   ```bash
   composer install
   php spark migrate:refresh --all -sed MainSeeder
   php spark serve
   ```
2. **Фронтенд:**
```bash
cd frontend
npm install
npx ng build --watch --configuration development
```
3. **База данных:**
```bash
Создайте БД diplodoc_db в PostgreSQL и укажите доступы в .env.
```
