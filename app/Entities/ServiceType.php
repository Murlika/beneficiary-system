<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ServiceType extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];

    public function getIcon()
    {
        return match($this->attributes['slug']) {
            'consultation' => '⚖️',
            'humanitarian' => '📦',
            'medical'      => '💊',
            default        => '🛠️',
        };
    }
}
