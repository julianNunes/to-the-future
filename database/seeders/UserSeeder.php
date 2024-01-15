<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::count() > 0) {
            return;
        }
        User::create(
            [
                'name' => 'Julian Nunes',
                'email' => 'eu_dinovu@hotmail.com',
                'password' => Hash::make('password')
            ],
            [
                'name' => 'Damares Alves dos Santos',
                'email' => 'damaresa747@gmail.com',
                'password' => Hash::make('password')
            ],
            [
                'name' => 'Abayomi Alves Artur',
                'email' => 'abayomi@mail.com',
                'password' => Hash::make('password')
            ]
        );
    }
}
