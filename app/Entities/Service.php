<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Service extends Entity
{
        // --- Константы статусов (Flow) ---
    public const STATUS_NEW         = 'new';
    public const STATUS_PENDING     = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_CANCELED    = 'canceled';
    
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    // Авто-каст суммы в число, чтобы Angular не ругался на строку
    protected $casts = [
        'amount' => 'float',
    ];

   /**
     * Возвращает список всех статусов для выпадающих списков (ТЗ п.1 Форма)
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_NEW         => '🆕 Новая',
            self::STATUS_PENDING     => '⏳ В ожидании',
            self::STATUS_IN_PROGRESS => '🛠️ В работе',
            self::STATUS_COMPLETED   => '✅ Завершена',
            self::STATUS_CANCELED    => '❌ Отменена',
        ];
    }

    /**
     * Хелпер для проверки: можно ли редактировать заявку?
     */
    public function canBeEdited(): bool
    {
        // Например, нельзя править уже завершенные или отмененные
        return !in_array($this->attributes['status'], [self::STATUS_COMPLETED, self::STATUS_CANCELED]);
    }

}
