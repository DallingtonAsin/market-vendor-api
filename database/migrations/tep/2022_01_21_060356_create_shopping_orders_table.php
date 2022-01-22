<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateShoppingOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopping_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->integer('vendor_id')->unsigned();
            $table->unsignedBigInteger('customer_id');
            $table->json('items');
            $table->double('quantity')->default(1);
            $table->double('amount');
            $table->string('address');
            $table->string('status')->default('PENDING');
            $table->date('delivered_on')->nullable();
            $table->boolean('is_deleted')->default(0);
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
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
        
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('shopping_orders');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
