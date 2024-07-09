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
            $nature_of_work = $request->query('nature_of_work');
            
            $jobs = JobPostingModel::where(function($query) use ($status){
                if($status != 'all'){
                    return $query->where('status', '=', $status);
                }
            })
            ->where(function($query) use ($nature_of_work){
                if($nature_of_work != null){
                    return $query->where('nature_of_work_id', '=', $nature_of_work);
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
    
    public function getOne($id){
        try {
            return response()->json([
                'status' => true,
                'data' => JobPostingModel::find($id),
            ], 200);
        } catch (\Throwable $th) {
            return $this->serverError($th);
        }
    }

    public function delete($id){
        try {
            JobPostingModel::find($id)->delete();
            return response()->json([
                'status' => true,
                'message' => 'Successfully deleted job posting',
            ], 200);
        } catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }

    public function update(Request $request, $id){
        try{
            // validate request
            $validateRequest = Validator::make($request->all(),
            [
                'company_name' => 'required',
                'title' => 'required',
                'description' => 'required',
                'nature_of_work_id' => 'required',
            ]);

            // if validation fails
            if($validateRequest->fails()){
                // return bad request
                return $this->badRequest($validateRequest->errors());
            }

            // update message
            JobPostingModel::find($id)->update($request->all());
            
            // return success
            return response()->json([
                'status' => true,
                'message' => 'Successfully updated job posting',
            ], 200);

        } catch (\Throwable $th) {
            // something went wrong, server error basically somethings wrong sa codes haha
            return $this->serverError($th);
        }
    }
}
