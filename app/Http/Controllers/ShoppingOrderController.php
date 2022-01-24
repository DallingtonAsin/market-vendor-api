<?php

namespace App\Http\Controllers;

use App\Models\ShoppingOrder;
use Illuminate\Http\Request;
use App\Repositories\ShoppingOrderRepository;
use App\Helpers\formattedApiResponse;
use App\Helpers\ApiResponse;
use App\Models\User;
use Validator;
use Globals;
use Helper;

class ShoppingOrderController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(ShoppingOrderRepository $ShoppingOrderRepo)
    {
        $ShoppingOrders = $ShoppingOrderRepo->getShoppingOrders();
        return formattedApiResponse::getJson($ShoppingOrders);
    }
    
    public function getCustomerOrders(ShoppingOrderRepository $ShoppingOrderRepo, $id)
    {
        $ShoppingOrders = $ShoppingOrderRepo->getCustomerShoppingOrders($id);
        return formattedApiResponse::getJson($ShoppingOrders);
    }
    public function getOrdersDetails(ShoppingOrderRepository $ShoppingOrderRepo, $id)
    {
        $ShoppingOrders = $ShoppingOrderRepo->getShoppingOrderDetails($id);
        return formattedApiResponse::getJson($ShoppingOrders);
    }
    
    
    
    
    
    
    public function pendingOrders(ShoppingOrderRepository $ShoppingOrderRepo)
    {
        $ShoppingOrders = $ShoppingOrderRepo->getPendingShoppingOrders();
        return formattedApiResponse::getJson($ShoppingOrders);
    }
    
    public function processedOrders(ShoppingOrderRepository $ShoppingOrderRepo)
    {
        $ShoppingOrders = $ShoppingOrderRepo->getProcessedShoppingOrders();
        return formattedApiResponse::getJson($ShoppingOrders);
    }
    
    
    
    public function myOrders(ShoppingOrderRepository $ShoppingOrderRepo, $id)
    {
        $ShoppingOrders = $ShoppingOrderRepo->getMyShoppingOrders($id);
        return formattedApiResponse::getJson($ShoppingOrders);
    }
    
    
    public function myPendingOrders(ShoppingOrderRepository $ShoppingOrderRepo, $id)
    {
        $ShoppingOrders = $ShoppingOrderRepo->getMyPendingShoppingOrders($id);
        return formattedApiResponse::getJson($ShoppingOrders);
    }
    
    public function myProcessedOrders(ShoppingOrderRepository $ShoppingOrderRepo, $id)
    {
        $ShoppingOrders = $ShoppingOrderRepo->getMyProcessedShoppingOrders($id);
        return formattedApiResponse::getJson($ShoppingOrders);
    }
    
    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {
        //
    }
    
    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        $resp = new ApiResponse();
        $method = "ShoppingOrderController@store";
        $validator = Validator::make($request->all(), [
            'item' => 'required',
            'quantity' => 'required',
            'vendor' => 'required',
            'customer_id' => 'required',
            'delivery_date' => 'required',
            'address' => 'required',
            
        ]);
        
        try{
            
            if($validator->fails()){
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = $validator->errors()->all();
            }
            else{
                
                $item = trim($request->input('item'));
                $quantity = trim($request->input('quantity'));
                $amount = floatval($quantity)*rand(1000,35000);
                $vendor = trim($request->input('vendor'));
                $customer_id = trim($request->input('customer_id'));
                $delivered_date = trim($request->input('delivered_date'));
                $address = trim($request->input('address'));
                
                
                $order = new ShoppingOrder();
                
                $order->items = json_encode($item);
                $order->order_no = ShoppingOrder::max('id') + 1;
                $order->quantity = $quantity;
                $order->amount = $amount;
                $order->vendor_id = User::where('first_name', 'like', '%'.$vendor.'%')->value('id');
                $order->address = $address;
                $order->customer_id = $customer_id;
                $order->delivered_date = date('Y-m-d', strtotime($delivered_date));
                
                if($order->save()){
                    
                    $action =  "submitted new order";
                    $message = "You have been successfully ".$action."";
                    Helper::logActivity($request, ['name' => 'system', 'role' => 'System', 'action' => $action]);
                    
                    $resp->message = $message;
                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                }
                else
                {
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = "Order could not be submited!";
                }
                
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $message = $ex->getMessage();
        }
        
        return response()->json($resp, 200);
    }
    
    /**
    * Display the specified resource.
    *
    * @param  \App\Models\ShoppingOrder  $ShoppingOrder
    * @return \Illuminate\Http\Response
    */
    public function show(ShoppingOrder $ShoppingOrder)
    {
        //
    }
    
    /**
    * Show the form for editing the specified resource.
    *
    * @param  \App\Models\ShoppingOrder  $ShoppingOrder
    * @return \Illuminate\Http\Response
    */
    public function edit(ShoppingOrder $ShoppingOrder)
    {
        //
    }
    
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\Models\ShoppingOrder  $ShoppingOrder
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, ShoppingOrder $ShoppingOrder)
    {
        //
    }
    
    /**
    * Remove the specified resource from storage.
    *
    * @param  \App\Models\ShoppingOrder  $ShoppingOrder
    * @return \Illuminate\Http\Response
    */
    public function destroy(ShoppingOrder $ShoppingOrder)
    {
        //
    }
    
    
    public function changeOrderStatus(Request $request, $id)
    {
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'status' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
                $this->response['message'] = $validator->errors()->all();
            }else{
                
                $author_id = $request->input('user_id');
                $status = $request->input('status');
                
                $statusAction = $status == 1 ? 'processed' : 'pending';
                if(ShoppingOrder::where('id', $id)->exists()){
                    $order = ShoppingOrder::find($id);
                    $order_no = $order->order_no; 
                    $order->status = $status == 1 ? Globals::$SHOPPING_LIST_PROCESSED_STATUS :  Globals::$SHOPPING_LIST_PENDING_STATUS;
                    if($order->save()){
                        $author = Helper::getUserNames($author_id);
                        $role = Helper::getUserRoleName($author_id);
                        $action = "changed the status of order with number ".$order_no." to ".$statusAction."";
                        Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                        $this->response['message'] = Helper::getMessage('success', $action);
                        $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                    }else{
                        $this->response['message'] ="Unable to ".$statusAction." order";
                        $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                    }
                }else{
                    $this->response['message'] ="Order doesn't exist!";
                    $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                }
            }
        }catch(\Exception $ex){
            $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
            $this->response['message'] = $ex->getMessage();
        }
        
        return response()->json($this->response, 200);  
        
    }
    
    
}
