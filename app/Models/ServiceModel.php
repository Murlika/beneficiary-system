<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table            = 'services';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Service::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields = [
        'beneficiary_id', 'type_id', 'service_date', 
        'status', 'amount', 'comment', 'deleted_at'
    ];

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
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * ТЗ п.1: Единый реестр (Плоская таблица с JOIN)
     */
public function getRegistry(array $filters = [], int $limit = 20, int $offset = 0) 
{
    $builder = $this->builder();
    
    // Выбираем конкретные поля, чтобы не тянуть мусор
    $builder->select('services.id, services.service_date, services.amount, services.status');
    $builder->select('beneficiaries.full_name as beneficiary_name, beneficiaries.type as beneficiary_type');
    $builder->select('service_types.title as service_title, service_types.slug as service_slug');
    
    $builder->join('beneficiaries', 'beneficiaries.id = services.beneficiary_id');
    $builder->join('service_types', 'service_types.id = services.type_id');

    // ДИНАМИЧЕСКАЯ СОРТИРОВКА
    $sortField = $filters['sort'] ?? 'service_date';
    $sortOrder = $filters['order'] ?? 'DESC';
    
    // Валидация полей, чтобы не прокинуть SQL-инъекцию в orderBy
    $allowedSort = ['service_date', 'amount', 'status', 'beneficiary_name'];
    if (!in_array($sortField, $allowedSort)) {
        $sortField = 'services.service_date';
    }

    // Поиск (Fulltext-like)
    if (!empty($filters['search'])) {
        $builder->groupStart()
                ->like('beneficiaries.full_name', $filters['search'])
                ->orLike('service_types.title', $filters['search'])
                ->groupEnd();
    }

    // Лайфхак: Маппинг фильтров (меньше IF-ов)
    $exactMatches = [
        'status'           => 'services.status',
        'beneficiary_type' => 'beneficiaries.type',
        'date_exact'       => 'DATE(services.service_date)'
    ];

    foreach ($exactMatches as $key => $column) {
        if (!empty($filters[$key])) {
            $builder->where($column, $filters[$key]);
        }
    }

    // Диапазоны
    if (!empty($filters['amount_min'])) $builder->where('services.amount >=', $filters['amount_min']);
    if (!empty($filters['amount_max'])) $builder->where('services.amount <=', $filters['amount_max']);

    
    $totalCount = (clone $builder)->countAllResults();
    
    $result = $builder->orderBy($sortField, $sortOrder)
                   ->get($limit, $offset) // limit/offset можно сразу сюда
                   ->getResultArray();

    if (empty($result) && !empty($filters)) {
        dino_log('debug', 'MODEL', 'EMPTY_REGISTRY', "Поиск не дал результатов", ['filters' => $filters]);
    }
                   
        return [
            'data'  => $result,
            'total' => $totalCount // Возвращаем для Angular Paginator
        ];
}

}
