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
        $messages = User::select(
            'users.id',
            'users.first_name',
            'users.last_name',
            'users.middle_name',
            'users.avatar',
            'users.type',
            'last_message.last_message_timestamp'
        )
        ->leftJoin('messages as received', 'users.id', '=', 'received.user_to')
        ->leftJoin('messages as sent', 'users.id', '=', 'sent.user_from')
        ->leftJoin(\DB::raw('(SELECT
                CASE
                    WHEN MAX(received.created_at) IS NOT NULL THEN MAX(received.created_at)
                    ELSE MAX(sent.created_at)
                END as last_message_timestamp,
                COALESCE(received.user_to, sent.user_from) as user_id
            FROM
                messages as received
            LEFT JOIN
                messages as sent ON received.user_to = sent.user_from
            GROUP BY
                COALESCE(received.user_to, sent.user_from)) as last_message'), function($join) {
                $join->on('users.id', '=', 'last_message.user_id');
        })
        ->where('users.id', '!=', $request->query('user_from'))
        ->groupBy(
            'users.id',
            'users.first_name',
            'users.last_name',
            'users.middle_name',
            'users.avatar',
            'users.type',
            'last_message.last_message_timestamp'
        )
        ->orderBy('last_message.last_message_timestamp', 'desc')
        ->paginate($request->query('limit'));
        
        $messages->getCollection()->transform(function ($user) {
            $avatar_url = null;

             // Assuming avatar path is relative to the storage directory
            $filePath = 'avatars/' . $user->avatar;
            if($user->type === 'admin'){
                $filePath = 'avatars/admin/' . $user->avatar;
            }

            if($user->avatar){

                if (Storage::disk('local')->exists($filePath)) {
                    $avatar_url = Storage::disk('local')->url($filePath);
                } else {
                    // Provide a default avatar URL if the avatar doesn't exist
                    $avatar_url = asset('default_avatar_url.jpg');
                }
            }

            return [
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'middle_name' => $user->middle_name,
                'received_messages' => $user->received_messages,
                'sent_messages' => $user->sent_messages,
                'last_message' => $user->getLastMessage(),
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
