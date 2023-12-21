<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class customContacts extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', '
        firstname', 
        'lastname', 
        'tags', 
        'phone', 
        'email', 
        'address', 
        'gender', 
        'company',  
        'position',  
        'website',  
        'facebook',  
        'twitter',  
        'linkedin'
    ];
}
