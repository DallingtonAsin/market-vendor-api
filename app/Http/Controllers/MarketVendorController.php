<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class MarketVendorController extends Controller
{




    public function authenticate(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
                $this->response['message'] = $validator->errors()->all();
            } else {
                
                ($request->has('remember'))
                ? $remembered = true
                : $remembered = false;
                
                $login = $request->input('username');
                $password = $request->input('password');
                
                filter_var($login, FILTER_VALIDATE_EMAIL)
                ? $fieldType = 'email' 
                : $fieldType = 'username';
                
                $user_id = $this->getUserId($login, $password);
                
                if(Auth::attempt([$fieldType => $login,
                'password' =>  $password
            ], $remembered)){
                
                $userId = $this->getUserId($login);

                $is_deleted = $this->isAccountDeleted($userId);
                $status = $this->findAccountStatus($userId);
                
                if($is_deleted == 0){
                if($status == 0){
                    $this->response['statusCode']  = Globals::$STATUS_CODE_FAILED;
                    $this->response['message']= 'Your account is inactivated, see admin';
                }
                if($status == 1){
                    $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                    $action = $this->response['message'] =  "logged into the system";
                    $user = Auth::user();
                    $user['access_token'] = $user->createToken('User->'.$user->username, ['user'])->accessToken;
                    $this->response['data'] = $user;
                    Helper::logActivity($request, ['name' => $login, 'role' => 'admin', 'action' => $action]);
                }
            }else{
                $this->response['statusCode']  = Globals::$STATUS_CODE_FAILED;
                $this->response['message']= 'Your account was removed, see admin';
            }
            }else
            {
                $this->response['statusCode']  = Globals::$STATUS_CODE_FAILED;
                $this->response['message'] = 'Invalid login credentials';
            }
        }
        
    } catch (\Exception $ex) {
        $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
        $this->response['message'] = $ex->getMessage();
    }
    
    return response()->json($this->response, 200);
}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
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
