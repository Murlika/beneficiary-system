<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceTypeModel extends Model
{
    protected $table            = 'service_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = \App\Entities\ServiceType::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields  = ['slug', 'title', 'description', 'deleted_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert = ['clearTitlesCache'];
    protected $beforeUpdate   = [];

    // Сбрасываем кэш при любом изменении (создание, апдейт, удаление)
    protected $afterUpdate = ['clearTitlesCache'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete = ['clearTitlesCache'];


       // Метод для получения списка имен с кэшем
    public function getCachedTitles(): string {
        $cacheKey = 'service_types_titles_list';
        
        if (! $titles = cache($cacheKey)) {
            $data = $this->select('title')->findAll();
            $titles = implode(', ', array_column($data, 'title'));
            // Сохраняем навечно
            cache()->save($cacheKey, $titles, 0); 
        }
        return $titles;
    }


public function getCachedList(): array // 🧬 Теперь возвращаем массив!
{
    $cacheKey = 'service_types_full_list';
    
    if (! $list = cache($cacheKey)) {
        // Берем ID и Title — это наш золотой стандарт для селектов
        $list = $this->select('id, title')
                     ->where('deleted_at IS NULL')
                     ->orderBy('title', 'ASC')
                     ->findAll();
                     
        // Сохраняем массив объектов в кэш
        cache()->save($cacheKey, $list, 3600); // 1 час (лучше не "навечно", вдруг добавишь новую услугу)
    }
    
    return $list;
}


    protected function clearTitlesCache(array $data) {
        cache()->delete('service_types_titles_list');
        return $data;
    }

}
