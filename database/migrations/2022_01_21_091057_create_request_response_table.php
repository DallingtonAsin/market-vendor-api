<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_response', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->text('request');
                $table->text('response');
                $table->string('method');
                $table->string('url');
                $table->ipAddress('ip_address');
                $table->timestamp('date')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('request_response');
    }
}
