<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Role;


class VendorRepository{

   // property

   public $vendors = [];

   // Method
   public function getVendors(){

    $users = User::orderBy('id', 'desc')->get();
    if(count((array)$users) > 0){
        foreach($users as $user){
            if($user->role == $this->getVendorRoleId()){
                array_push($this->vendors, $user);
            }
        }
    }
    return $this->vendors;

   }

   public function getVendorDetails($vendor_id){
       try{

          $user = User::find($vendor_id);
          return $user;

       }catch(\Exception $ex){
           throw $ex;
       }
   }

   private function getVendorRoleId(){
       $role_id = Role::where('is_admin', 0)->value('id');
       return $role_id;
   }

   






}