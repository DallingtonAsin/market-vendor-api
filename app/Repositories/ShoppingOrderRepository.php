<?php

namespace App\Repositories;

use App\Models\ShoppingOrder;
use App\Models\Customer;
use App\Models\User;
use Globals;
use Illuminate\Support\Facades\Gate;
use Auth;

class ShoppingOrderRepository{

   // property

   public $shopping_lists;

   // all shopping orders
   public function getShoppingOrders(){

   $this->shopping_lists = ShoppingOrder::orderBy('id', 'desc')->get();
    if(!empty($this->shopping_lists)){
       foreach($this->shopping_lists as $order){
          $customer = $this->getCustomerDetails($order->customer_id);
          $vendor = $this->getVendorDetails($order->vendor_id);
          $order->customer_name = $customer->first_name." ".$customer->last_name;
          $order->phone_number = $customer->phone_number;
          $order->vendor = $vendor->first_name." ".$vendor->last_name;
          $order->request_date = date("Y-m-d H:i A", strtotime($order->created_at));
       }
    }
    return $this->shopping_lists;
   }

 // pending shopping orders
   public function getPendingShoppingOrders(){

      $this->shopping_lists = ShoppingOrder::where('status', Globals::$SHOPPING_LIST_PENDING_STATUS)->orderBy('id', 'desc')->get();
      if(!empty($this->shopping_lists)){
         foreach($this->shopping_lists as $order){
            $customer = $this->getCustomerDetails($order->customer_id);
            $vendor = $this->getVendorDetails($order->vendor_id);
            $order->customer_name = $customer->first_name." ".$customer->last_name;
            $order->phone_number = $customer->phone_number;
            $order->vendor = $vendor->first_name." ".$vendor->last_name;
            $order->request_date = date("Y-m-d H:i A", strtotime($order->created_at));
         }
      }
      return $this->shopping_lists;
     }


     // processed shopping orders
     public function getProcessedShoppingOrders(){

      $this->shopping_lists = ShoppingOrder::where('status', Globals::$SHOPPING_LIST_PROCESSED_STATUS)->orderBy('id', 'desc')->get();
      if(!empty($this->shopping_lists)){
         foreach($this->shopping_lists as $order){
            $customer = $this->getCustomerDetails($order->customer_id);
            $vendor = $this->getVendorDetails($order->vendor_id);
            $order->customer_name = $customer->first_name." ".$customer->last_name;
            $order->phone_number = $customer->phone_number;
            $order->vendor = $vendor->first_name." ".$vendor->last_name;
            $order->request_date = date("Y-m-d H:i A", strtotime($order->created_at));
         }
      }
      return $this->shopping_lists;
     }



     // specific shopping orders
   public function getMyShoppingOrders($vendor_id){

      $this->shopping_lists = ShoppingOrder::where('vendor_id', $vendor_id)->orderBy('id', 'desc')->get();
       if(!empty($this->shopping_lists)){
          foreach($this->shopping_lists as $order){
             $customer = $this->getCustomerDetails($order->customer_id);
             $vendor = $this->getVendorDetails($order->vendor_id);
             $order->customer_name = $customer->first_name." ".$customer->last_name;
             $order->phone_number = $customer->phone_number;
             $order->vendor = $vendor->first_name." ".$vendor->last_name;
             $order->request_date = date("Y-m-d H:i A", strtotime($order->created_at));
          }
       }
       return $this->shopping_lists;
      }
   
    // pending shopping orders for specific vendor
      public function getMyPendingShoppingOrders($vendor_id){
   
         $this->shopping_lists = ShoppingOrder::where('vendor_id', $vendor_id)->where('status', Globals::$SHOPPING_LIST_PENDING_STATUS)->orderBy('id', 'desc')->get();
         if(!empty($this->shopping_lists)){
            foreach($this->shopping_lists as $order){
               $customer = $this->getCustomerDetails($order->customer_id);
               $vendor = $this->getVendorDetails($order->vendor_id);
               $order->customer_name = $customer->first_name." ".$customer->last_name;
               $order->phone_number = $customer->phone_number;
               $order->vendor = $vendor->first_name." ".$vendor->last_name;
               $order->request_date = date("Y-m-d H:i A", strtotime($order->created_at));
            }
         }
         return $this->shopping_lists;
        }
   
   
        // processed shopping orders for specific vendor
        public function getMyProcessedShoppingOrders($vendor_id){
   
         $this->shopping_lists = ShoppingOrder::where('vendor_id', $vendor_id)->where('status', Globals::$SHOPPING_LIST_PROCESSED_STATUS)->orderBy('id', 'desc')->get();
         if(!empty($this->shopping_lists)){
            foreach($this->shopping_lists as $order){
               $customer = $this->getCustomerDetails($order->customer_id);
               $vendor = $this->getVendorDetails($order->vendor_id);
               $order->customer_name = $customer->first_name." ".$customer->last_name;
               $order->phone_number = $customer->phone_number;
               $order->vendor = $vendor->first_name." ".$vendor->last_name;
               $order->request_date = date("Y-m-d H:i A", strtotime($order->created_at));
            }
         }
         return $this->shopping_lists;
        }

  // customer specific shopping orders
  public function getCustomerShoppingOrders($customer_id){

   $this->shopping_lists = ShoppingOrder::where('customer_id', $customer_id)->orderBy('id', 'desc')->get();
    if(!empty($this->shopping_lists)){
       foreach($this->shopping_lists as $order){
          $customer = $this->getCustomerDetails($order->customer_id);
          $vendor = $this->getVendorDetails($order->vendor_id);
          $order->customer_name = $customer->first_name." ".$customer->last_name;
          $order->phone_number = $customer->phone_number;
          $order->vendor = $vendor->first_name." ".$vendor->last_name;
          $order->request_date = date("Y-m-d H:i A", strtotime($order->created_at));
       }
    }
    return $this->shopping_lists;
   }

   // fetch shopping order details
  public function getShoppingOrderDetails($order_no){

   $this->shopping_lists = ShoppingOrder::where('order_no', $order_no)->orderBy('id', 'desc')->get();
    if(!empty($this->shopping_lists)){
       foreach($this->shopping_lists as $order){

          $customer = $this->getCustomerDetails($order->customer_id);
          $vendor = $this->getVendorDetails($order->vendor_id);
          $order->customer_name = $customer->first_name." ".$customer->last_name;
          $order->phone_number = $customer->phone_number;
          $order->vendor = $vendor->first_name." ".$vendor->last_name;
          $order->request_date = date("Y-m-d H:i A", strtotime($order->created_at));

       }
    }
    return $this->shopping_lists;
   }


   private function getCustomerDetails($id){
      $customer = null;
      if(Customer::where('id', $id)->exists()){
         $customer = Customer::find($id);
      }
      return $customer;
   }

   private function getVendorDetails($id){
      $user = null;
      if(User::where('id', $id)->exists()){
         $user = User::find($id);
      }
      return $user;
   }







}