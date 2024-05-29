<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'birthdate',
        'contact_number',
        'street',
        'barangay',
        'municipality',
        'province',
        'zip_code',
        'course_id',
        'year_graduated',
        'employment_status',
        'work',
        'work_location',
        'avatar',
        'type',
        'status',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function course(){
        return $this->belongsTo(CourseModel::class, 'course_id', 'id');
    }

    public function received_messages(){
        return $this->hasMany(MessageModel::class, 'user_to', 'id');
    }

    public function sent_messages(){
        return $this->hasMany(MessageModel::class, 'user_from', 'id');
    }

    public function lastMessageReceived(){
        return $this->received_messages()->latest()->first();
    }

    public function lastMessageSent(){
        return $this->sent_messages()->latest()->first();
    }
    
    public function getLastMessage()
    {
        $lastReceivedMessage = $this->received_messages()
            ->latest() // Get the latest received message
            ->with('sender') // Eager load the sender
            ->first(); // Retrieve only the first result

        $lastSentMessage = $this->sent_messages()
            ->latest() // Get the latest sent message
            ->with('receiver') // Eager load the receiver
            ->first(); // Retrieve only the first result

        // Compare timestamps of last received and sent messages to get the latest one
        if ($lastReceivedMessage && $lastSentMessage) {
            return $lastReceivedMessage->created_at > $lastSentMessage->created_at ? $lastReceivedMessage : $lastSentMessage;
        } elseif ($lastReceivedMessage) {
            return $lastReceivedMessage;
        } elseif ($lastSentMessage) {
            return $lastSentMessage;
        }

        return null; // No messages found
    }
}
