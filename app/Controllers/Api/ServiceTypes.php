<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class ServiceTypes extends ResourceController
{
    protected $modelName = 'App\Models\ServiceTypeModel'; 
    protected $format    = 'json';

    public function index()
    {
        // 🦕 Достаем все активные типы услуг
        $types = $this->model->getCachedList();
                             
        return $this->respond($types);
    }
}
