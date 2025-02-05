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
        Schema::create('lead_distribution_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->boolean('status');
            $table->integer('total');
            $table->integer("remaining");
            $table->integer('percentage_by_user')->default(100);

            $table->string('batch_id')->nullable();

            $table->index('batch_id');

            $table->foreign('batch_id')
                ->references('id')
                ->on('job_batches')
                ->onDelete('cascade');

            $table->integer('count_recycle')->default(0);
            $table->dateTime('last_recycle_date')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::table('lead_distribution_campaigns', function (Blueprint $table) {
            $table->dropIndex('lead_distribution_campaigns_status_index');

            $table->dropForeign(['batch_id']);
            $table->dropIndex(['batch_id']);
            $table->dropColumn('batch_id');
        });

        Schema::dropIfExists('lead_distribution_campaigns');
    }
};
