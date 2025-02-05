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
        Schema::table('lead_distribution_prospect', function (Blueprint $table) {
            $table->unsignedBigInteger('tabulation_id')->nullable();

            $table->foreign('tabulation_id', "tabulation_id_fk")
                ->references('id')
                ->on('tabulations')
                ->onDelete('set null');

            $table->index('tabulation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_distribution_prospect', function (Blueprint $table) {
            $table->dropIndex('lead_distribution_prospect_tabulation_id_index');
            $table->dropForeign('tabulation_id_fk');
            $table->dropColumn('tabulation_id');
        });
    }
};
