<?php

use App\Models\LeadDistributionCampaign;
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
        Schema::create('campaign_processing_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Inserting the default statuses
        DB::table('campaign_processing_statuses')->insert([
            ['name' => 'Aguardando processamento'], // [1]
            ['name' => 'Em processamento'],
            ['name' => 'Finalizado'],
            ['name' => 'Cancelado'],
        ]);

        Schema::table('lead_distribution_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_processing_status_id')
                ->nullable()
                ->default(1);

            $table->foreign('campaign_processing_status_id', "campaign_processing_status_id_fk")
                ->references('id')
                ->on('campaign_processing_statuses')
                ->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_processing_statuses');

        Schema::table('lead_distribution_campaigns', function (Blueprint $table) {
            $table->dropForeign("campaign_processing_status_id_fk");
        });

        Schema::table('lead_distribution_campaigns', function (Blueprint $table) {
            $table->dropColumn('campaign_processing_status_id');
        });

    }
};
