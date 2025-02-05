<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leads_taken_by_month_report', function (Blueprint $table) {
            $table->id();
            $table->integer('day');
            $table->integer('month');
            $table->integer('year');
            $table->integer('total');
            $table->integer('user_id');
            $table->timestamps();

            $table->index(['month', 'year', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads_taken_by_month_report', function (Blueprint $table) {
            $table->dropIndex('leads_taken_by_month_report_month_year_user_id_index');
        });

        Schema::dropIfExists('leads_taken_by_month_report');
    }
};
