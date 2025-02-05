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
        Schema::create('lead_distribution_prospect', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_distribution_campaign_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->double("margin");
            $table->string("convenant");
            $table->string("organ");
            $table->timestamps();

            $table->foreign('lead_distribution_campaign_id', "lead_distribution_campaign_fk")
                ->references('id')
                ->on('lead_distribution_campaigns')
                ->onDelete('set null');
            
            $table->foreign('client_id', "client_id_fk")
                ->references('id')
                ->on('clients')
                ->onDelete('set null');
            
            $table->foreign('user_id', "user_id_fk")
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        //drop foreign keys
        Schema::table('lead_distribution_prospect', function (Blueprint $table) {
            $table->dropForeign('lead_distribution_campaign_fk');
            $table->dropForeign('client_id_fk');
            $table->dropForeign('user_id_fk');
        });

        Schema::dropIfExists('lead_distribution_prospect');
    }
};
