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
        Schema::create('lead_distribution_campaign_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lead_distribution_campaign_id');

            $table->foreign('user_id', 'user_lead_distribution_campaign_user_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('lead_distribution_campaign_id', 'user_campaign_fk')
                ->references('id')
                ->on('lead_distribution_campaigns')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        //drop foreign keys
        Schema::table('lead_distribution_campaign_user', function (Blueprint $table) {
            $table->dropForeign('user_campaign_fk');
            $table->dropForeign('user_lead_distribution_campaign_user_id_foreign');
            
        });

        Schema::dropIfExists('lead_distribution_campaign_user');
    }
};
