<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('tabulations')->insert([
            ['id' => 1, 'name' => 'Aberto'],
            ['id' => 2, 'name' => 'Fechado'],
            ['id' => 3, 'name' => 'Reprovado'],
            ['id' => 4, 'name' => 'Sem interesse'],
            ['id' => 5, 'name' => 'Negociacao'],
            ['id' => 6, 'name' => 'Sem WhatApp'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('tabulations')->delete([1, 2, 3, 4, 5]);
    }
};
