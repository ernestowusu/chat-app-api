<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use Hash;

class AuthController extends Controller
{
    //

    public function validateRequest($request,$arrayOfParametersToValidate){
        $errorMessage = null;
        $hasError = false;
        $validator = Validator::make($request,$arrayOfParametersToValidate);
        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            $hasError = true;
        }
        return (object)['hasError'=>$hasError,'errorMessage'=>$errorMessage];
    }   
    public function register(Request $request){
        $validator = $this->validateRequest($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|unique:users',
            'password' => 'required',
        ]);
        if ($validator->hasError) {
            return response()->json(['Status'=>0,'Message'=>$validator->errorMessage]);
        }
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        if($user->save()){
            $token = $user->createToken('master')->accessToken;
            return response()->json(['Status'=>0,'Data'=>$user,'Token'=>$token]);
        }
        return response()->json(['Status'=>0,'Message'=>'User Could not be created.']);
    }

    public function login(Request $request){
        $validator = $this->validateRequest($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if ($validator->hasError) {
            return response()->json(['Status'=>0,'Message'=>$validator->errorMessage]);
        }
        $invalidResponse = response()->json(['Status'=>0,'Message'=>'Invalid Email or Password']);
        $user = User::where('email',$request->email)->first();
        if(is_null($user)){
            return $invalidResponse;
        }
        if(!Hash::check($request->password,$user->password)){
            return $invalidResponse;
        }
        $token = $user->createToken('master')->accessToken;
        return response()->json(['Status'=>0,'Data'=>$user,'Token'=>$token]);

    }
}
