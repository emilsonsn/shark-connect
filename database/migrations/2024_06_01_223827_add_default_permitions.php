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
        DB::table('permissions')->insert([
            ['permission' => 'manage-campaigns'],
            ['permission' => 'manage-users'],
            ['permission' => 'manage-groups'],
            ['permission' => 'list-count-leads-taken-by-subordinates'],
            ['permission' => 'list-count-leads-taken-by-all'],
            ['permission' => 'monthly-report'],
            ['permission' => 'configurate'],
            ['permission' => 'view-campaigns'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->truncate();
    }
};
