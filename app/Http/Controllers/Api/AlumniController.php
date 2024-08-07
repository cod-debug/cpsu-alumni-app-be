<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\AuthService;

use App\Mail\UserForgotPassword;
use Illuminate\Support\Facades\Mail;

class AlumniController extends Controller
{
    //
    

    public function getPaginatedAlumni(Request $request){
        try {
            $size = $request->query("limit") ?? 10;
            $keyword = $request->query("search");
            $type = "alumni";

            $users = User::where(function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', "%$keyword%")
                    ->orWhere('middle_name', 'like', "%$keyword%")
                    ->orWhere('last_name', 'like', "%$keyword%")
                    ->orWhere('email', 'like', "%$keyword%")
                    ->orWhere('work', 'like', "%$keyword%");
            })
            ->orderBy("last_name")
            ->paginate($size);

            $users->getCollection()->transform(function ($item) {
                $filePath = 'avatars/' . $item['avatar']; // Assuming avatar path is relative to the storage directory
                if (Storage::disk('local')->exists($filePath)) {
                    $item['avatar_url'] = Storage::disk('local')->url($filePath);
                } else {
                    // Provide a default avatar URL if the avatar doesn't exist
                    $item['avatar_url'] = asset('default_avatar_url.jpg'); // Assuming default_avatar_url.jpg is in your public directory
                }
                $item['course'] = $item->course;
                return $item;
            });

            return response()->json([
                'status' => true,
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }

    public function getOne($id){
        try {
            $user = User::find($id);
            
            if($user){
                return response()->json([
                    'status' => true,
                    'message' => 'User found.',
                    'data' => $user
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.',
                    'data' => $user
                ], 404);
            }
        } catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }

    public function update(Request $request, $id){
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'gender' => 'required',
                'birthdate' => 'required',
                'contact_number' => 'required',
                'barangay' => 'required',
                'municipality' => 'required',
                'province' => 'required',
                'zip_code' => 'required',
                'course_id' => 'required',
                'year_graduated' => 'required',
                'employment_status' => 'required'
            ]);

            // check if request is valid
            if($validateUser->fails()){
                // invalid request
                return $this->badRequest($validateUser->errors());
            }

            // create user
            $user = User::find($id)->update($request->all());

            // return success
            return response()->json([
                'status' => true,
                'message' => 'Alumni updated successfully',
                'user' => $user
            ], 200);

        } catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }

    public function getCountPerYear(){
        try {
            $usersCount = User::selectRaw('year_graduated, COUNT(*) as count')
            ->where('type', '=', 'alumni')
            ->groupBy('year_graduated')
            ->orderBy('year_graduated', 'desc')
            ->get();

            return response()->json([
                'status' => true,
                'data' => $usersCount
            ], 200);
        }  catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }

    public function login(Request $request){
        try {
            // validate user input
            $validateUser = Validator::make($request->all(), 
            [
                'email' => 'email',
                'contact_number' => '',
                'password' => 'required',
                'type' => 'required',
            ]);

            // check if validation fails
            if($validateUser->fails()){
                // return 401 unauthorized
                return $this->badRequest($validateUser->errors());
            }

            // check if email and password exists
            if(!Auth::attempt($request->only(['email', 'password'])) && !Auth::attempt($request->only(['contact_number', 'password']))){
                // if not return 401 - email and password invalid
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            // select specific user
            if($request->type == 'email'){
                $user = User::where('email', $request->email)->where('type', 'alumni')->first();
            } else {
                $user = User::where('contact_number', $request->contact_number)->where('type', 'alumni')->first();
            }

            if(is_null($user)){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }
            
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
                    "require_change_password" => $user->require_change_password,
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
    
    public function requiredChangePassword(Request $request){
        try {
            // validate request
            $validateUser = Validator::make($request->all(), 
            [
                'user_id' => 'required|numeric',
                'password' => 'required',
            ]);

            // check if validation fails
            if($validateUser->fails()){
                // return 401 unauthorized
                return $this->badRequest($validateUser->errors());
            }
            
            $user = User::find($request->user_id);
            if(!$user->require_change_password){
                return response()->json([
                    'status' => false,
                    'message' => "User already changed password"
                ], 400);

            }

            $user->update([
                'password' => Hash::make($request->password),
                'require_change_password' => false
            ]);

            return response()->json([
                'status' => true,
                'message' => "Successfully updated password."
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function forgotPassword(Request $request, AuthService $auth_service){
        try {
            $validateUser = Validator::make($request->all(),
            [
                'email' => 'required|email',
            ]);

            // check if validation fails
            if($validateUser->fails()){
                // return 401 unauthorized
                return $this->badRequest($validateUser->errors());
            }
            $message = 'If email exists on our system you will receive a temporary password.';

            $user = User::where('email', $request->email)->first();

            if($user === null){
                return response()->json([
                    'status' => true,
                    'message' => $message,
                ], 200);
            }
            $random_password = $auth_service->generateRandomString(8);
            $data = [
                'user_name' => $user->first_name,
                'password' => $random_password
            ];

            // update user password
            $user->update([
                'password' => Hash::make($random_password),
                'require_change_password' => true,
            ]);

            // send email with new password
            Mail::to($user->email)->send(new UserForgotPassword($data));

            return response()->json([
                'status' => true,
                'message' => $message,
            ], 200);
        } catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }

    public function countByGender(){
        try {
            return User::select('gender', \DB::raw('count(*) as count'))
            ->where('type', '=', 'alumni')
            ->where('status', '=', 'active')
            ->groupBy('gender')
            ->get();
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function countByEmploymentStatus(){
        try {
            return User::select([
                \DB::raw('COUNT(CASE WHEN work IS NOT NULL THEN 1 END) AS users_with_work'),
                \DB::raw('COUNT(CASE WHEN work IS NULL THEN 1 END) AS users_without_work'),
            ])
            ->where('type', '=', 'alumni')
            ->where('status', '=', 'active')
            ->first();
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
