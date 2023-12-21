<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class liveChatting extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_message',
        'message_response',
        'message_type',
    ];
}
