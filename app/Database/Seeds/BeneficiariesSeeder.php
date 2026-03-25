<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BeneficiariesSeeder extends Seeder
{

public function run() {
    $faker = \Faker\Factory::create('ru_RU');
    $data  = [];

    for ($i = 0; $i < 1050; $i++) {
        $isPerson = $faker->boolean(70); // 70% физлиц
        $data[] = [
            'type'       => $isPerson ? 'person' : 'company',
            'full_name'  => $isPerson ? $faker->name : $faker->company,
            'extra_data' => json_encode([
                'inn'   => $faker->inn,
                'phone' => $faker->phoneNumber,
                'email' => $faker->email
            ]),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if (count($data) >= 100) {
            $this->db->table('beneficiaries')->insertBatch($data);
            $data = [];
        }
    }
}

}
