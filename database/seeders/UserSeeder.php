<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'id' => 2,
            'name' => 'juni',
            'email' => 'juni@gmail.com',
            'role' => 'admin',
            'email_verified_at' => null,
            'password' => Hash::make('12345678'), // hashed version of 12345678
            'saldo' => 0,
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
