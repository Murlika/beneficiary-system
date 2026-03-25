<?php

namespace App\Services;

use App\Models\ServiceModel;

class RegistryService
{
    protected $serviceModel;

    public function __construct()
    {
        $this->serviceModel = new ServiceModel();
    }

    /**
     * ТЗ п.1: Получение отфильтрованного реестра
     */
    public function getFilteredRegistry(array $params)
    {
        // Вытягиваем плоскую таблицу через модель, которую мы уже настроили
        // Добавляем логику: если в поиске пусто — отдаем последние 20 записей
        $limit  = $params['limit'] ?? 20;
        $offset = $params['offset'] ?? 0;
        
        return $this->serviceModel->getRegistry($params, $limit, $offset);
    }

    /**
     * Подготовка данных для Excel (ТЗ п.2)
     */
    public function getExportData(array $params)
    {
        // Тут мы вызовем ту же логику, но без лимитов
        return $this->serviceModel->getRegistry($params, 5000, 0);
    }
}
