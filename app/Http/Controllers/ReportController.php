<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ShoppingList;
use App\Models\ActivityLog;
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
                "total_users" => User::count(),
                "total_shopping_requests" => ShoppingList::count(),
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
            $data = ActivityLog::all();
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


