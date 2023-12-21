<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'infoFormData', 
        'socialFormData',
        'colorTheme', 
        'change_photo', 
        'change_logo', 
        'changeProductImages',

        'sends',
        'totalViews',
        
        'cardTitle','firstName' ,'lastName', 'email', 'phoneNumber', 'alternativePhoneNo', 'companyName', 'jobTitle', 'address',
        'aboutText', 'phoneTextAllow', 'showSaveButton', 'showForwardButton', 'showInviteCode',
        'facebook', 'twitter', 'youtube', 'instagram', 'linkedin', 'pinterest', 'skypeID', 'whatsappID', 'snapchat', 'lineID', 'voxerID',
        'youtubeTitle', 'youtubeLink', 'vimeoTitle', 'vimeoLink', 'linkButtonTitle', 'websiteLink', 'city', 'country', 'profile_image',
         'cardTitle'
    ];
}
