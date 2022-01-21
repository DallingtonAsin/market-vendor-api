<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\ApiResponse;
use App\Repositories\UserRepository;
use App\Helpers\formattedApiResponse;
use Illuminate\Support\Str;
use App\Mail\RegistrationMailSender;
use Hash;
use Validator;
use Globals;
use Auth;
use Helper;


class UserController extends Controller
{
    
    public $response = [];
    
    
    public function __constructor(){
        $this->response = new ApiResponse();
    }
    
    
    
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
                        $user['access_token'] = $user->createToken('vendor->'.$user->username, ['vendor'])->accessToken;
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

private function getUserId($login)
{
    filter_var($login, FILTER_VALIDATE_EMAIL)
    ? $fieldType = 'email' 
    : $fieldType = 'username';
    
    $userId = User::where($fieldType, $login)
    ->value('email');
    
    return $userId;
}

private function findAccountStatus($id){ 
    $accountStatus = User::where('email', $id)->value('is_active');
    return $accountStatus; 
}

private function isAccountDeleted($id){ 
    $is_deleted = User::where('email', $id)->value('is_deleted');
    return $is_deleted; 
}

/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
public function index(UserRepository $userrepo)
{
    $users = $userrepo->getUsers();
    return formattedApiResponse::getJson($users);
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
    $method = "UserController@store";
    
    try{
        
        if($request->filled(['user_id', 'first_name','last_name', 'email', 'mobile_no'])){
            
            $user_id = $request->input('user_id');
            $fname = trim($request->input('first_name'));
            $lname = trim($request->input('last_name'));
            $email = trim($request->input('email'));
            $telno = trim($request->input('mobile_no'));
            
            $reg = User::find($user_id);
            $registra = $reg->first_name." ".$reg->last_name;
            $name = $fname." ".$lname;
            $defaultPwd = '12345678';
            
            
            $user = new User();
            $name = $fname." ".$lname;
            $username = strtolower(Str::random(6).".".$fname);
            $bool_userExists = User::where('username' ,$username)->exists();
            if(!$bool_userExists){
                
                $password = Hash::make($defaultPwd, ['rounds' => 12]);
                $count = User::where('email', '=', $email)->count();
                if($count == 0){
                    
                    $user->first_name = $fname;
                    $user->last_name = $lname;
                    $user->username = $username;
                    $user->email = $email;
                    $user->phone_number = $telno;
                    $user->password = $password;
                    $save_status = $user->save();
                    if($save_status){
                        $name = $fname." ".$lname;
                        $subject = 'Vendor Registration';
                        $registraPosition = 'Vendor';
                        $registraEmail = 'info@marketvendor.com';
                        $default_password = $defaultPwd;
                        $now = now();
                        $registeredRole = 'Market Vendor';
                        $company_name = config('app.company');
                        $action =  "registered ".$name." as ".$registeredRole."";
                     
                        $sendAction = "You have been registered as ".$registeredRole."  at ".$company_name." today at ".$now."";
                        Helper::logActivity($request, ['name' => $registra, 'role' => 'Admin', 'action' => $action]);
                        $data = array(
                            'name' => $name,
                            'first_name' => $fname,
                            'last_name' => $lname,
                            'username' => $username,
                            'password' => $default_password,
                            'user_position' => 'user',
                            'registra' => $registra,
                            'registraPosition' => $registraPosition,
                            'registraEmail' => $registraEmail,
                            'email' => $email,
                            'subject' => $subject,
                            'created_at' => $now,
                            'details' => $sendAction,
                            'activity' => 'registration',
                            'company' => $company_name,
                        );
                        
                        if(Helper::is_connectedToInternet() == 1){
                            \Mail::to($email)->send(new RegistrationMailSender($data));
                            $message = $action." and email has been sent";
                            
                        }else{
                            $message = $action;
                        }
                        
                        $resp->message = Helper::getMessage('success', $message);
                        $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                        $resp->data = User::count();
                    }
                    else
                    {
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = "Vendor registration failed!";
                    }
                } else{
                    $message = "Vendor with email ".$email." has been already registered";
                    $responseInfo = Helper::getMessage('error', $message);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message  = $responseInfo;
                    
                }
                
                
            }else{
                $resp->message = "username ".$username." has already been taken, choose another one";
                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
            }
            
        } else{
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = "Unable to process request: missing parameters";
        }
        
    } catch (\Exception $ex) {
        $resp->statusCode = Globals::$STATUS_CODE_ERROR;
        $resp->message = $message = $ex->getMessage();
    }
    
    $dataArr = array("code" => $resp->statusCode,
    "message" => $resp->message,
    "method" => $method);
    Helper::LogRequest($request, $dataArr);
    return response()->json($resp);
    
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

public function changeAccountStatus(Request $request, $id)
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

            $statusAction = $status == 1 ? 'activated' : 'deactivated';
            if(User::where('id', $id)->exists()){
            $user = User::find($id);
            $names = Helper::getUserNames($id); 
            $user->is_active = $status;
            if($user->save()){
                $author = Helper::getUserNames($author_id);
                $role = Helper::getUserRoleName($author_id);
                $action = "".$statusAction." ".$names."'s account";
                Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                $this->response['message'] = Helper::getMessage('success', $action);
                $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
            }else{
                $this->response['message'] ="Unable to ".$statusAction." vendor account!";
                $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
            }
        }else{
            $this->response['message'] ="Vendor account doesn't exist!";
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
