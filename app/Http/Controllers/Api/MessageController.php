<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MessageModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function add(Request $request){
        try{
            $validateMessage = Validator::make($request->all(), [
                'message' => 'required',
                'user_from' => 'required|integer',
                'user_to' => 'required|integer',
                'status' => 'sent'
            ]);

            // if validation fails
            if($validateMessage->fails()){
                // return bad request
                return $this->badRequest($validateMessage->errors());
            }

            // create message
            MessageModel::create($request->all());
            
            // return success
            return response()->json([
                'status' => true,
                'message' => 'Successfully sent message',
            ], 200);

        } catch (\Throwable $th) {
            // something went wrong server error basically somethings wrong sa codes haha
            return $this->serverError($th);
        }
    }
    
    public function update(Request $request, $id){
        try{
            // validate request
            $validateMessage = Validator::make($request->all(), [
                'message' => 'required',
                'is_edited' => true,
            ]);

            // if validation fails
            if($validateMessage->fails()){
                // return bad request
                return $this->badRequest($validateMessage->errors());
            }

            // update message
            MessageController::find($id)->update($request->all());
            
            // return success
            return response()->json([
                'status' => true,
                'message' => 'Successfully updated message',
            ], 200);

        } catch (\Throwable $th) {
            // something went wrong server error basically somethings wrong sa codes haha
            return $this->serverError($th);
        }
    }

    public function getMessagesByUser(Request $request){
        try {
            $user_from = $request->query('user_from');
            $user_to = $request->query('user_to');

            $messages = MessageModel::where(function($query) use ($user_from, $user_to) {
                return $query->where('user_from', $user_from)->where('user_to', $user_to);
            })
            ->orWhere(function($query) use ($user_from, $user_to) {
                return $query->where('user_from', $user_to)->where('user_to', $user_from);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(30);

            return response()->json($messages);
        } catch (\Throwable $th) {
            // something went wrong server error basically somethings wrong sa codes haha
            return $this->serverError($th);
        }
    }

    public function getAllMessages(Request $request){
       // Fetch users with their last message timestamp
        $messages = User::select(
            'users.id',
            'users.first_name',
            'users.last_name',
            'users.middle_name',
            'users.avatar',
            'users.type',
            \DB::raw('COALESCE(MAX(received.created_at), MAX(sent.created_at)) as last_message_timestamp')
        )
        ->leftJoin('messages as received', 'users.id', '=', 'received.user_to')
        ->leftJoin('messages as sent', 'users.id', '=', 'sent.user_from')
        ->where('users.id', '!=', $request->query('user_from'))
        ->groupBy(
            'users.id',
            'users.first_name',
            'users.last_name',
            'users.middle_name',
            'users.avatar',
            'users.type'
        )
        ->orderBy('last_message_timestamp', 'desc')
        ->paginate($request->query('limit'));

        // Transform user collection
        $messages->getCollection()->transform(function ($user) use ($request) {
            // Generate avatar URL
            $avatar_url = $user->avatar ? asset($user->type === 'admin' ? 'avatars/admin/' . $user->avatar : 'avatars/' . $user->avatar) : null;
            $avatar_url = Storage::disk('local')->url($avatar_url);

            // Get last message if exists
            $last_message = null;
            if ($user->getLastMessage() && in_array($request->user_from, [$user->getLastMessage()->user_from, $user->getLastMessage()->user_to])) {
                $last_message = $user->getLastMessage();
            }

            return [
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'middle_name' => $user->middle_name,
                'last_message' => $last_message,
                'avatar_url' => $avatar_url
            ];
        });

        return response()->json($messages);
    }

    public function getReceivedMessages($id){
        try {
            $messages = MessageModel::where('user_to', $id)->orderBy('created_at','desc')->paginate(5);

            
        $messages->getCollection()->transform(function ($message) {
            $avatar_url = null;

             // Assuming avatar path is relative to the storage directory
            $filePath = 'avatars/' . $message->sender->avatar;
            if($message->sender->type === 'admin'){
                $filePath = 'avatars/admin/' . $message->sender->avatar;
            }

            if($message->sender->avatar){

                if (Storage::disk('local')->exists($filePath)) {
                    $avatar_url = Storage::disk('local')->url($filePath);
                } else {
                    // Provide a default avatar URL if the avatar doesn't exist
                    $avatar_url = asset('default_avatar_url.jpg');
                }
            }

            return [
                ...collect($message),
                "avatar_url" => $avatar_url,
            ];
        });
            
            return response()->json($messages);
        } catch (\Throwable $th) {
            return $this->serverError($th);
        }
    }
}
