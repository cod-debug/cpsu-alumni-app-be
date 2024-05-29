<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
}
