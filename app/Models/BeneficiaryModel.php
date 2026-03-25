<?php

namespace App\Models;

use CodeIgniter\Model;

class BeneficiaryModel extends Model
{
    protected $table            = 'beneficiaries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = \App\Entities\Beneficiary::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields = ['type', 'full_name', 'extra_data', 'deleted_at'];

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

    // ТЗ: Удобный поиск/автодополнение (для 1000+ записей)
    public function search(string $term, int $limit = 10) {
        return $this->like('full_name', $term, 'both', null, true)
                    ->orderBy('full_name', 'ASC')
                    ->findAll($limit);
    }

    /**
     * Получает данные для общего списка без чувствительной информации
     */
    public function findForRegistry(array $filters = [], int $limit = 20, int $offset = 0)
    {
        $builder = $this->builder();
        
        // Выбираем только публичные поля
        $builder->select('id, full_name, type, created_at');
 
        if (!empty($filters['search'])) {
            $builder->like('full_name', $filters['search'], 'both', null, true);
        }

        return $builder->orderBy('full_name', 'ASC')
                    ->limit($limit, $offset)
                    ->get()
                    ->getResultArray(); 
    }

}

