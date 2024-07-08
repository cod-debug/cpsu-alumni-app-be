<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\NatureOfWorkModel;

class NatureOfWorkController extends Controller
{
    //
    public function add(Request $request){
        try {
            // validate request
            $validateRequest = Validator::make($request->all(), [
                'nature_of_work' => 'required|unique:natures_of_work,nature_of_work',
            ]);

            if($validateRequest->fails()){
                return $this->badRequest($validateRequest->errors());
            }

            NatureOfWorkModel::create($request->all());
            
            // return success
            return response()->json([
                'status' => true,
                'message' => 'Successfully created nature of work',
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
                'data' => NatureOfWorkModel::find($id),
            ], 200);
        } catch (\Throwable $th) {
            return $this->serverError($th);
        }
    }

    public function update(Request $request, $id){
        try{
            // validate request
            $validateRequest = Validator::make($request->all(), [
                'nature_of_work' => 'required',
            ]);

            // if validation fails
            if($validateRequest->fails()){
                // return bad request
                return $this->badRequest($validateRequest->errors());
            }

            // update message
            NatureOfWorkModel::find($id)->update($request->all());
            
            // return success
            return response()->json([
                'status' => true,
                'message' => 'Successfully updated nature of work',
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

            $naturesOfWork = NatureOfWorkModel::where(function($query) use ($status){
                if($status != 'all'){
                    return $query->where('status', '=', $status);
                }
            })
            ->where(function($query) use ($keyword){
                return $query->where('nature_of_work', 'LIKE', "%$keyword%");
            })
            ->orderBy('nature_of_work')
            ->paginate($size);


            return response()->json([
                'status' => true,
                'data' => $naturesOfWork
            ], 200);

        } catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }

    public function delete($id){
        try {
            NatureOfWorkModel::find($id)->delete();
            return response()->json([
                'status' => true,
                'message' => 'Successfully deleted nature of work',
            ], 200);
        } catch (\Throwable $th) {
            // something went wrong server error
            return $this->serverError($th);
        }
    }
}
