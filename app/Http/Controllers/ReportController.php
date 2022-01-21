<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ShoppingOrder;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Role;
use Globals;

class ReportController extends Controller
{
    
    public $response = [];
    
    
    public function __constructor(){
        $this->response = new ApiResponse();
    }
    

    public function index(){
        try{
            $data = array(
                "total_vendors" => User::count(),
                "total_logs" => ActivityLog::count(),
                "total_roles" => Role::count(),
                "total_customers" => Customer::count(),
                "total_orders" => ShoppingOrder::count(),
                "total_revenue" => ShoppingOrder::sum('amount'),
                "total_pending_shopping_orders" => ShoppingOrder::where('status', Globals::$SHOPPING_LIST_PENDING_STATUS)->count(),
                "total_processed_shopping_orders" => ShoppingOrder::where('status', Globals::$SHOPPING_LIST_PROCESSED_STATUS)->count(),
            );
            $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
            $this->response['message'] = 'Data Found';
            $this->response['data'] = $data;
        }catch(\Exception $ex){
            $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
            $this->response['message'] = $ex->getMessage();
        }
        return response()->json($this->response, 200);
    }

    public function fetchLogs(){
        try{
            $data = ActivityLog::orderBy('id', 'desc')->get();
            $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
            $this->response['message'] = 'Data Found';
            $this->response['data'] = $data;
        }catch(\Exception $ex){
            $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
            $this->response['message'] = $ex->getMessage();
        }
        return response()->json($this->response, 200);
    }




}


