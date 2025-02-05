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
        Schema::create('lead_distribution_user_configs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->unsignedBigInteger('current_campaign_id')
                ->nullable();

            $table->foreign('current_campaign_id')
                ->references('id')
                ->on('lead_distribution_campaigns');

            $table->integer('limit_leads')->default(500);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        //drop foreign keys
        Schema::table('lead_distribution_user_configs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->dropForeign(['current_campaign_id']);
            $table->dropColumn('current_campaign_id');
        });

        Schema::dropIfExists('lead_distribution_user_configs');
    }
};
