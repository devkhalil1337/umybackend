<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'price', 
        'net_price', 
        'description', 
        'image', 
        'limit', 
        'expire_in',
        'logo', 
        'product_image', 
        'social_media_listing', 
        'website', 
        'videos',
        'stripe_product_id', 
        'stripe_price_id',
        'vimeo', 'umyotube',
        'vimeo', 'umyotube'
    ];
}
