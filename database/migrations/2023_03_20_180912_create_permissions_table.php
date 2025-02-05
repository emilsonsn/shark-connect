<?php

use App\Models\Permission;
use App\Models\User;
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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('permission');
            $table->timestamps();
        });

        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Permission::class);
            $table->foreignIdFor(User::class);
            $table->timestamps();

            $table->index('permission_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_user', function (Blueprint $table) {
            $table->dropIndex('permission_user_permission_id_index');
            $table->dropIndex('permission_user_user_id_index');
        });

        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permissions');

    }
};
