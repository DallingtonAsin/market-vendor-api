<?php

namespace App\Repositories;

use App\Models\ShoppingOrder;
use App\Models\Customer;


class ShoppingOrderRepository{

   // property

   public $shopping_lists;

   // Method
   public function getShoppingOrders(){

    $this->shopping_lists = ShoppingOrder::orderBy('id', 'desc')->get();
    if(!empty($this->shopping_lists)){
       foreach($this->shopping_lists as $order){
          $customer = $this->getCustomerName($order->customer_id);
          $order->customer_name = $customer->first_name." ".$customer->last_name;
          $order->phone_number = $customer->phone_number;
       }
    }
    return $this->shopping_lists;

   }

   private function getCustomerName($id){
      $customer = null;
      if(Customer::where('id', $id)->exists()){
         $customer = Customer::find($id);
      }
      return $customer;
   }






}