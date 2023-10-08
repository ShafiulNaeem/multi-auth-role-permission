<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auth_guard_id')->nullable();
            $table->string('name');
            $table->boolean('is_admin')->default(false);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('auth_guard_id')
                ->on('auth_guards')
                ->references('id')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
};