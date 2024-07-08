<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function badRequest($validateDataErrors){
        return response()->json([
            'status_code' => 400,
            'message' => 'Invalid Request',
            'errors' => $validateDataErrors
        ], 400);
    }

    public function serverError($th){
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong. Please contact administrator',
            'error' => $th->getMessage()
        ], 500);
    }
}
