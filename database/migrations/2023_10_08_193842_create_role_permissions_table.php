<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auth_guard_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('auth_user_id')->nullable();
            $table->string('module')->nullable();
            $table->string('operation')->nullable();
            $table->string('route')->nullable();
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->string('route_name')->default(0);
            $table->integer('is_permit')->default(0);

            $table->foreign('auth_guard_id')
                ->on('auth_guards')
                ->references('id')
                ->nullOnDelete();

            $table->foreign('role_id')
                ->on('roles')
                ->references('id')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_permissions');
    }
};