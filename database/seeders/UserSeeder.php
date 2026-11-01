<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public const DEFAULT_PASSWORD = 'tirtawarga123';

    public function run(): void
    {
        $users = [
            ['name' => 'Admin BPBD Kota Bandung', 'email' => 'bpbd@tirtawarga.com',          'role' => 'bpbd', 'no_hp' => '081200000001'],
            ['name' => 'Ketua RT Antapani Kidul', 'email' => 'rtantapani@tirtawarga.com',    'role' => 'rt',   'no_hp' => '081200000005'],
            ['name' => 'Ketua RT Babakan Sari',   'email' => 'rtbabakansari@tirtawarga.com', 'role' => 'rt',   'no_hp' => '081200000003'],
        ];

        foreach ($users as $data) {
            User::firstOrNew(['email' => $data['email']])
                ->forceFill([
                    'name'              => $data['name'],
                    'role'              => $data['role'],
                    'no_hp'             => $data['no_hp'],
                    'password'          => Hash::make(self::DEFAULT_PASSWORD),
                    'email_verified_at' => now(),
                ])
                ->save();
        }
    }
}
