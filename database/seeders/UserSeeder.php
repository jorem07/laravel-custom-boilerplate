<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Silber\Bouncer\Database\Models;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'password123';

        $users = [
            ['id' => 1, 'fullname' => 'Maria Santos', 'email' => 'maria.santos@pao.gov.ph', 'assignment' => 'Admin', 'status' => 'Active', 'is_online' => true, 'role' => 'admin'],
            ['id' => 2, 'fullname' => 'Juan Dela Cruz', 'email' => 'juan.dela.cruz@pao.gov.ph', 'assignment' => 'Counter 1', 'status' => 'Active', 'is_online' => true, 'role' => 'officer'],
            ['id' => 3, 'fullname' => 'Pedro Reyes', 'email' => 'pedro.reyes@pao.gov.ph', 'assignment' => 'Counter 2', 'status' => 'Active', 'is_online' => true, 'role' => 'officer'],
            ['id' => 4, 'fullname' => 'Ana Garcia', 'email' => 'ana.garcia@pao.gov.ph', 'assignment' => 'Counter 3', 'status' => 'Active', 'is_online' => true, 'role' => 'officer'],
            ['id' => 5, 'fullname' => 'Ramon Cruz', 'email' => 'ramon.cruz@pao.gov.ph', 'assignment' => 'Counter 6', 'status' => 'Active', 'is_online' => true, 'role' => 'officer'],
            ['id' => 6, 'fullname' => 'Sophie Lim', 'email' => 'sophic.lim@pao.gov.ph', 'assignment' => 'Counter 7', 'status' => 'Inactive', 'is_online' => false, 'role' => 'officer'],
            ['id' => 7, 'fullname' => 'Jose Ramirez', 'email' => 'jose.ramirez@pao.gov.ph', 'assignment' => 'Counter 4', 'status' => 'Active', 'is_online' => true, 'role' => 'officer'],
            ['id' => 8, 'fullname' => 'David Lee', 'email' => 'david.lee@pao.gov.ph', 'assignment' => 'Counter 5', 'status' => 'Active', 'is_online' => true, 'role' => 'officer'],
            ['id' => 9, 'fullname' => 'Jane Doe', 'email' => 'jane.doe@pao.gov.ph', 'assignment' => 'Counter 8', 'status' => 'Active', 'is_online' => true, 'role' => 'officer'],
            ['id' => 10, 'fullname' => 'John Smith', 'email' => 'john.smith@pao.gov.ph', 'assignment' => 'Counter 9', 'status' => 'Active', 'is_online' => true, 'role' => 'officer'],
            ['id' => 11, 'fullname' => 'Grace Tan', 'email' => 'grace.tan@pao.gov.ph', 'assignment' => 'Counter 10', 'status' => 'Active', 'is_online' => true, 'role' => 'officer'],
            ['id' => 12, 'fullname' => 'Mark Co', 'email' => 'mark.co@pao.gov.ph', 'assignment' => 'Unassigned', 'status' => 'Active', 'is_online' => false, 'role' => 'officer'],
            ['id' => 13, 'fullname' => 'Carlo Reyes', 'email' => 'carlo.reyes@pao.gov.ph', 'assignment' => 'Unassigned', 'status' => 'Active', 'is_online' => false, 'role' => 'officer'],
            ['id' => 14, 'fullname' => 'Elena Flores', 'email' => 'elena.flores@pao.gov.ph', 'assignment' => 'Unassigned', 'status' => 'Active', 'is_online' => false, 'role' => 'officer'],
            ['id' => 15, 'fullname' => 'Roberto Aquino', 'email' => 'roberto.aquino@pao.gov.ph', 'assignment' => 'Unassigned', 'status' => 'Active', 'is_online' => false, 'role' => 'officer'],
            ['id' => 16, 'fullname' => 'Teresa Villanueva', 'email' => 'teresa.villanueva@pao.gov.ph', 'assignment' => 'Unassigned', 'status' => 'Active', 'is_online' => false, 'role' => 'officer'],
            ['id' => 17, 'fullname' => 'Antonio Bautista', 'email' => 'antonio.bautista@pao.gov.ph', 'assignment' => 'Unassigned', 'status' => 'Active', 'is_online' => false, 'role' => 'officer'],
            ['id' => 18, 'fullname' => 'Liza Magsaysay', 'email' => 'liza.magsaysay@pao.gov.ph', 'assignment' => 'Unassigned', 'status' => 'Active', 'is_online' => false, 'role' => 'officer'],
            ['id' => 19, 'fullname' => 'Fernando Dizon', 'email' => 'fernando.dizon@pao.gov.ph', 'assignment' => 'Unassigned', 'status' => 'Active', 'is_online' => false, 'role' => 'officer'],
            ['id' => 20, 'fullname' => 'Carmen Lazaro', 'email' => 'carmen.lazaro@pao.gov.ph', 'assignment' => 'Unassigned', 'status' => 'Active', 'is_online' => false, 'role' => 'officer'],
            ['id' => 21, 'fullname' => 'Ricardo Mendoza', 'email' => 'ricardo.mendoza@pao.gov.ph', 'assignment' => 'Unassigned', 'status' => 'Active', 'is_online' => false, 'role' => 'officer'],
            ['id' => 22, 'fullname' => 'Angela Torres', 'email' => 'angela.torres@pao.gov.ph', 'assignment' => 'Unassigned', 'status' => 'Active', 'is_online' => false, 'role' => 'officer'],
            ['id' => 23, 'fullname' => 'Admin Staff', 'email' => 'admin@example.com', 'assignment' => 'Admin', 'status' => 'Active', 'is_online' => false, 'role' => 'admin'],
            ['id' => 24, 'fullname' => 'Officer Staff', 'email' => 'officer@example.com', 'assignment' => 'Counter 11', 'status' => 'Active', 'is_online' => false, 'role' => 'officer'],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            $userData['password'] = $password;
            $userData['allow_login'] = true;

            $user = User::updateOrCreate(['id' => $userData['id']], $userData);
            $user->assign($role);
        }
    }
}
