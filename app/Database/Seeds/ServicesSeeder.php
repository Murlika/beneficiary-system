<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Entities\Service as ServiceEntity;

class ServicesSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create('ru_RU');

        $beneficiaryIds = $this->db->table('beneficiaries')->select('id')->get()->getResultArray();
        $typeIds        = $this->db->table('service_types')->select('id')->get()->getResultArray();

        if (empty($beneficiaryIds) || empty($typeIds)) {
            echo "Бро, сначала заполни людей и типы услуг!";
            return;
        }

        $statuses = [
            ServiceEntity::STATUS_NEW,
            ServiceEntity::STATUS_PENDING,
            ServiceEntity::STATUS_IN_PROGRESS,
            ServiceEntity::STATUS_COMPLETED,
            ServiceEntity::STATUS_CANCELED
        ];

        $data = [];
        for ($i = 0; $i < 200; $i++) {
            $data[] = [
                'beneficiary_id' => $faker->randomElement($beneficiaryIds)['id'],
                'type_id'        => $faker->randomElement($typeIds)['id'],
                'service_date'   => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                'status'         => $faker->randomElement($statuses),
                'amount'         => $faker->randomFloat(2, 500, 15000), // От 500 до 15к
                'comment'        => $faker->boolean(50) ? $faker->sentence() : null,
                'created_at'     => date('Y-m-d H:i:s'),
            ];

            // Вставляем пачками, чтобы 8ГБ RAM не грустить
            if (count($data) >= 50) {
                $this->db->table('services')->insertBatch($data);
                $data = [];
            }
        }
    }
}
