<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
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
                        $user['access_token'] = $user->createToken('user->'.$user->username, ['user'])->accessToken;
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



public function store(Request $request)
{
    $resp = new ApiResponse();
    $method = "UserController@store";
    
    try{
        
        if($request->filled(['user_id', 'first_name','last_name', 'address',
        'email', 'mobile_no','user_role'])){
            $fname = trim($request->input('first_name'));
            $lname = trim($request->input('last_name'));
            $address = trim($request->input('address'));
            $email = trim($request->input('email'));
            $telno = trim($request->input('mobile_no'));
            $role = $request->input('user_role');
            $reg = User::find($request->input('user_id'));
            $registra = $reg->first_name." ".$reg->last_name;
            $registra_id = User::where('id', $request->input('user_id'))->value('role');
            $name = $fname." ".$lname;
            $defaultPwd = '12345678';
            
            if($request->filled('id')) {
                
                $userId = $request->input('id');
                $user = User::find($userId);
                $username = $request->input('username');
                $bool_userExists = $this->validateUsername($user->username, $username); // User::where('username' ,$username)->exists();
                
                if(!$bool_userExists){
                    
                    if($request->hasFile('photo')){
                        
                        $file = $request->file('photo');
                        $file_extension = $file->extension();
                        if(!empty($user->image)){
                            Storage::disk('public')->delete($user->image);
                        }
                        $fileName = $userId.''.time().'.'.$file_extension;
                        $filePath = $file->storeAs('images/users', $fileName, 'public');
                        $image = $filePath;
                    }else{
                        $image  = $user->image;
                    }
                    
                    $hasUpdated = User::where('id', '=', $userId)
                    ->update([
                        'first_name' => $fname,
                        'last_name' => $lname,
                        'username' => $username,
                        'email' => $email,
                        'phone_number' => $telno,
                        'address' => $address,
                        'image' => $image,
                    ]);
                    
                    if($hasUpdated){
                        $action = "updated profile";
                        $resp->message = Helper::getMessage('success', $action);
                        $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                        $resp->data = User::count();
                    }else{
                        $resp->message ="Unable to update user account details!";
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    }
                }
                else{
                    $resp->message = "username ".$request->input('username')." has already been taken, choose another one";
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED; 
                }
                
            }else {
                
                $user = new User();
                $name = $fname." ".$lname;
                $username = strtolower(Str::random(6).".".$fname);
                $bool_userExists = User::where('username' ,$username)->exists();
                if(!$bool_userExists){
                    
                    $password = Hash::make($defaultPwd, ['rounds' => 12]);
                    $count = User::where('email', '=', $email)->count();
                    if($count == 0){
                        
                        if($request->hasFile('photo')){
                            $file = $request->file('photo');
                            $file_name = $file->getClientOriginalName();
                            $file_extension = $file->extension();
                            $fileName = $file_name.''.time().'.'.$file_extension;
                            $filePath = $file->storeAs('images/users', $fileName, 'public');
                            $image = $filePath;
                        }else{
                            $image = null;
                        }
                        
                        $user->first_name = $fname;
                        $user->last_name = $lname;
                        $user->username = $username;
                        $user->email = $email;
                        $user->role = $role;
                        $user->phone_number = $telno;
                        $user->address = $address;
                        $user->image = $image;
                        $user->password = $password;
                        $user->is_active = 1;
                        
                        $save_status = $user->save();
                        if($save_status){
                            $name = $fname." ".$lname;
                            $subject = 'User Registration';
                            $registraPosition = 'User';
                            $registraEmail = 'info@parkproug.com'; //$request->user()->email;
                            $default_password = $defaultPwd;
                            $now = now();
                            $registeredRole = Helper::getUserRole($role);
                            $action =  "registered user ".$name." as ".$registeredRole."";

                            $company = Company::whereNotNull('name')->first();
                            if(isset($company->name)){
                                $company_name = $company->name;
                            }else{
                                $company_name = env('APP_NAME');
                            }

                            $sendAction = "You have been registered as ".$registeredRole."  at ".$company_name." today at ".$now."";
                            Helper::logActivity($request, ['name' => $registra, 'role' => Helper::getUserRole($registra_id), 'action' => $action]);
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
                            $resp->message = "User registration failed!";
                        }
                    } else{
                        $message = "User with email ".$email." has been already registered";
                        $responseInfo = Helper::getMessage('error', $message);
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message  = $responseInfo;
                        
                    }
                    
                    
                }else{
                    $resp->message = "username ".$username." has already been taken, choose another one";
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                }
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
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'first_name' => 'required',
        'last_name' => 'required',
        'phone_number' => 'required',
        'email' => 'required',
        'address' => 'required',
    ]);
    
    try{
        if($validator->fails()){
            $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
            $this->response['message'] = $validator->errors()->all();
        }else{
            
            $author_id = $request->input('user_id');
            $first_name = $request->input('first_name');
            $last_name = $request->input('last_name');
            $phone_number = $request->input('phone_number');
            $email = $request->input('email');
            $address = $request->input('address');
            $user = User::find($id);
            $names = Helper::getUserNames($id);
            
            $user->first_name = $first_name;
            $user->last_name = $last_name;
            $user->email = $email;
            $user->phone_number = $phone_number;
            $user->address = $address;
            
            if($user->save()){
                $author = Helper::getUserNames($author_id);
                $role = Helper::getUserRoleName($author_id);
                $action = "updated ".$names." details";
                Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                $this->response['message'] = Helper::getMessage('success', $action);
                $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
            }else{
                $this->response['message'] ="Unable to update user details!";
                $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
            }
        }
    }catch(\Exception $ex){
        $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
        $this->response['message'] = $ex->getMessage();
    }
    
    return response()->json($this->response, 200); 
}

/**
* Remove the specified resource from storage.
*
* @param  int  $id
* @return \Illuminate\Http\Response
*/
public function destroy($id)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
    ]);
    
    try{
        if($validator->fails()){
            $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
            $this->response['message'] = $validator->errors()->all();
        }else{
            
            $author_id = $request->input('user_id');
            $author = Helper::getUserNames($author_id);
            $user = User::find($id);
            $is_deleted = $user->is_deleted;
            $undo = !$is_deleted;
            $activity = $undo ? 'deleted': 'restored';

            $names = Helper::getUserNames($id); 
            $user->is_deleted = $undo;
            $user->deleted_by = $author;
            if($user->save()){
                $role = Helper::getUserRoleName($author_id);
                $action = "".$activity." ".$names."'s account";
                Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                $this->response['message'] = Helper::getMessage('success', $action);
                $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
            }else{
                $this->response['message'] ="Unable to delete user!";
                $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
            }
        }
    }catch(\Exception $ex){
        $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
        $this->response['message'] = $ex->getMessage();
    }
    
    return response()->json($this->response, 200); 
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



public function changePassword(Request $request)
    {
        $resp = new ApiResponse();
        try {
            
            if( ($request->has('user_id') && $request->filled('user_id')) &&
            ($request->has('current_password') && $request->filled('current_password')) &&
            ($request->has('new_password') && $request->filled('new_password')) &&
            ($request->has('confirm_password') && $request->filled('confirm_password'))){
                
                $user_id = $request->input('user_id');
                $current_password = $request->input('current_password');
                $new_password = $request->input('new_password');
                $confirm_password = $request->input('confirm_password');
                
                $user = User::find($user_id);
                $old_password = $user->password;
                if($new_password == $confirm_password){
                    if(Hash::check($current_password, $old_password)){
                        $user->password = Hash::make($new_password);
                        if($user->save()){
                            $action = "changed your password";
                            $name = $user->first_name." ".$user->last_name;
                            Helper::logActivity($request, ['name' => $name, 'role' => Helper::getUserRole($user->role), 'action' => $action]);
                            $message = Helper::getMessage('success', $action);
                            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $resp->message  = $message;
                        }else{
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                            $resp->message = "Unable to change your password";
                        }  
                    }else{
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = "Incorrect old password";
                    }
                }else{
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = "Enter new matching passwords";
                }
            }
            else{
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = "Unable to process request";
            }
            
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
            $resp->data = $ex->getMessage();
        }
        
        return response()->json($resp);
    }


}
