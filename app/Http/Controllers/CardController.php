<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use DB;
use App\Models\Activity;
use Validator;
use App\Models\User;
use Hash;
use Auth;
use App\Models\Package;
use Mail;
use App\Mail\SendMail;
use App\Models\Category;
use PayPal\Api\ChargeModel; 
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition; 
use PayPal\Api\Plan;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;
use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Api\ShippingAddress;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\ResultPrinter;
use Session;
use Stripe;
use App\Models\MCSportType;
use App\Models\SCAgeType;
use App\Models\SCPosition;

class CardController extends Controller
{
    public function MainCategorySportType()
    {
        $types = MCSportType::latest()->get();
        
        return response()->json(['status'=>'Success', "message" => 'Types Found', 'types' => $types]);
    }
    
    public function SubCategoriesAgeType()
    {
        $types = SCAgeType::latest()->get();
        
        return response()->json(['status'=>'Success', "message" => 'Types Found', 'types' => $types]);
    }



    
    public function SubcategoryPosition(Request $request)
    {
        $category_id = $request->category_id;
        if(!isset($category_id)):
            return response()->json(['status'=>'Failed', "message" => 'Category ID is required']);
        endif;
        
        // get id from position
        $MCSportType = MCSportType::where('name', $category_id)->first();
        
        if(!isset($MCSportType)):
            if($category_id == 'N/A'):
                $types = SCPosition::where('id', 212)->latest()->get();
                return response()->json(['status'=>'Success', "message" => 'Types Found', 'types' => $types]);
            else:
                return response()->json(['status'=>'Failed', "message" => 'No Types Found']);
            endif;
        endif;
        
        $types = SCPosition::where('category_id', $MCSportType->id)->latest()->get();
        
        if($types->count() > 0):
            return response()->json(['status'=>'Success', "message" => 'Types Found', 'types' => $types]);
        else:
            if($category_id == 'N/A'):
                $types = SCPosition::where('id', 212)->latest()->get();
                return response()->json(['status'=>'Success', "message" => 'Types Found', 'types' => $types]);
            else:
                return response()->json(['status'=>'Failed', "message" => 'No Types Found']);
            endif;
        endif;
    }
    
    public function updateExpiryDate(Request $request)
    {
        $user_id = request('user_id');
        if(!isset($user_id)):
            return response()->json(['status'=>'Failed', "message" => 'User id is required']);
        endif;
        $date_of_expiry = request('date_of_expiry');
        if(!isset($date_of_expiry)):
            return response()->json(['status'=>'Failed', "message" => 'Expiry is required']);
        endif;
        
        $date_of_expiry_1 = $request->get('date_of_expiry');
        
        if($date_of_expiry_1 == 'never'):
            $date_of_expiry = '0000-00-00';
        else:
            $date_of_expiry = \Carbon\Carbon::now()->addMonths(1);;
        endif;
        
        $User = User::find($user_id);
        
        $User->date_of_expiry = $date_of_expiry;

        if ($User->save()) {
            return (['status'=>'Success', 'message'=>'Date of Expiry Updated Successfully']);
        }
        else{
            return response()->json(['status'=>'Failed', "message" => "Failed to update. Try again"]);
        }
    }
    
    public function resetPassword(Request $request)
    {
        $rules1 = [
            'email'        => 'required',
        ];
        $Validator = Validator::make($request->all(), $rules1);
        if ($Validator->fails()) {
            return response()->json(['status'=>'Failed', 'message'=>'Email is required']);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
           return (['status'=>'Failed', 'message'=>'Email is not registered.']);
        }

        // generate password random 8 letters
        $length = 8;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        // save in DB
        $user->password = bcrypt($randomString);
        
        if ($user->save()) {
            // send in Email
            $data = [
               'password'   => $randomString,
               'username'   => $user->firstname. $user->lastname,
            ];
            Mail::send('Email.changePassword', $data , function ($m) use ($request){
                $m->to($request->email, 'Password Reset')->from('recovery@umyocards.com')->subject('Reset Password.');
            });
            return (['status'=>'Success', 'message'=>'Password Sent Successfully']);
        }else{
            return (['status'=>'Failed', 'message'=>'Failed to send. please try again.']);
        }
    }
    
    
    public function getAllUsers(Request $request)
    {
        $users = User::where('user_type', 'user')->get();
        if($users->count()):
            
        foreach($users as $user):
            if(!empty($user->refer_code)):
                $myRefers = User::where('reffered_from', $user->refer_code)->count();
                
                $refferedUsers = User::where('reffered_from', $user->refer_code)->get();
                $packagePrice = 0;
                foreach($refferedUsers as $userReffered):
                    $packagePrice += Package::where('id', $userReffered->package_id)->sum('price');
                    
                    $packageData = Package::find($userReffered->package_id);
                    
                    if(isset($packageData)):
                        $user["bonusAmount"] =  number_format(($packageData->price/100)*50, 2);
                    else:
                        $user["bonusAmount"] =  0;
                    endif;
                endforeach;
            else:
                $myRefers = 0;
                $packagePrice = 0;
            endif;
            
            if(!isset($user["bonusAmount"])):
                $user["bonusAmount"] =  0;
            endif;
            
            if(!empty($user->package_id)):
                $limit = Package::find($user->package_id);
                if(!empty($limit->limit)):
                    $user->limit = $limit->limit;
                    $packagePrice = $limit->price; 
                else:
                    $user->limit = 0;
                    $packagePrice = 0;
                endif;
            else:
                $user->limit = 0;
                $packagePrice = 0;
            endif;
            
            $user->myRefers = $myRefers;
            $user->packagePrice = $packagePrice;
            
        endforeach;
            return response()->json(['status'=>'Success', "message" => 'Users Found', 'Users' => $users]);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Users not found']);
        endif;
    }
    
    public function saveCard(Request $request)
    {
        $header = $request->header('Authorization');
        if(!$header)
        {
            return response()->json(['Failed'=>'Unauthenticated']);
        }
        
        $user_id                = request('user_id');
        $infoFormData           = request('infoFormData');
        $socialFormData         = request('socialFormData');
   
        $change_photo           = request('change_photo');
        $change_logo            = request('change_logo');
        $changeProductImages    = request('changeProductImages');
        $colorTheme             = request('colorTheme');
        $cardTitle             = request('cardTitle');
        
        // get user package
        $user = User::find($user_id);
        $user_package = Package::find($user->package_id);
        
        if(isset($user_package)):
            $package_limit = $user_package->limit;
        else:
            $package_limit = 0;
        endif;
        
        // count cards of user_id user
        $myCardsCount = Card::where('user_id', $user_id)->count();
        
        if($myCardsCount >= $package_limit AND $package_limit != 0)
        {
            return response()->json(['status'=>'Failed', "message" => 'You exceeds your cards limit']);
        }
    
        $Card = new Card([
            'user_id'           => $user_id,
            'infoFormData'      => $infoFormData,
            'socialFormData'    => $socialFormData,
            'colorTheme'        => $colorTheme,
            'change_photo'      => $change_photo,
            'change_logo'       => $change_logo,
            'changeProductImages'=> $changeProductImages,
            'cardTitle'         => $cardTitle,
        ]);

        if($Card->save()):
            
        $Activity = new Activity([
            'user_id'            => $user_id,
            'activity'          => 'You save card successfully',
        ]);
        $Activity->save();
        
        $Card->status = 200;

        $infoFormData = json_decode($Card->infoFormData, true);
        $socialFormData = json_decode($Card->socialFormData, true);
        
        $Card->infoFormData = $infoFormData;
        $Card->socialFormData = $socialFormData;
        
        return response()->json(['status'=>'Success', "message" => 'Successfully Posted Card', 'Card' => $Card]);
        else:
            return response()->json(['status'=>'Success', "message" => 'Failed to post card']);
        endif;
    }


    public function getCard(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $stripe_subscription_id = $request->stripe_subscription_id;
        $subscription_id        = $request->subscription_id;
        // if(!isset($stripe_subscription_id) AND !isset($subscription_id))
        // {
        //     return response()->json(['Failed'=>'Unauthenticated', 'message' => 'Paypal or Stripe Subscription id is required.']);
        // }
        
        $user_id = request('user_id');
        $userData = User::find($user_id);
        
        if(!isset($user_id))
        {
            return response()->json(['Failed'=>'Unauthenticated', 'message' => 'User ID is required.']);
        }
        
        // Check for paypal
        if(isset($subscription_id) AND $subscription_id != 'null' AND $subscription_id != 'NULL'):
            $apiContext = new \PayPal\Rest\ApiContext(
                    new \PayPal\Auth\OAuthTokenCredential(
                        env('PAYPAL_KEY'),     // ClientID
                        env('PAYPAL_SECRET')     // ClientSecret
                    )
            );
            $apiContext->setConfig(
                array(
                    'mode' => 'live',
                    'log.LogEnabled' => true,
                    'log.FileName' => '../PayPal.log',
                    'log.LogLevel' => 'INFO', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                    'cache.enabled' => true,
                )
            );
            
            $Agreement = Agreement::get($subscription_id, $apiContext);
            
            if($Agreement->state != 'Active'){
                $userData->subscription_status = 'No';
                $userData->save();
                return response()->json(['status'=>'Failed', "message" => 'Package is expired.']);
            }
        endif;
        
        // Check for stripe
        if(isset($stripe_subscription_id) AND $stripe_subscription_id != 'null' AND $stripe_subscription_id != 'NULL'):
            $stripe = new \Stripe\StripeClient(
                env('STRIPE_SECRET') // secret key
            );
            
            $data = $stripe->subscriptions->retrieve(
              $stripe_subscription_id,
              []
            );
            
            if($data->status != 'active'){
                $userData->subscription_status = 'No';
                $userData->save();
                return response()->json(['status'=>'Failed', "message" => 'Package is expired.']);
            }
        endif;
         
        if($userData->subscription_status == 'Yes'):
            $Cards = Card::where('user_id', $user_id)->orderBy('id', 'DESC')->get();
            
            foreach($Cards as $Card):
                $infoFormData = json_decode($Card->infoFormData, true);
                $socialFormData = json_decode($Card->socialFormData, true);
                // foreach($socialFormData as $key => $social):

                //     if($key == 'youtubeVideos'):
                //       // return $social;
                //     endif;
                //     if($key == 'vimeoVideos'):
                //         // return $social;
                //     endif;
                //     if($key == 'umyotubeVideos'):
                //         if(empty($social)):
                //             unset($socialFormData[$key]);
                //         endif;
                //     endif;
                // endforeach;
                   
                $Card->infoFormData = $infoFormData;
                $Card->socialFormData = $socialFormData;
            endforeach;
            return response()->json(['status'=>'Success', 'Card' => $Cards]);
        else:
            return response()->json(['status'=>'error', 'message' => 'You have not purchased package.']);
        endif;
    }

    public function getSingleCard(Request $request)
    {
        $card_id = request('card_id');

        $Card = Card::find($card_id);
        
        if(!isset($Card)):
            return response()->json(['status'=>'error', 'message' => 'Card not found.']);
        endif;
        
        $userData = User::find($Card->user_id);
        
        if(!isset($userData)):
            return response()->json(['status'=>'error', 'message' => 'User not found.']);
        endif;
        
        if($userData->subscription_status == 'Yes'):
            if(!empty($Card)):
                $Card->status = 200;
                $userdata = User::find($Card->user_id);
                $inviteCode = $userdata->refer_code;
                $infoFormData = json_decode($Card->infoFormData, true);
                $socialFormData = json_decode($Card->socialFormData, true);
                $infoFormData['inviteCode'] = $inviteCode;
                $Card->infoFormData = $infoFormData;
                $Card->socialFormData = $socialFormData;
                $Activity = new Activity([
                    'user_id'            => $Card->user_id,
                    'activity'          => 'You get card successfully',
                ]);
                $Activity->save();
                
                $socialFormData = $Card->socialFormData;
                
                foreach($socialFormData as $key => $social):
                    if($key == 'youtubeVideos'):
                        $count=0;
                        foreach($social as $k => $soc):
                            foreach($soc as $keys => $s):
                                if($keys == "youtubeLink"):
                                    if(empty($s)):
                                        unset($social[$count]);
                                    endif;
                                endif;
                            endforeach;
                            $socialFormData[$key] = $social;
                            $Card->socialFormData = $socialFormData;
                        $count++;
                        endforeach;
                    endif;
                    if($key == 'vimeoVideos'):
                        $count=0;
                        foreach($social as $k => $soc):
                            foreach($soc as $keys => $s):
                                if($keys == "vimeoLink"):
                                    if(empty($s)):
                                        unset($social[$count]);
                                    endif;
                                endif;
                            endforeach;
                            $socialFormData[$key] = $social;
                            $Card->socialFormData = $socialFormData;
                        $count++;
                        endforeach;
                    endif;
                    if($key == 'umyotubeVideos'):
                        $count=0;
                        foreach($social as $k => $soc):
                            foreach($soc as $keys => $s):
                                if($keys == "umyotubeLink"):
                                    if(empty($s)):
                                        unset($social[$count]);
                                    endif;
                                endif;
                            endforeach;
                            $socialFormData[$key] = $social;
                            $Card->socialFormData = $socialFormData;
                        $count++;
                        endforeach;
                    endif;
                endforeach;
                
                
                return response()->json(['status'=>'Success', 'Card' => $Card]);
            else:
                return response()->json(['status'=>'Success', "message" => 'No card found']);
            endif;
        else:
            return response()->json(['status'=>'error', 'message' => 'You have not purchased package.']);
        endif;
    }
    
    public function deleteCard(Request $request)
    {
        $header = $request->header('Authorization');
        if(!$header)
        {
            return response()->json(['Failed'=>'Unauthenticated']);
        }
        
        $card_id = request('card_id');

        $Card = Card::find($card_id);
        
        $user_id = $Card->user_id;
        
        if($Card->delete()):
        
        $Activity = new Activity([
            'user_id'            => $user_id,
            'activity'          => 'You deleted card successfully',
        ]);
        $Activity->save();
        
        return response()->json(['status'=>'Success', "message" => 'Successfully Deleted Card']);
        else:
            return response()->json(['status'=>'Success', "message" => 'Failed to delete card']);
        endif;
    }
    
    public function updateCard(Request $request)
    {
        $header = $request->header('Authorization');
        if(!$header)
        {
            return response()->json(['Failed'=>'Unauthenticated']);
        }
        
        $card_id = request('card_id');
        if($card_id == ''):
            return response()->json(['status'=>'Failed', "message" => 'Please provide card id']);
        endif;


        $Card = Card::find($card_id);

        $user_id                = request('user_id');
        $infoFormData           = request('infoFormData');
        $socialFormData         = request('socialFormData');
   
        $change_photo           = request('change_photo');
        $change_logo            = request('change_logo');
        $changeProductImages    = request('changeProductImages');
        $colorTheme             = request('colorTheme');
        $cardTitle             = request('cardTitle');
        
        $Card->user_id            = $user_id;
        $Card->infoFormData       = $infoFormData;
        $Card->socialFormData     = $socialFormData;
        $Card->colorTheme         = $colorTheme;
        $Card->change_photo       = $change_photo;
        $Card->change_logo        = $change_logo;
        $Card->changeProductImages= $changeProductImages;
        $Card->cardTitle= $cardTitle;

        if($Card->save()):
            
        $Activity = new Activity([
            'user_id'            => $user_id,
            'activity'          => 'You updated card successfully',
        ]);
        $Activity->save();
        
        $Card->status = 200;

        $infoFormData = json_decode($Card->infoFormData, true);
        $socialFormData = json_decode($Card->socialFormData, true);
        
        $Card->infoFormData = $infoFormData;
        $Card->socialFormData = $socialFormData;
        
        return response()->json(['status'=>'Success', "message" => 'Successfully updated Card', 'Card' => $Card]);
        else:
            return response()->json(['status'=>'Success', "message" => 'Failed to update card']);
        endif;
    }

    public function incrementSend(Request $request)
    {
        $card_id = request('card_id');

        $Card = Card::find($card_id);
        
        $user_id = $Card->user_id;
        
        DB::table('cards')->where('id', $card_id)->increment('sends', 1);
        
        $Activity = new Activity([
            'user_id'            => $user_id,
            'activity'          => 'You shared card successfully',
        ]);
        $Activity->save();
        
        return response()->json(['msg'=>'Success', 'status' => 200]);
    }
    
    public function incrementTotalViews(Request $request)
    {
        $card_id = request('card_id');

        $Card = Card::find($card_id);
        
        $user_id = $Card->user_id;
        
        DB::table('cards')->where('id', $card_id)->increment('totalViews', 1);
        
         $Activity = new Activity([
            'user_id'            => $user_id,
            'activity'          => 'You viewed card successfully',
        ]);
        $Activity->save();
        
        return response()->json(['msg'=>'Success', 'status' => 200]);
    }
    
    
    public function getActivity(Request $request)
    {
        $header = $request->header('Authorization');
        if(!$header)
        {
            return response()->json(['Failed'=>'Unauthenticated']);
        }
        
        $user_id = request('user_id');

        $Activitys = Activity::where('user_id', $user_id)->orderBy('id', 'DESC')->get();
        
        return response()->json(['status'=>'Success', 'Activity' => $Activitys]);
    }
    
    public function deleteActivity(Request $request)
    {
        $header = $request->header('Authorization');
        if(!$header)
        {
            return response()->json(['Failed'=>'Unauthenticated']);
        }
        
        $activity_id = request('activity_id');

        $Activity = Activity::find($activity_id);

        if($Activity->delete()):
        return response()->json(['status'=>'Success', "message" => 'Successfully Deleted Activity']);
        else:
            return response()->json(['status'=>'Success', "message" => 'Failed to delete Activity']);
        endif;
    }
    
    public function changePassword(Request $request)
    {
        $header = $request->header('Authorization');
        if(!$header)
        {
            return response()->json(['Failed'=>'Unauthenticated']);
        }
        
        $rules1 = [
            'email'        => 'required',
        ];
        $Validator = Validator::make($request->all(), $rules1);
        
        if ($Validator->fails()) {
            return response()->json(['status'=>'Failed', 'message'=>'Email is required']);
        }

        $rules2 = [
            'new_password'     => 'required',
        ];
        $Validator = Validator::make($request->all(), $rules2);
        if ($Validator->fails()) {
            return response()->json(['status'=>'Failed', 'message'=>'Password is required']);
        }

        $rules3 = [
            'new_password'     => 'min:6',
        ];
        
        $Validator = Validator::make($request->all(), $rules3);
        if ($Validator->fails()) {
            return response()->json(['status'=>'Failed', 'message'=>'The password must be at least 6 characters.']);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
           return (['status'=>'Failed', 'message'=>'User not found']);
        }
        
        if (!Hash::check($request->old_password, $user->password)) {
            // The passwords matches
            return response()->json(['status'=>'Failed', 'message'=>'Your current password does not matches with the password you provided. Please try again.']);
        }
        
        $user->password = bcrypt($request->get('new_password'));
        
        if ($user->save()) {
            return (['status'=>'Success', 'message'=>'Password Changes Successfully']);
        }else{
            return (['status'=>'Failed', 'message'=>'Failed to reset. please try again.']);
        }
    }
    
    public function editUser(Request $request)
    {
        
        $header = $request->header('Authorization');
        if(!$header)
        {
            return response()->json(['Failed'=>'Unauthenticated']);
        }
        
        $user = User::find($request->user_id);
        if (!$user) {
           return (['status'=>'Failed', 'message'=>'User not found']);
        }
        if(isset($request->firstname)):
        $user->firstname = $request->firstname;
        endif;
        if(isset($request->lastname)):
        $user->lastname = $request->lastname;
        endif;
        if(isset($request->phone)):
        $user->phone = $request->phone;
        endif;
        if(isset($request->email)):
        $user->email = $request->email;
        endif;
        if(isset($request->gender)):
        $user->gender = $request->gender;
        endif;
        if(isset($request->time_zone_country)):
        $user->time_zone_country = $request->time_zone_country;
        endif;
        if(isset($request->time_zone_time)):
        $user->time_zone_time = $request->time_zone_time;
        endif;
        if(isset($request->DOB)):
        $user->DOB = $request->DOB;
        endif;
        if(isset($request->address)):
        $user->address = $request->address;
        endif;
        if(isset($request->company)):
        $user->company = $request->company;
        endif;
        if(isset($request->job_title)):
        $user->job_title = $request->job_title;
        endif;
        if(isset($request->website)):
        $user->website = $request->website;
        endif;
        if(isset($request->facebook_url)):
        $user->facebook_url = $request->facebook_url;
        endif;
        if(isset($request->twitter_url)):
        $user->twitter_url = $request->twitter_url;
        endif;
        if(isset($request->linkedin_url)):
        $user->linkedin_url = $request->linkedin_url;
        endif;
        if(isset($request->youtube_url)):
        $user->youtube_url = $request->youtube_url;
        endif;
        
        if(isset($request->city)):
        $user->city = $request->city;
        endif;
        if(isset($request->country)):
        $user->country = $request->country;
        endif;
        if(isset($request->profile_image)):
        $user->profile_image = $request->profile_image;
        endif;
        
        if(isset($request->package_id)):
        $user->package_id = $request->package_id;
        endif;
        
        if(isset($request->business_type)):
            $user->business_type = $request->business_type;
        endif;
        if(isset($request->sub_category)):
            $user->sub_category = $request->sub_category;
        endif;
           
        if(isset($request->sport_type)):
            $user->sport_type = $request->sport_type;
        endif;
        
        if(isset($request->age_type)):
            $user->age_type = $request->age_type;
        endif;
        
        if(isset($request->position)):
            $user->position = $request->position;
        endif;
    
        if(isset($request->state)):
            $user->state = $request->state;
        endif;
        
        if ($user->save()) {
            return (['status'=>'Success', 'message'=>'User Info Updated Successfully', 'User' => $user ]);
        }else{
            return (['status'=>'Failed', 'message'=>'Failed to update. please try again.']);
        }
    }
    
    public function checkEmail(Request $request)
    {
        $email = $request->email;
        if (!$email) {
           return (['status'=>'Failed', 'message'=>'Email is Required.']);
        }
        
        $user = User::where('email', $request->email)->first();
        if ($user) {
            return (['status'=>'Success', 'message'=>'Email is already registered.']);
        }else{
            return (['status'=>'Failed', 'message'=>'Email is not registered.']);
        }
    }
    
    public function addCategory(Request $request)
    {
        $business_type = $request->business_type;
        $sub_category  = $request->sub_category;
        
        if (!$business_type) {
           return (['status'=>'Failed', 'message'=>'business_type is Required.']);
        }
        
        if (!$sub_category) {
          return (['status'=>'Failed', 'message'=>'sub_category is Required.']);
        }
        
        $Category = new Category([
            'business_type'  => $business_type,
            'sub_category'   => $sub_category,
        ]);
        
        if ($Category->save()) {
            return (['status'=>'Success', 'Message'=>'Category added']);
        }else{
            return (['status'=>'Failed', 'message'=>'Failed to add category.']);
        }
    }

    public function allCategories()
    {
        $Categories = Category::select('business_type')->distinct('business_type')->orderBy('business_type')->get();
        if ($Categories) {
            return (['status'=>'Success', 'Categories'=>$Categories]);
        }else{
            return (['status'=>'Failed', 'message'=>'Not Found.']);
        }
    }
    
    public function subCategories(Request $request)
    {
        $business_type = $request->business_type;
        if (!$business_type) {
           return (['status'=>'Failed', 'message'=>'Main Category is Required.']);
        }
        
        $Categories = Category::select('sub_category')->where('business_type', $business_type)->orderBy('sub_category')->get();
        if($Categories) {
            return (['status'=>'Success', 'Categories'=>$Categories]);
        }else{
            return (['status'=>'Failed', 'message'=>'Not Found.']);
        }
    }
    
    public function getSearchCardByDropdown(Request $request)
    {

        $business_type  = $request->business_type;
        $age_type       = $request->age_type;
        $position       = $request->position;
        $sport_type     = $request->sport_type;
        $state          = $request->state;
        

        
        $UserIds = [];
        
        if(isset($business_type)):
            $business_type_ids  = User::select('id')->where('business_type', $business_type)->latest()->get();
            foreach($business_type_ids as $business_type_id):
                array_push($UserIds, $business_type_id->id);
            endforeach;
        endif;
        
        if(isset($age_type)):
            $age_type_ids = User::select('id')->where('age_type', $age_type)->latest()->get();
            foreach($age_type_ids as $age_type_id):
                array_push($UserIds, $age_type_id->id);
            endforeach;
        endif;
        
        if(isset($position)):
            $position_ids = User::select('id')->where('position', $position)->latest()->get();
            foreach($position_ids as $position_id):
                array_push($UserIds, $position_id->id);
            endforeach;
        endif;
        
        
        if(isset($sport_type)):
            $sport_type_ids = User::select('id')->where('sport_type', $sport_type)->latest()->get();
            foreach($sport_type_ids as $sport_type_id):
                array_push($UserIds, $sport_type_id->id);
            endforeach;
        endif;
        
        if(isset($state)):
            $sport_type_ids = User::select('id')->where('state', $state)->latest()->get();
            foreach($sport_type_ids as $sport_type_id):
                array_push($UserIds, $sport_type_id->id);
            endforeach;
        endif;
                                    
        $cards = Card::whereIn('user_id', $UserIds)->latest()->get();
        
        $cardsData = [];
        $cardsData1 = [];
        
        if($business_type == "Athlete"):
            $card = Card::find(251);
            if(isset($card)):
                $cardsData['id']        = $card->id;
                $data                   = json_decode($card->infoFormData);
                $cardsData['cardTitle'] = $data->cardTitle;
                array_push($cardsData1, $cardsData);
            endif;
        endif;
            
        foreach($cards as $card):
            $cardsData['id']        = $card->id;
            $data                   = json_decode($card->infoFormData);
            $cardsData['cardTitle'] = $data->cardTitle;
            
            array_push($cardsData1, $cardsData);
        endforeach;
        
        if($cards) {
            return (['status'=>'Success', 'Cards'=>$cardsData1]);
        }else{
            return (['status'=>'Failed', 'message'=>'Not Found.']);
        }
    }


    

    
    public function getSearchCardByDropdownproliving(Request $request)
    {

        $business_type  = $request->business_type;
        // $age_type       = $request->age_type;
        // $position       = $request->position;
        // $sport_type     = $request->sport_type;
        $state          = $request->state;
        $location=$request->location;
        $race=$request->race;
        $gender=$request->gender;
        $city=$request->city;
        $name=$request->name;
        

        
        $UserIds = [];
        
        if(isset($business_type)):
            $business_type_ids  = User::select('id')->where('business_type', $business_type)->latest()->get();
            foreach($business_type_ids as $business_type_id):
                array_push($UserIds, $business_type_id->id);
            endforeach;
        endif;
        
        if(isset($location)):
            $location_ids = User::select('id')->where('location', $location)->latest()->get();
            foreach($location_ids as $location_id):
                array_push($UserIds, $location_id->id);
            endforeach;
        endif;
        
        if(isset($race)):
            $race_ids = User::select('id')->where('race', $race)->latest()->get();
            foreach($race_ids as $race_id):
                array_push($UserIds, $race_id->id);
            endforeach;
        endif;
        
        
        if(isset($gender)):
            $gender_ids = User::select('id')->where('gender', $gender)->latest()->get();
            foreach($gender_ids as $gender_id):
                array_push($UserIds, $gender_id->id);
            endforeach;
        endif;
        
        if(isset($state)):
            $sport_type_ids = User::select('id')->where('state', $state)->latest()->get();
            foreach($sport_type_ids as $sport_type_id):
                array_push($UserIds, $sport_type_id->id);
            endforeach;
        endif;

        if(isset($city)):
            $city_ids = User::select('id')->where('city', $city)->latest()->get();
            foreach($city_ids as $city_id):
                array_push($UserIds, $city_id->id);
            endforeach;
        endif;

        if(isset($name)):
            $name_ids = User::select('id')->where('firstname', $name)->latest()->get();
            foreach($name_ids as $name_id):
                array_push($UserIds, $name_id->id);
            endforeach;
        endif;
                                    
        $cards = Card::whereIn('user_id', $UserIds)->latest()->get();
        
        $cardsData = [];
        $cardsData1 = [];
        
        if($business_type == "Athlete"):
            $card = Card::find(251);
            if(isset($card)):
                $cardsData['id']        = $card->id;
                $data                   = json_decode($card->infoFormData);
                $cardsData['cardTitle'] = $data->cardTitle;
                array_push($cardsData1, $cardsData);
            endif;
        endif;
            
        foreach($cards as $card):
            $cardsData['id']        = $card->id;
            $data                   = json_decode($card->infoFormData);
            $cardsData['cardTitle'] = $data->cardTitle;
            
            array_push($cardsData1, $cardsData);
        endforeach;
        
        if($cards) {
            return (['status'=>'Success', 'Cards'=>$cardsData1]);
        }else{
            return (['status'=>'Failed', 'message'=>'Not Found.']);
        }
        
    }
    




    public function SearchByCategories(Request $request)
    {
        $search = $request->search;
        if (!$search) {
           return (['status'=>'Failed', 'message'=>'Search is Required.']);
        }
        
        $UserIds = User::select('id')
                                    ->where('business_type', 'LIKE', '%'.$search.'%')
                                    // ->orWhere('sub_category', 'LIKE', '%'.$search.'%')
                                    // ->orWhere('firstname', 'LIKE', '%'.$search.'%')
                                    // ->orWhere('lastname', 'LIKE', '%'.$search.'%')
                                    ->orWhere('sport_type', 'LIKE', '%'.$search.'%')
                                    ->orWhere('age_type', 'LIKE', '%'.$search.'%')
                                    ->orWhere('position', 'LIKE', '%'.$search.'%')
                                    ->latest()
                                    ->get();
        // $cards = Card::->latest()->get();
        $cards = Card::whereIn('user_id', $UserIds)->orWhere('cardTitle', 'LIKE', $search.'%')->latest()->get();
        
        $cardsData = [];
        $cardsData1 = [];
        foreach($cards as $card):
            $cardsData['id']        = $card->id;
            $data                   = json_decode($card->infoFormData);
            $cardsData['cardTitle'] = $data->cardTitle;
            
            array_push($cardsData1, $cardsData);
        endforeach;
        
        if($cards) {
            return (['status'=>'Success', 'Cards'=>$cardsData1]);
        }else{
            return (['status'=>'Failed', 'message'=>'Not Found.']);
        }
    }
    
    
    public function categories()
    {
        $BusinessType  = Category::select('business_type')->distinct('business_type')->get();
        $subCategories = Category::select('sub_category')->latest()->get();
        return (['status'=>'Success', 'BusinessType'=>$BusinessType, 'subCategories'=>$subCategories]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
