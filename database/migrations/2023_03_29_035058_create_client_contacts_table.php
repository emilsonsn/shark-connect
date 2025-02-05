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
        Schema::create('client_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->integer('ddd');
            $table->bigInteger('number');
            $table->timestamps();

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        //drop foreign keys
        Schema::table('client_contacts', function (Blueprint $table) {
            $table->dropForeign('client_contacts_client_id_foreign');
        });

        Schema::dropIfExists('client_contacts');
    }
};
