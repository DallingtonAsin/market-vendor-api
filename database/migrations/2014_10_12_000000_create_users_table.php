<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('username')->unique();
                $table->string('email')->nullable();
                $table->unsignedBigInteger('role');
                $table->string('phone_number')->unique();
                $table->string('address')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('image')->nullable();
                $table->string('password');
                $table->boolean('is_active')->default(true);
                $table->boolean('is_deleted')->default(false);
                $table->string('deleted_by')->nullable();
                $table->rememberToken()->nullable();
                $table->timestamps();
                $table->foreign('role')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('users');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}