<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use Config\Services;

class Beneficiary extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'extra_data' => 'json-array',
    ];

    // Красивое отображение типа для фронта
    public function getTypeLabel(): string {
        return $this->attributes['type'] === 'person' ? '👤 Физлицо' : '🏢 Юрлицо';
    }

        // Сеттер: Шифруем телефон/паспорт перед сохранением
    public function setExtraData(array $data) {
        $encrypter = Services::encrypter();
        if (isset($data['phone'])) {
            $data['phone_encrypted'] = base64_encode($encrypter->encrypt($data['phone']));
            unset($data['phone']); // Оригинал не храним
        }
        $this->attributes['extra_data'] = json_encode($data);
        return $this;
    }

    public function getSafePhone(): string {
        $data = json_decode($this->attributes['extra_data'], true);
        // Достаем, дешифруем и маскируем: +7 (999) ***-44-55
        return preg_replace('/(\d{3})\d{3}(\d{2})(\d{2})/', '$1***$2$3', $data['phone'] ?? '');
    }
    
}
