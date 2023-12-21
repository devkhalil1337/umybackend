<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use Hash;
use App\Models\Package;
use Mail;
use App\Mail\SendMail;
use App\Models\Bonusamount;
use App\Models\Contacts;
use Carbon\Models\Carbon;
use Auth;

class AuthController extends Controller
{


    public function login()
    {
        $package_id = request(['package_id']);
        
        $credentials = request(['email', 'password']);
        
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // if(isset($package_id)):
        //     $user1 = auth()->user();
            
        //     $packageData = Package::select('expire_in', 'id')->find($package_id);
            
        //     if(isset($packageData[0])):
        //         $user1->package_id       = $packageData[0]->id;
        //         $date = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
        //         $user1->date_of_expiry   = substr($date->addDays($packageData[0]->expire_in), 0, 10);
                
        //         $user1->save();
        //     // else:
        //     //     return response()->json(['status' => 'error', 'error'=> 'Package not found.']);
        //     endif;
        // endif;
        
        
        if(auth::user()->user_type == 'support' OR auth::user()->user_type == 'admin'):
            //logout and return error
            $user = auth()->user();
            $user->login = 'false';
            $user->save();
            auth()->logout();
            
            return response()->json(['status' => 'error']);
        endif;
        
        if(auth::user()->id == 10):
            $user = User::select('email')->find(auth::user()->id);
        else:
            $user = User::select('id', 'firstname', 'lastname','email', 'phone', 'date_of_expiry', 'is_verified', 'subscription_status')->find(auth::user()->id);
        endif;
        
        $user->login = 'true';
        $user->save();
        
        if(!empty($user->refer_code)):
                $refferedUsers = User::where('reffered_from', $user->refer_code)->get();
                
                $packagePrice = 0;
                foreach($refferedUsers as $userReffered):
                    $packagePrice += Package::where('id', $userReffered->package_id)->sum('price');
                endforeach;
                
                $myRefers = User::where('reffered_from', $user->refer_code)->count();
                
                $user->myRefers = $myRefers;
                $user->packagePrice = $packagePrice;
        else:
            $myRefers = 0;
            $packagePrice = 0;
            
            $user->myRefers = $myRefers;
            $user->packagePrice = $packagePrice;
        endif;
        $newToken = $this->respondWithToken($token);
        //$data = ['token'=>$newToken, 'user'=>$user];

        $data = $newToken->getData();
        $data->user = $user;
        
        // // check expiry
        // if($user->id != 10):
        //     if($user->subscription_status == 'No'):
        //         return response()->json(['status'=>'error', 'message' => 'Package is expired.']);
        //     endif;
        // endif;
        
        
        
        // if($user->date_of_expiry != NULL AND $user->date_of_expiry < date('Y-m-d')):
        //     $data->Expired = 'True';
        // else:
        //     $data->Expired = 'False';
        // endif;
        $data->status = 200;
        return response()->json($data);
    }
    
    
    public function adminSignIn()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        if(auth::user()->id == 10 OR auth::user()->user_type == 'support'):
            
            $authUser = auth::user();
            $authUser->login = 'true';
            $authUser->save();
            
            $user = User::select('id', 'firstname as name','email', 'user_type', 'login')->find(auth::user()->id);
            
            $newToken = $this->respondWithToken($token);
            $data = $newToken->getData();
            $data->admin = $user;
            $data->status = 200;
            return response()->json($data);
        else:
            return response()->json(['message' => 'Unauthorized'], 200);
        endif;
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $user = auth()->user();
        
        if(!$user):
            return response()->json(['message' => 'You are already logged out.']);
        endif;
        $user->login = 'false';
        $user->save();
        
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }


    public function register(Request $request)
    {
        $package_id = request('package_id');
        
        if(!isset($package_id)):
            return response()->json(['status'=>'Failed', "message" => 'Package id is required']);
        endif;
        
        $rules1 = [
            'email' => 'unique:users',
        ];

        $Validator = Validator::make($request->all(), $rules1);

        if ($Validator->fails()) {
            return response()->json(['status'=>'Failed', 'message'=>'The email has already been taken.']);
        }
        
        
        $rules01 = [
            'email' => 'email',
        ];

        $Validator01 = Validator::make($request->all(), $rules01);

        if ($Validator01->fails()) {
            return response()->json(['status'=>'Failed', 'message'=>'The email formate is not correct.']);
        }

        $rules2 = [
            'password' => 'min:6',
        ];

        $Validator = Validator::make($request->all(), $rules2);
        if ($Validator->fails()) {
            return response()->json(['status'=>'Failed', 'message'=>'The password must be at least 6 characters.']);
        }

        $rules3 = [
            'phone' => 'unique:users',
        ];

        $Validator = Validator::make($request->all(), $rules3);

        if ($Validator->fails()) {
            return response()->json(['status'=>'Failed', 'message'=>'The phone has already been taken.']);
        }
        
        
        // handle date of expiry
        // get package details by package_id selected by user
        $packageDeta = Package::find($request->get('package_id'));
        // add days - to date_of_expiry to user_id
        $daysOfExpiry = $packageDeta->expire_in;
        
        if($daysOfExpiry != 0):
            $date_of_expiry = \Carbon\Carbon::now()->addDays($daysOfExpiry);
        else:
            $date_of_expiry = NULL;
        endif;
  
        $User = new User([
            'firstname'  => $request->get('firstname'),
            'lastname'   => $request->get('lastname'),
            'email'      => $request->get('email'),
            'password'   => bcrypt($request->get('password')),
            'phone'      => $request->get('phone'),
            'package_id' => $request->get('package_id'),
            'reffered_from'  => $request->get('reffered_from'),
            'date_of_expiry' => $date_of_expiry,
            'is_verified'   => 'No',
            'country'  => $request->get('country'),
            'business_type'  => $request->get('business_type'),
            'sub_category'  => $request->get('sub_category'),
            'payment_id'    => $request->get('payment_id'),
            'payment_method' => $request->get('payment_method'),
            
            'balance_transaction'    => $request->get('balance_transaction'),
            'balance_transaction_type' => $request->get('balance_transaction_type'),
            
            'plan_id'           => $request->get('plan_id'),
            'subscription_id'   => $request->get('subscription_id'),
            
            'stripe_customer_id'           => $request->get('stripe_customer_id'),
            'stripe_subscription_id'        => $request->get('stripe_subscription_id'),
            
            'subscription_status'   => 'Yes',
            
            'sport_type' => $request->get('sport_type'),
            'age_type' => $request->get('age_type'),
            'position' => $request->get('position'),

            'state' => $request->get('state'),
            'city' => $request->get('city'),
            'gender' => $request->get('gender'),
            'location' => $request->get('location'),
            'race' => $request->get('race'),
        ]);

        // generate password random 8 letters
        $length = 4;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        // save in DB
        $User->verification_code = $randomString;

        if ($User->save()) {
            $user = User::find($User->id);
            
            // save following
            $friend_id = request('friend_id');
            if(isset($friend_id) AND $friend_id != 'null'):
                $Contacts = new Contacts([
                    'user_id'    => $User->id,
                    'friend_id'  => $friend_id,
                ]);
                $Contacts->save();
            endif;
            
            // save bonus amount if reffered_from is not empty
            $reffered_from  = $request->get('reffered_from');
            if(isset($reffered_from)):
                $Bonusamount = new Bonusamount([
                    'user_id'       => $User->id,
                    'referal_code'  => $reffered_from,
                    'status'        => 'Pending'
                ]);
                $Bonusamount->save();
            endif;
            
            try {
                $data = [
                    'verification_code' => $randomString,
                    'username' => $user->firstname . $user->lastname,
                ];

                Mail::send('Email.verificationCode', $data, function ($m) use ($request) {
                    $m->to($request->email, 'Verification Code')
                        ->from('verification@umyocards.com', 'umyocards')
                        ->subject('Verification Code.');
                });
            } catch (\Exception $e) {
                // Log the email sending error or handle it as appropriate
                // You can also rethrow the exception if you want to propagate it further
                // throw $e;
                return response()->json(['status' => 'Failed', 'message' => 'Error sending verification email']);
            }

            
            
            $credentials = request(['email', 'password']);

            if (! $token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $newToken = $this->respondWithToken($token);
            //$data = ['token'=>$newToken, 'user'=>$user];

            $data = $newToken->getData();
            // Then treat it as a regular json. For example...
            $data->user = $user;
            $data->status = 200;

            //$token->user = $user;
             return response()->json($data);

            // $data = ['token'=>$token, 'user'=>$user];
            // return $this->respondWithToken($data);

        }else{
            return response()->json(['status'=>'Failed', "message" => "Failed to register. Try again"]);
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
