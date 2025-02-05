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
        Schema::create('group_lead_distribution_campaign', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('group_id')->nullable();

            $table->foreign('group_id', "group_id_fk")
                ->references('id')
                ->on('groups')
                ->onDelete('set null');

            $table->unsignedBigInteger('lead_distribution_campaign_id')->nullable();

            $table->foreign('lead_distribution_campaign_id', "lead_distribution_campaign_id_fk")
                ->references('id')
                ->on('lead_distribution_campaigns')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //drop foreign keys
        Schema::table('group_lead_distribution_campaign', function (Blueprint $table) {
            $table->dropForeign('group_id_fk');
            $table->dropForeign('lead_distribution_campaign_id_fk');
        });

        Schema::dropIfExists('group_lead_distribution_campaign');
    }
};
