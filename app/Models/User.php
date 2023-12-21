<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 
        'lastname',
        'phone',
        'email',
        'password', 
        'package_id', 
        'date_of_expiry', 
        'verification_code', 
        'is_verified',
        'gender', 
        'time_zone_country', 
        'time_zone_time', 
        'DOB', 
        'address', 
        'company', 
        'job_title', 
        'website', 
        'facebook_url', 
        'twitter_url', 
        'linkedin_url', 
        'youtube_url', 
        'profile_image', 
        'refer_code', 
        'reffered_from', 
        'country',
        'payment_id', 
        'payment_method', 
        'user_type', 
        'login', 
        'business_type', 
        'sub_category',
        'admin_text',
        'balance_transaction', 
        'balance_transaction_type',
        'stripe_customer_id', 
        'stripe_subscription_id',
        
        'plan_id',
        'subscription_id',
        
        'subscription_status',

        'sport_type',
        'age_type',
        'position',
        'state',
        'location',
        'race',
        'city'
        
    ];



    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
