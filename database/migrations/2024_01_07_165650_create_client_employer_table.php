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
        Schema::create('client_employers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->integer('especie')->nullable();
            $table->string('uf')->nullable();
            $table->string('matricula')->nullable();
            $table->string('convenio')->nullable();
            $table->string('orgão')->nullable();
            $table->timestamps();

            $table->foreign('client_id', "client_employer_id_fk")
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
        Schema::table('client_employers', function (Blueprint $table) {
            $table->dropForeign('client_employer_id_fk');
        });

        Schema::dropIfExists('client_employers');
    }
};
