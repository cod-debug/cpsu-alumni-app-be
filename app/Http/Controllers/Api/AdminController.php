<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    //
    public function register(Request $request){
        try {
            $validateUser = Validator::make($request->all(),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'gender' => 'required',
                'birthdate' => 'required',
                'contact_number' => 'required',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|unique:users,username',
                'password' => 'required',
            ]);

            // check if request is valid
            if($validateUser->fails()){
                // invalid request
                return $this->badRequest($validateUser->errors());
            }

            $added_request = [
                'password' => Hash::make($request->input('password')),
                'type' => 'admin'
            ];

            if($request->hasFile('avatar')){
                $file = $request->file('avatar');
                
                $originalName = str_replace(" ", "_", $request->last_name) . '-' . str_replace(" ", "_", $request->first_name).$file->getClientOriginalName();

                $path = Storage::disk('local')->putFileAs('avatars/admin/', $file, $originalName);

                $added_request['avatar'] = $originalName;
            }
            // create user
            $user = User::create([...$request->all(),
                ...$added_request 
            ]);
            
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
    
    public function getPaginatedAdmin(Request $request){
        try {
            $size = $request->query("limit") ?? 10;
            $keyword = $request->query("search");
            $type = "admin";

            $users = User::where(function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', "%$keyword%")
                    ->orWhere('middle_name', 'like', "%$keyword%")
                    ->orWhere('last_name', 'like', "%$keyword%")
                    ->orWhere('email', 'like', "%$keyword%")
                    ->orWhere('username', 'like', "%$keyword%");
            })
            ->orderBy("last_name")
            ->paginate($size);

            $users->getCollection()->transform(function ($item) {
                $filePath = 'avatars/admin/' . $item['avatar']; // Assuming avatar path is relative to the storage directory
                if (Storage::disk('local')->exists($filePath)) {
                    $item['avatar_url'] = Storage::disk('local')->url(env('STORAGE_PREFIX').$filePath);
                } else {
                    // Provide a default avatar URL if the avatar doesn't exist
                    $item['avatar_url'] = asset('default_avatar_url.jpg'); // Assuming default_avatar_url.jpg is in your public directory
                }
                return $item;
            });

            return response()->json([
                'status' => true,
                'size' => $size,
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
                'message' => 'Admin updated successfully',
                'user' => $user
            ], 200);

        } catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }
}
