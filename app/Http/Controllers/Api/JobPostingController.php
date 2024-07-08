<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobPostingModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class JobPostingController extends Controller
{
    //
    public function add(Request $request){

        try {
            $validateRequest = Validator::make($request->all(),
            [
                'company_name' => 'required',
                'title' => 'required',
                'description' => 'required',
                'nature_of_work_id' => 'required',
            ]);

            // check if request is valid
            if($validateRequest->fails()){
                // invalid request
                return $this->badRequest($validateRequest->errors());
            }
            
            JobPostingModel::create($request->all());

            // return success
            return response()->json([
                'status' => true,
                'message' => 'Job Posted Successfully'
            ], 200);
        } catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }

    public function getPaginated(Request $request){
        try {
            $size = $request->query("limit") ?? 10;
            $keyword = $request->query("search");
            $status = $request->query("status") ?? 'all';
            
            $jobs = JobPostingModel::where(function($query) use ($status){
                if($status != 'all'){
                    return $query->where('status', '=', $status);
                }
            })
            ->where(function($query) use ($keyword){
                return $query->where('title', 'LIKE', "%$keyword%")
                ->orWhere('company_name', 'LIKE', "%$keyword%")
                ->orWhere('description', 'LIKE', "%$keyword%");
            })
            ->paginate($size);


            return response()->json([
                'status' => true,
                'data' => $jobs
            ], 200);
        } catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }
}
