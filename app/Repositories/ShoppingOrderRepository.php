<?php

namespace App\Repositories;

use App\Models\ShoppingOrder;

class ShoppingOrderRepository{

   // property

   public $shopping_lists;

   // Method
   public function getShoppingOrders(){

    $this->shopping_lists = ShoppingOrder::orderBy('id', 'desc')->get();
    return $this->shopping_lists;

   }






}