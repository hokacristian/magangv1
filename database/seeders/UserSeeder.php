<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'username' => 'penerimaan_user',
                'email' => 'penerimaan@example.com',
                'password' => Hash::make('password123'),
                'role' => 'penerimaan',
            ],
            [
                'username' => 'pengeluaran_user',
                'email' => 'pengeluaran@example.com',
                'password' => Hash::make('password123'),
                'role' => 'pengeluaran',
            ],
            [
                'username' => 'katim_user',
                'email' => 'katim@example.com',
                'password' => Hash::make('password123'),
                'role' => 'katim',
            ],
            [
                'username' => 'direktur_user',
                'email' => 'direktur@example.com',
                'password' => Hash::make('password123'),
                'role' => 'direktur',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
