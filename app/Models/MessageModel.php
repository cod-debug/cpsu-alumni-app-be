<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageModel extends Model
{
    use HasFactory;
    protected $table = "messages";

    protected $fillable = [
        'message',
        'user_from',
        'user_to',
        'status',
        'is_edited',
        'is_seen'
    ];

    public function receiver(){
        return $this->belongsTo(User::class, 'user_to');
    }

    public function sender(){
        return $this->belongsTo(User::class, 'user_from');
    }
}
