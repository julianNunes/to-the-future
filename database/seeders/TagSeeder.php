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
                'name' => 'Casa',
                'user_id' => 1,
            ],
            [
                'name' => 'Festa',
                'user_id' => 1,
            ],
            [
                'name' => 'Farmacia',
                'user_id' => 1,
            ],
            [
                'name' => 'Saúde',
                'user_id' => 1,
            ],
            [
                'name' => 'Lazer',
                'user_id' => 1,
            ],
            [
                'name' => 'Mercado',
                'user_id' => 1,
            ],
            [
                'name' => 'Assinatura',
                'user_id' => 1,
            ],
            [
                'name' => 'Transporte',
                'user_id' => 1,
            ],
            [
                'name' => 'Uber',
                'user_id' => 1,
            ],
            [
                'name' => 'Viagem',
                'user_id' => 1,
            ],
            [
                'name' => 'Ifood',
                'user_id' => 1,
            ],
            [
                'name' => 'Profissinal',
                'user_id' => 1,
            ],
            [
                'name' => 'Estudos',
                'user_id' => 1,
            ],
            [
                'name' => 'Roupas',
                'user_id' => 1,
            ],
            [
                'name' => 'Alimentação',
                'user_id' => 1,
            ],
            [
                'name' => 'Supermercado',
                'user_id' => 1,
            ]
        ];

        collect($tags)->each(function ($tag) {
            Tag::create($tag);
        });

        $tags = [
            [
                'name' => 'Casa',
                'user_id' => 2,
            ],
            [
                'name' => 'Festa',
                'user_id' => 2,
            ],
            [
                'name' => 'Farmacia',
                'user_id' => 2,
            ],
            [
                'name' => 'Saúde',
                'user_id' => 2,
            ],
            [
                'name' => 'Lazer',
                'user_id' => 2,
            ],
            [
                'name' => 'Mercado',
                'user_id' => 2,
            ],
            [
                'name' => 'Assinatura',
                'user_id' => 2,
            ],
            [
                'name' => 'Transporte',
                'user_id' => 2,
            ],
            [
                'name' => 'Uber',
                'user_id' => 2,
            ],
            [
                'name' => 'Viagem',
                'user_id' => 2,
            ],
            [
                'name' => 'Ifood',
                'user_id' => 2,
            ],
            [
                'name' => 'Profissinal',
                'user_id' => 2,
            ],
            [
                'name' => 'Estudos',
                'user_id' => 2,
            ],
            [
                'name' => 'Roupas',
                'user_id' => 2,
            ],
            [
                'name' => 'Alimentação',
                'user_id' => 2,
            ],
            [
                'name' => 'Supermercado',
                'user_id' => 2,
            ]
        ];

        collect($tags)->each(function ($tag) {
            Tag::create($tag);
        });
    }
}
