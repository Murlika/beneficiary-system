<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ServiceTypesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'slug'        => 'consultation',
                'title'       => 'Юридическая консультация',
                'description' => 'Первичный прием и разъяснение прав благополучателя.'
            ],
            [
                'slug'        => 'humanitarian',
                'title'       => 'Гуманитарная помощь',
                'description' => 'Выдача продуктовых наборов или вещей первой необходимости.'
            ],
            [
                'slug'        => 'psychological',
                'title'       => 'Психологическая поддержка',
                'description' => 'Индивидуальные сессии с психологом фонда.'
            ],
            [
                'slug'        => 'education',
                'title'       => 'Образовательные курсы',
                'description' => 'Обучение профессиональным навыкам или грамотности.'
            ],
            [
                'slug'        => 'material_aid',
                'title'       => 'Денежная выплата',
                'description' => 'Целевая материальная помощь на спец. нужды.'
            ],
        ];
        
        $this->db->table('service_types')->insertBatch($data);
    }
}
