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
        Schema::table('lead_distribution_campaign_user', function (Blueprint $table) {
            $table->integer('limit')
                ->nullable()
                ->default(400)
                ->after('lead_distribution_campaign_id');

            $table->integer('caught_today')
                ->nullable()
                ->default(0)
                ->after('limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_distribution_campaign_user', function (Blueprint $table) {
            $table->dropColumn('limit');
            $table->dropColumn('caught_today');
        });
    }
};
