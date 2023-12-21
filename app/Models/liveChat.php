<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class liveChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'token',
        'respond_by',
        'dated',
        'status'
    ];
}
