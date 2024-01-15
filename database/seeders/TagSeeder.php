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
        Tag::create(
            [
                'name' => '',
                'user_id' => 1
            ],
        );
    }
}
