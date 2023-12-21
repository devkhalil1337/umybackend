<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\User;
use Validator;
use Hash;
use Auth;
use App\Models\Card;

class PackageController extends Controller
{

    public function updateUserPackage(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $user_id = request('user_id');
        $package_id = request('package_id');
        
        if(!isset($user_id)):
            return response()->json(['status'=>'Failed', "message" => 'User id is required']);
        endif;
        
        $user = User::find($user_id);
        if(empty($user)):
            return response()->json(['status'=>'Failed', "message" => 'User not found']);
        endif;
        
        if(!isset($package_id)):
            return response()->json(['status'=>'Failed', "message" => 'Package id is required']);
        endif;
        
        $package = Package::find($package_id);
        if(empty($package)):
            return response()->json(['status'=>'Failed', "message" => 'Package not found']);
        endif;

        // handle date of expiry
        // get package details by package_id selected by user
        $packageDeta = Package::find($package_id);
        // add days - to date_of_expiry to user_id
        $daysOfExpiry = $packageDeta->expire_in;
        
        $date_of_expiry = \Carbon\Carbon::now()->addDays($daysOfExpiry);
        
        $User = User::find($user_id);
        
        $User->date_of_expiry = $date_of_expiry;
        $User->package_id     = $package_id;

        
        if($User->save()):
            $User->status = 200;
            return response()->json(['status'=>'Success', "message" => 'User Package is upgraded successfully', 'User' => $User]);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Failed to upgrade Package']);
        endif;
        
    }
    
    public function createPackage(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $price        = request('price');
        $description  = request('description');
        $image        = request('image');
        $limit        = request('limit');
        $expire_in    = request('expire_in');
        $logo               = request('logo');
        $product_image      = request('product_image');
        $social_media_listing    = request('social_media_listing');
        $website            = request('website');
        $videos             = request('videos');
        $net_price          = request('net_price');
        
        $vimeo             = request('vimeo');
        $umyotube             = request('umyotube');
        if(!isset($vimeo)):
            $vimeo = 'No video';
        endif;
        if(!isset($umyotube)):
            $umyotube = 'No video';
        endif;
        
        // if(!$price)
        // {
        //     return response()->json(['Failed'=>'Price is required']);
        // }
        
        if(!$description)
        {
            return response()->json(['Failed'=>'Description is required']);
        }

        $Package = new Package([
            'price'         => $price,
            'description'   => $description,
            'image'         => $image,
            'limit'         => $limit,
            'expire_in'    => $expire_in,
            'logo'          => $logo,
            'product_image' => $product_image,
            'social_media_listing'    => $social_media_listing,
            'website'       => $website,
            'videos'        => $videos,
            'net_price'     => $net_price,
            'vimeo'        => $vimeo,
            'umyotube'        => $umyotube,
        ]);

        if($Package->save()):
        $Package->status = 200;
        
        return response()->json(['status'=>'Success', "message" => 'Successfully created Package', 'Package' => $Package]);
        else:
            return response()->json(['status'=>'Success', "message" => 'Failed to create Package']);
        endif;
    }
    
    public function updatePackage(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        $id           = request('id'); 
        
        $Package = Package::find($id);
        
        if(request('price')):
            $price        = request('price');
        else:
            $price = $Package->price;
        endif;
        
        if(request('description')):
            $description = request('description');
        else:
            $description = $Package->description;
        endif;
        
        if(request('image')):
            $image        = request('image');
        else:
            $image = $Package->image;
        endif;
        
        if(request('limit')):
            $limit        = request('limit');
        else:
            $limit = $Package->limit;
        endif;
        
        if(request('expire_in')):
            $expire_in        = request('expire_in');
        else:
            $expire_in = $Package->expire_in;
        endif;
        
        if(request('logo')):
            $logo        = request('logo');
        else:
            $logo = $Package->logo;
        endif;
        if(request('product_image')):
            $product_image        = request('product_image');
        else:
            $product_image = $Package->product_image;
        endif;
        if(request('social_media_listing')):
            $social_media_listing   = request('social_media_listing');
        else:
            $social_media_listing   = $Package->social_media_listing;
        endif;
        if(request('website')):
            $website        = request('website');
        else:
            $website = $Package->website;
        endif;
        if(request('videos')):
            $videos        = request('videos');
        else:
            $videos = $Package->videos;
        endif;
        
         if(request('vimeo')):
            $vimeo        = request('vimeo');
        else:
            $vimeo = $Package->vimeo;
        endif;
        
        if(request('umyotube')):
            $umyotube        = request('umyotube');
        else:
            $umyotube = $Package->umyotube;
        endif;
        
        // if(request('net_price')):
        //     $net_price = request('net_price');
        // else:
            
        // endif;
        
        $net_price = request('net_price');
        
        
        $Package->price         = $price;
        $Package->description   = $description;
        $Package->image         = $image;
        $Package->limit         = $limit;
        $Package->expire_in     = $expire_in;
        
        $Package->logo          = $logo;
        $Package->product_image = $product_image;
        $Package->social_media_listing     = $social_media_listing;
        $Package->website       = $website;
        $Package->videos        = $videos;
        $Package->net_price     = $net_price;
        $Package->vimeo        = $vimeo;
        $Package->umyotube        = $umyotube;
            
        if($Package->save()):
        $Package->status = 200;
        
        return response()->json(['status'=>'Success', "message" => 'Successfully updated Package', 'Package' => $Package]);
        else:
            return response()->json(['status'=>'Success', "message" => 'Failed to update Package']);
        endif;
    }
    
    public function getPackages(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }

        $Package = Package::all();

        if($Package->count()):
        $Package->status = 200;
        
        return response()->json(['status'=>'Success', "message" => 'Successfully found Package', 'Package' => $Package]);
        else:
            return response()->json(['status'=>'Success', "message" => 'Failed to find Package']);
        endif;
    }
    
    public function deletePackage(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
    
        $id = request('id');
        $Package = Package::find($id);
        
        // check usage
        $usage = User::where('package_id', $id)->count();
        
        if($usage == 0):
            if($Package->delete()):
                $Package->status = 200;
            
                return response()->json(['status'=>'Success', "message" => 'Successfully deleted Package']);
            else:
                return response()->json(['status'=>'error', "message" => 'Failed to delete Package']);
            endif;
        else:
            return response()->json(['status'=>'error', "message" => 'Failed to delete Package. Already using by users']);
        endif;
    }
    
    public function getPackageSingle(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
    
        $id = request('id');
        $Package = Package::find($id);

        if(!empty($Package)):
        $Package->status = 200;
        
        return response()->json(['status'=>'Success', "message" => 'Successfully found Package', 'Package' => $Package]);
        else:
            return response()->json(['status'=>'Success', "message" => 'Failed to find Package']);
        endif;
    }
    
    // --------------------------------------------------------------------------------------------------------
    
    
    public function getAdmin(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $admin = User::select('email')->where('id', 10)->first();

        if(!empty($admin)):
        $admin->status = 200;
        
        return response()->json(['status'=>'Success', "message" => 'Successfully found admin', 'admin' => $admin]);
        else:
            return response()->json(['status'=>'Success', "message" => 'Failed to find admin']);
        endif;
    }
    
    public function updateAdmin(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $email = request('email');
    
        $admin = User::find(10);

        $admin->email = $email;
        
        if($admin->save()):
        $admin->status = 200;
        
        $admin1 = User::select('email')->where('id', 10)->first();
        
        return response()->json(['status'=>'Success', "message" => 'Successfully updated admin', 'user' => $admin1]);
        else:
            return response()->json(['status'=>'Success', "message" => 'Failed to update admin']);
        endif;
    }
    
    public function updatePasswordAdmin(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
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
    
    
    public function countUser(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $userCount = User::where('id', '!=' ,10)->count();
        
        if ($userCount > 0) {
            return (['status'=>'Success', 'message'=>'User Found', 'Users' => $userCount]);
        }else{
            return (['status'=>'Failed', 'message'=>'Users not found.']);
        }
    }
    
    public function countCard(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $CardCount = Card::count();
        
        if ($CardCount > 0) {
            return (['status'=>'Success', 'message'=>'Cards Found', 'Cards' => $CardCount]);
        }else{
            return (['status'=>'Failed', 'message'=>'Cards not found.']);
        }
    }
    
    public function  CountPackageUsage(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $package_id = request('package_id');
        
        
        $countPackageUsage = User::where('package_id', $package_id)->count();
        
        if ($countPackageUsage > 0) {
            return (['status'=>'Success', 'message'=>'Usage Found', 'Count' => $countPackageUsage]);
        }else{
            return (['status'=>'Failed', 'message'=>'No Usage Fount.']);
        }
    }
    
    
    public function Usage(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $userCount = User::where('id', '!=' ,10)->count();
        $CardCount = Card::count();
        
        $Packages = Package::all();
        
        foreach($Packages as $Package):
            $countPackageUsage = User::where('package_id', $Package->id)->count();
            $Package->usage = $countPackageUsage;
        endforeach;
            
        if ($userCount > 0 OR $CardCount > 0) {
            return (['status'=>'Success', 'message'=>'Usage Found', 'UsersUsage' => $userCount, 'CardUsage' => $CardCount, 'Packages'=>$Packages]);
        }else{
            return (['status'=>'Failed', 'message'=>'Usage not found.']);
        }
        
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
