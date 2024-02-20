<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Tag::count() > 0) {
            return;
        }

        $tags = [
            [
                'name' => 'CASA',
            ],
            [
                'name' => 'FESTA',
            ],
            [
                'name' => 'FARMACIA',
            ],
            [
                'name' => 'SAÚDE',
            ],
            [
                'name' => 'LAZER',
            ],
            [
                'name' => 'MERCADO',
            ],
            [
                'name' => 'ASSINATURA',
            ],
            [
                'name' => 'TRANSPORTE',
            ],
            [
                'name' => 'UBER',
            ],
            [
                'name' => 'VIAGEM',
            ],
            [
                'name' => 'IFOOD',
            ],
            [
                'name' => 'PROFISSINAL',
            ],
            [
                'name' => 'ESTUDOS',
            ],
            [
                'name' => 'ROUPAS',
            ],
            [
                'name' => 'ALIMENTAÇÃO',
            ],
            [
                'name' => 'SUPERMERCADO',
            ]
        ];

        collect($tags)->each(function ($tag) {
            Tag::create($tag);
        });
    }
}
