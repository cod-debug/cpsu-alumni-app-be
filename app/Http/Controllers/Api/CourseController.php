<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\CourseModel;

class CourseController extends Controller
{
    //

    public function add(Request $request){
        try {
            // validate request
            $validateRequest = Validator::make($request->all(), [
                'course_name' => 'required|unique:courses,course_name',
            ]);

            if($validateRequest->fails()){
                return $this->badRequest($validateRequest->errors());
            }

            CourseModel::create($request->all());
            
            // return success
            return response()->json([
                'status' => true,
                'message' => 'Successfully created course',
            ], 200);

        } catch (\Throwable $th) {
            // something went wrong server error basically somethings wrong sa codes haha
            return $this->serverError($th);
        }
    }
    
    public function getOne($id){
        try {
            return response()->json([
                'status' => true,
                'data' => CourseModel::find($id),
            ], 200);
        } catch (\Throwable $th) {
            return $this->serverError($th);
        }
    }

    public function update(Request $request, $id){
        try{
            // validate request
            $validateRequest = Validator::make($request->all(), [
                'course_name' => 'required',
            ]);

            // if validation fails
            if($validateRequest->fails()){
                // return bad request
                return $this->badRequest($validateRequest->errors());
            }

            // update message
            CourseModel::find($id)->update($request->all());
            
            // return success
            return response()->json([
                'status' => true,
                'message' => 'Successfully updated course',
            ], 200);

        } catch (\Throwable $th) {
            // something went wrong, server error basically somethings wrong sa codes haha
            return $this->serverError($th);
        }
    }

    public function getPaginated(Request $request){
        
        try {
            $size = $request->query("limit") ?? 10;
            $keyword = $request->query("search");
            $status = $request->query("status") ?? 'all';

            $courses = CourseModel::where(function($query) use ($status){
                if($status != 'all'){
                    return $query->where('status', '=', $status);
                }
            })
            ->where(function($query) use ($keyword){
                return $query->where('course_name', 'LIKE', "%$keyword%")
                ->orWhere('course_description', 'LIKE', "%$keyword%");
            })
            ->orderBy('course_name')
            ->paginate($size);


            return response()->json([
                'status' => true,
                'data' => $courses
            ], 200);

        } catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }

    public function delete($id){
        try {
            CourseModel::find($id)->delete();
            return response()->json([
                'status' => true,
                'message' => 'Successfully deleted course',
            ], 200);
        } catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }
}
