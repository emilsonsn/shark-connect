<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();
        \App\Models\Group::factory()->create([
            'name' => 'Admin',
            'is_active' => true,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Dev',
            'email' => 'Dev@hotmail.com',
            'login' => 'dev',
            'password' => bcrypt('3cnt9458'),
            'is_active' => true,
            'group_id' => 1,
        ]);
    }
}
