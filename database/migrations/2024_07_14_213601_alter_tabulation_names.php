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
        
        DB::table('tabulations')->where('id', 3)->update([
            "name" => 'Sem Saldo',
        ]);

        DB::table('tabulations')->where('id', 5)->update([
            "name" => 'Mensagem Enviada',
        ]);
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        DB::table('tabulations')->where('id', 3)->update([
            "name" => 'Reprovado',
        ]);

        DB::table('tabulations')->where('id', 5)->update([
            "name" => 'Negociacao',
        ]);
        
    }
};
