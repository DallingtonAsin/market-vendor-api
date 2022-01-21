<?php

namespace App\Repositories;

use App\Models\Good;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class GoodsRepository{

   // property

   public $goods;

   // Method
   public function getGoods(){

    $this->goods = Good::orderBy('id', 'desc')->get();
    if(!empty($this->goods)){
        foreach($this->goods as $item){
            if(!empty($item->photo)){
                $item->photo =  Storage::disk('public')->url($item->photo);
            }
            $vendor = $this->getVendorDetails($item->vendor_id);
            $item->vendor = $vendor->first_name." ".$vendor->last_name;
        }
    }
    return $this->goods;

   }

   private function getVendorDetails($id){
    $user = null;
    if(User::where('id', $id)->exists()){
       $user = User::find($id);
    }
    return $user;
 }







}