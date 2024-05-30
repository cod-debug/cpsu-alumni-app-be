<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\AuthService;
use App\Mail\UserTemporaryPassword;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    //
    public function register(Request $request, AuthService $auth_service){
        
        try {
            $validateUser = Validator::make($request->all(),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'gender' => 'required',
                'birthdate' => 'required',
                'contact_number' => 'required|unique:users,contact_number',
                'barangay' => 'required',
                'municipality' => 'required',
                'province' => 'required',
                'zip_code' => 'required',
                'course_id' => 'required',
                'year_graduated' => 'required',
                'employment_status' => 'required',
                'email' => 'required|email|unique:users,email',
                'type' => ''
            ]);

            // check if request is valid
            if($validateUser->fails()){
                // invalid request
                return $this->badRequest($validateUser->errors());
            }

            $random_password = $auth_service->generateRandomString(8);
            $added_request = [
                'password' => Hash::make($random_password),
                'username' => $request->input('username') ?? $request->input('email')
            ];
            
            if($request->hasFile('avatar')){
                $file = $request->file('avatar');
                
                $originalName = str_replace(" ", "_", $request->last_name) . '-' . str_replace(" ", "_", $request->first_name).$file->getClientOriginalName();

                $path = Storage::disk('local')->putFileAs('avatars/', $file, $originalName);

                $added_request['avatar'] = $originalName;
            }
            // create user
            $user = User::create([...$request->all(),
                ...$added_request 
            ]);
            
            $data = [
                'user_name' => $user->first_name,
                'password' => $random_password
            ];
            Mail::to($user->email)->send(new UserTemporaryPassword($data));
            // return success
            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'user' => $user
            ], 200);

        } catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }
    public function login(Request $request)
    {
        try {
            // validate user input
            $validateUser = Validator::make($request->all(), 
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // check if validation fails
            if($validateUser->fails()){
                // return 401 unauthorized
                return $this->badRequest($validateUser->errors());
            }

            // check if email and password exists
            if(!Auth::attempt($request->only(['email', 'password']))){
                // if not return 401 - email and password invalid
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            // select specific user
            $user = User::where('email', $request->email)->first();

            // remove existing tokens to invalidate other logins
            $user->tokens()->delete();
            
            // response with token
            return response()->json([
                'status' => 'success',
                'message' => 'User Logged In Successfully',
                'data' => [
                    "user_id" => $user->id,
                    "first_name" => $user->first_name,
                    "middle_name" => $user->middle_name,
                    "last_name" => $user->last_name,
                    "type" => $user->type,
                ],
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
