<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitDatabase extends Migration
{
    public function up()
    {
       $sql = "
        CREATE TABLE service_types (
            id SERIAL PRIMARY KEY,
            slug VARCHAR(50) UNIQUE NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT NOW(),
            deleted_at TIMESTAMP NULL
        );

        CREATE TABLE beneficiaries (
            id SERIAL PRIMARY KEY,
            type VARCHAR(20) CHECK (type IN ('person', 'company')),
            full_name VARCHAR(255) NOT NULL,
            extra_data JSONB DEFAULT '{}', -- ИНН, ОГРН, Паспорт тут
            created_at TIMESTAMP DEFAULT NOW(),
            deleted_at TIMESTAMP NULL
                );
                -- Ускоряем поиск по ФИО (ТЗ п.1)
        CREATE INDEX idx_beneficiaries_full_name ON beneficiaries(full_name);
        -- Ускоряем фильтр по типу (физ/юр лицо)
        CREATE INDEX idx_beneficiaries_type ON beneficiaries(type);
        -- Добавляем GIN индекс для быстрого поиска внутри JSON (ТЗ п.1)
        CREATE INDEX idx_beneficiaries_extra_data ON beneficiaries USING GIN (extra_data);
    
    CREATE TABLE services (
        id SERIAL PRIMARY KEY,
        beneficiary_id INT REFERENCES beneficiaries(id) ON DELETE RESTRICT, 
        type_id INT REFERENCES service_types(id) ON DELETE RESTRICT,
        service_date DATE NOT NULL DEFAULT CURRENT_DATE,
        status VARCHAR(20) DEFAULT 'new'
            CHECK (status IN ('new', 'pending', 'in_progress', 'completed', 'canceled')),
        amount DECIMAL(12, 2) DEFAULT 0.00,
        comment TEXT,
        created_at TIMESTAMP DEFAULT NOW(),
        deleted_at TIMESTAMP NULL
    );
    -- Внешние ключи (Foreign Keys)  чтобы JOIN не тормозил
    CREATE INDEX idx_services_beneficiary_id ON services(beneficiary_id);
    CREATE INDEX idx_services_type_id ON services(type_id);

    -- Индексы для ускорения поиска (ТЗ п.1)
    CREATE INDEX idx_services_date ON services(service_date);
    CREATE INDEX idx_services_status ON services(status);

    ";
    $this->db->query($sql);
    }

    public function down()
    {
           $this->db->query("DROP TABLE IF EXISTS services;");
    $this->db->query("DROP TABLE IF EXISTS beneficiaries;");
    $this->db->query("DROP TABLE IF EXISTS service_types;");
    }
}
