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
    public function getRegistry(array $filters = [], int $limit = 20, int $offset = 0) {
        $builder = $this->builder();
        
        $builder->select('services.*');
        $builder->select('beneficiaries.full_name as beneficiary_name, beneficiaries.type as beneficiary_type');
        $builder->select('service_types.title as service_title, service_types.slug as service_slug');
        
        $builder->join('beneficiaries', 'beneficiaries.id = services.beneficiary_id');
        $builder->join('service_types', 'service_types.id = services.type_id');

        // ТЗ п.1: Фильтрация/Поиск
        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('beneficiaries.full_name', $filters['search'], 'both', null, true)
                    ->orLike('service_types.title', $filters['search'], 'both', null, true)
                    ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $builder->where('services.status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('services.service_date >=', $filters['date_from']);
        }

        return $builder->orderBy('services.service_date', 'DESC')
                       ->limit($limit, $offset)
                       ->get()
                       ->getResultArray(); // Для API массив быстрее
    }
}
