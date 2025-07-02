<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KonsumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run() {
    ([
        'no_identitas' => 'KNS001',
        'nama' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
        'saldo' => 50000,
    ]);
    
    \App\Models\Konsumen::create([
        'no_identitas' => 'KNS002',
        'nama' => 'Jane Smith',
        'email' => 'jane@example.com',
        'password' => bcrypt('password'),
        'saldo' => 25000,
    ]);
}   
}
