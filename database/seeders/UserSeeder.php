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
                'name' => 'User Penerimaan',
                'email' => 'penerimaan@example.com',
                'password' => Hash::make('password123'),
                'role' => 'penerimaan',
            ],
            [
                'username' => 'pengeluaran_user',
                'name' => 'User Pengeluaran',
                'email' => 'pengeluaran@example.com',
                'password' => Hash::make('password123'),
                'role' => 'pengeluaran',
            ],
            [
                'username' => 'katim_user',
                'name' => 'User Katim',
                'email' => 'katim@example.com',
                'password' => Hash::make('password123'),
                'role' => 'katim',
            ],
            [
                'username' => 'direktur_user',
                'name' => 'User Direktur',
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
