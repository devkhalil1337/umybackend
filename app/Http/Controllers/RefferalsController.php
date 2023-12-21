<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Refferals;
use App\Models\User;
use Mail;
use App\Mail\SendMail;

class RefferalsController extends Controller
{
     public function applyForReferral(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $user_id     = request('user_id');
        
        $user = User::find($user_id);
        if($user->refer_code != ''):
            return response()->json(['status'=>'Failed', "message" => 'Your referal code is already created.']);
        endif;
        
        $namePart = $user->firstname;
        $phonePart = substr($user->phone, -3);
        $emailPart = strstr($user->email, '@', true);
            
        $code = $namePart.$phonePart.$emailPart;
        
        $user->refer_code = $code;
        

        if($user->save()):
            $user->status = 200;
            return response()->json(['status'=>'Success', "message" => 'Successfully created Refferal code']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Failed to create Refferal']);
        endif;
    }
    
    
    public function referalAppliedUsersList(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        
        $Refferals = Refferals::all();
        
        if($Refferals->count()):
            
            foreach($Refferals as $Refferal):
                $user = User::find($Refferal->user_id);
                $Refferal->user = $user;
            endforeach;
            
            $Refferals->status = 200;
            return response()->json(['status'=>'Success', "message" => 'Refferals Found', 'Refferal' => $Refferals]);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Failed to find Refferal']);
        endif;
    }
    
    
    public function createRefferalCode(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $referal_id   = request('referal_id');
        
        $Refferals = Refferals::find($referal_id);
        
        $user = User::find($Refferals->user_id);
        
        $namePart = $user->firstname;
        $phonePart = substr($user->phone, -3);
        $emailPart = strstr($user->email, '@', true);
            
        $code = $namePart.$phonePart.$emailPart;
        
        $user->refer_code = $code;
        
        if($user->save()):
            // delete refer now
            $Refferals->delete();
            $user->status = 200;
            // $user = Refferals::select('id', 'name','email', 'phone', 'refer_code')->find($user->id);
            return response()->json(['status'=>'Success', "message" => 'Code saved for '.$user->firstname]);

        else:
            return response()->json(['status'=>'Failed', "message" => 'Failed to save refferal code']);
        endif;
    }
    
    public function HowMuchUsersByReferalCode(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $referal_code = request('referal_code');
        
        $Users = User::where('reffered_from', $referal_code)->count();
        
        if($Users > 0):
            return response()->json(['status'=>'Success', "message" => 'Users are registerd for code '.$referal_code, 'UsersRegisterd' => $Users]);
        else:
            return response()->json(['status'=>'Failed', "message" => 'No Users registered for code '.$referal_code]);
        endif;
    }
      
    public function verify_code(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $verification_code = request('verification_code');
        $email             = request('email');
        
        $user = User::where('email', $email)->first();
        
        if($user->verification_code == $verification_code):
            $user->verification_code = '';
            $user->is_verified      = 'Yes';
            
            if($user->save()):
                return response()->json(['status'=>'Success', "message" => 'Verified']);
            else:
                return response()->json(['status'=>'Failed', "message" => 'Failed to verify']);
            endif;
        else:
            return response()->json(['status'=>'Failed', "message" => 'Failed to verify']);
        endif;
        
    }
    
    public function resend_code(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $email             = request('email');
        
        $UserCheck = User::where('email', $email)->count();
        if($UserCheck == 0):
            return response()->json(['status'=>'Failed', "message" => 'Your email is not registered with us.']);
        endif;
        $User = User::where('email', $email)->first();
        
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
        
            // send in Email
            $data = [
               'verification_code'   => $randomString,
               'username'   => $User->firstname. $User->lastname,
            ];
            Mail::send('Email.verificationCode', $data , function ($m) use ($request){
                $m->to($request->email, 'Verification Code')->from('verification@umyocards.com')->subject('Verification Code.');
            });
            
            return response()->json(['status'=>'Success', "message" => "Sent Verification Code"]);

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
