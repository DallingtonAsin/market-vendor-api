<?php

namespace App\Repositories;

use App\Models\ShoppingList;

class ShoppingListRepository{

   // property

   public $shopping_lists;

   // Method
   public function getShoppingLists(){

    $this->shopping_lists = ShoppingList::orderBy('id', 'desc')->get();
    return $this->shopping_lists;

   }






}