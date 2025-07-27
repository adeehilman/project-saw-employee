<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class PemimpinPerusahaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Pemimpin Perusahaan user
        User::create([
            'name' => 'Direktur Utama',
            'email' => 'direktur@perusahaan.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'Pemimpin Perusahaan',
            'remember_token' => Str::random(10),
        ]);

        // Create additional company leader if needed
        User::create([
            'name' => 'Manajer Operasional',
            'email' => 'manajer@perusahaan.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'Pemimpin Perusahaan',
            'remember_token' => Str::random(10),
        ]);

        // Create HR Manager with approval rights
        User::create([
            'name' => 'Manajer SDM',
            'email' => 'hr@perusahaan.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'Pemimpin Perusahaan',
            'remember_token' => Str::random(10),
        ]);
    }
}
