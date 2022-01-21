<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Good;
use App\Models\User;
use App\Helpers\ApiResponse;
use App\Repositories\GoodsRepository;
use App\Helpers\formattedApiResponse;
use Validator;
use Globals;
use Auth;
use Helper;

class GoodsController extends Controller
{
    
    public $response = [];
    
    
    public function __constructor(){
        $this->response = new ApiResponse();
    }
    
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(GoodsRepository $goodsRepo)
    {
        $goods = $goodsRepo->getGoods();
        return formattedApiResponse::getJson($goods);
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
        $validatedData = Validator::make($request->all(), [
            'user_id' => 'required',
            'vendor_id' => 'required',
            'name' => 'required',
            'qty_available' => 'required',
            'unit_price' => 'required',
            // 'photo' => 'required',
        ]);
        
        if($validatedData->fails()){
            $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
            $this->response['message'] =  $validatedData->errors()->all();
        }else{
            
            $vendor_id = trim($request->input('vendor_id'));
            $name = trim($request->input('name'));
            $qty_available = trim($request->input('qty_available'));
            $unit_price = $request->input('unit_price');
            $reg = User::find($request->input('user_id'));
            $registra = $reg->first_name." ".$reg->last_name;
            $registra_id = User::where('id', $request->input('user_id'))->value('role');
            
            $doesGoodExist = Good::where('vendor_id', $vendor_id)->where('name', $name)->exists();
            if(!$doesGoodExist){
                
                if($request->hasFile('photo')){
                    $file = $request->file('photo');
                    $file_name = $file->getClientOriginalName();
                    $file_extension = $file->extension();
                    $fileName = time().'.'.$file_extension;
                    $filePath = $file->storeAs('images/goods', $fileName, 'public');
                    $photo = $filePath;
                }else{
                    $photo = null;
                }
                
                $good = new Good();
                $good->vendor_id =  $vendor_id;
                $good->name =  ucfirst($name);
                $good->qty_available = $qty_available;
                $good->unit_price =  $unit_price;
                $good->photo =  $photo;

                if($good->save()){
                    $action =  "added new good ".$name."";
                    $message = "You have been successfully added good ".$name."!";
                    Helper::logActivity($request, ['name' => $registra, 'role' => Helper::getUserRole($registra_id), 'action' => $action]);
                    
                    $this->response['message'] = $message;
                    $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                    
                }
                else
                {
                    $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                    $this->resp['message'] = "New good has not been added!";
                }
            } else{
                $message = "Good named ".$name." for vendor ".$vendor." has been already added";
                $responseInfo = Helper::getMessage('error', $message);
                $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                $this->response['message']  = $responseInfo;
            }
        }
        return response()->json($this->response, 200);
        
    }
    
    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        //
    }
    
    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        //
    }
    
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id)
    {
        //
    }
    
    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        //
    }
}
