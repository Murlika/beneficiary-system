<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexesToBeneficiaries extends Migration
{
    public function up()
    {
        $this->db->query("DROP INDEX IF EXISTS idx_beneficiaries_full_name");

        // 🦕 Создаем мощный СОСТАВНОЙ индекс (ФИО + ID)
        // Это идеальное топливо для ORDER BY full_name, id LIMIT X OFFSET Y
        $this->db->query("CREATE INDEX idx_beneficiaries_name_id ON beneficiaries (full_name ASC, id ASC)");
        
        dino_log('info', 'DB', 'UPGRADE', "Индекс мутировал в составной! Пагинация теперь летит как раптор 🐾");
    }

    public function down()
    {
        $this->db->query("DROP INDEX IF EXISTS idx_beneficiaries_name_id");
        $this->db->query("CREATE INDEX idx_beneficiaries_full_name ON beneficiaries(full_name)");
    }
}
