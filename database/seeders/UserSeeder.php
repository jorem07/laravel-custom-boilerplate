<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        $users = [
            [
                'id' => 1,
                'first_name' => 'System',
                'last_name' => 'Admin',
                'middle_name' => null,
                'email' => 'admin@example.com',
                'status' => true,
                'allow_login' => true,
                'role' => 'admin',
            ]

            // [
            //     'id' => 2,
            //     'first_name' => 'Juan',
            //     'last_name' => 'Dela Cruz',
            //     'middle_name' => null,
            //     'email' => 'officer1@example.com',
            //     'status' => true,
            //     'allow_login' => true,
            //     'role' => 'officer',
            // ],
            // [
            //     'id' => 3,
            //     'first_name' => 'Maria',
            //     'last_name' => 'Santos',
            //     'middle_name' => null,
            //     'email' => 'officer2@example.com',
            //     'status' => true,
            //     'allow_login' => true,
            //     'role' => 'officer',
            // ],
            // [
            //     'id' => 4,
            //     'first_name' => 'Pedro',
            //     'last_name' => 'Penduko',
            //     'middle_name' => null,
            //     'email' => 'officer3@example.com',
            //     'status' => true,
            //     'allow_login' => true,
            //     'role' => 'officer',
            // ],
            // [
            //     'id' => 5,
            //     'first_name' => 'Ana',
            //     'last_name' => 'Reyes',
            //     'middle_name' => null,
            //     'email' => 'officer4@example.com',
            //     'status' => true,
            //     'allow_login' => true,
            //     'role' => 'officer',
            // ],
            // [
            //     'id' => 6,
            //     'first_name' => 'Mark',
            //     'last_name' => 'Cruz',
            //     'middle_name' => null,
            //     'email' => 'officer5@example.com',
            //     'status' => true,
            //     'allow_login' => true,
            //     'role' => 'officer',
            // ],
            // [
            //     'id' => 7,
            //     'first_name' => 'Elena',
            //     'last_name' => 'Torralba',
            //     'middle_name' => null,
            //     'email' => 'officer6@example.com',
            //     'status' => true,
            //     'allow_login' => true,
            //     'role' => 'officer',
            // ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            $userData['password'] = $password;

            $user = User::updateOrCreate(['id' => $userData['id']], $userData);
            $user->assign($role);
        }

        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            $seq = \Illuminate\Support\Facades\DB::selectOne("SELECT pg_get_serial_sequence('users', 'id') as seq");
            if ($seq && $seq->seq) {
                $maxId = \Illuminate\Support\Facades\DB::table('users')->max('id') ?? 0;
                \Illuminate\Support\Facades\DB::statement("SELECT setval('{$seq->seq}', " . ($maxId + 1) . ", false)");
            }
        }
    }
}


