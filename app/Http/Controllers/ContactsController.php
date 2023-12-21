<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Contacts;
use App\Models\Activity;
use App\Models\Package;
use App\Models\Bonusamount;

class ContactsController extends Controller
{
    public function searchUser(Request $request)
    {
        $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }listOfReferals
        
        $query = request('query');
        $user_id = request('user_id');
        
        if(!isset($query))
        {
            return response()->json(['status'=>'Failed', 'message'=>'Query is required']);
        }
        if(!isset($user_id))
        {
            return response()->json(['status'=>'Failed', 'message'=>'User id is required']);
        }
        
        $user = User::Where('id', '!=', $user_id)
                      ->Where('id', '!=', 10)
                      ->where('firstname', 'LIKE', '%'.$query.'%')
                      ->orWhere('lastname', 'LIKE', '%'.$query.'%')
                      ->orWhere('country', $query)
                      ->orWhere('business_type', $query)
                      ->get();

        if($user->count()):
        return response()->json(['status'=>'Success', "message" => 'User Found', 'Users' => $user]);
        else:
            return response()->json(['status'=>'Success', "message" => 'No User Found']);
        endif;
    }
    
    public function userData(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $user_id    = request('user_id');
        $friend_id  = request('friend_id');
        $user = User::find($user_id);

        if(isset($friend_id) AND !empty($user)):
            $Contacts = Contacts::where('user_id', $user_id)->where('friend_id', $friend_id)->first();
            $friend = User::find($friend_id);
            if(!empty($Contacts)){
                //$user->contact = $Contacts->status;
                $contact = $Contacts->status;
                $contactStatus = 'You requested '.$user->firstname.' '.$user->lastname;
            }else{
                $Contacts2 = Contacts::where('user_id', $friend_id)->where('friend_id', $user_id)->first();
                if(!empty($Contacts2)){
                    $contact = $Contacts2->status;
                    $contactStatus = $user->firstname.' '.$user->lastname.' requested You';
                }else{
                    $contact = 'none';
                    $contactStatus = '';
                }
            }
            if(!empty($user->refer_code)):
                $refferedUsers = User::where('reffered_from', $user->refer_code)->get();
                $packagePrice = 0;
                foreach($refferedUsers as $user):
                    $packagePrice += Package::where('id', $user->package_id)->sum('price');
                endforeach;
                $myRefers = User::where('reffered_from', $user->refer_code)->count();
            else:
                $myRefers = 0;
                $packagePrice = 0;
            endif;
            if(isset($user->package_id)):
                $limit = Package::find($user->package_id);
                $user->limit = $limit->limit;
            else:
                 $user->limit = 0;
            endif;
            
            return response()->json(['status'=>'Success', "message" => 'User Found', 'Users' => $user, 'friend' => $friend, 'contacted'=>$contact, 'contactStatus'=>$contactStatus, 'totalReffers'=>$myRefers, 'packagePrice'=>$packagePrice]);
        elseif(!isset($friend_id) AND !empty($user)):
            
            if(!empty($user->refer_code)):
                $refferedUsers = User::where('reffered_from', $user->refer_code)->get();
                
                $packagePrice = 0;
                foreach($refferedUsers as $userReffered):
                    $packagePrice += Package::where('id', $userReffered->package_id)->sum('price');
                endforeach;
                
                $myRefers = User::where('reffered_from', $user->refer_code)->count();
            else:
                $myRefers = 0;
                $packagePrice = 0;
            endif;
            
            if(!empty($user->package_id)):
                $limit = Package::find($user->package_id);
                if(isset($limit)):
                    $user->limit = $limit->limit;
                else:
                    $user->limit = 0;
                endif;
            else:
                $user->limit = 0;
                $limit = [];
            endif;
            
            $packageData = Package::select('logo', 'product_image', 'social_media_listing', 'website', 'videos', 'vimeo', 'umyotube')->find($user->package_id);
            $user->PackageData = $packageData;
            
            return response()->json(['status'=>'Success', "message" => 'User Found', 'Users' => $user, 'totalReffers'=>$myRefers, 'packagePrice'=>$packagePrice]);
        else:
            return response()->json(['status'=>'Success', "message" => 'No User Found']);
        endif;
    }
    
    public function myRequests(Request $request)
    {
        $header = $request->header('Authorization');
        if(!$header)
        {
            return response()->json(['Failed'=>'Unauthenticated']);
        }
        
        $user_id = request('user_id');
        // $requests = Contacts::where('user_id', $user_id)->orWhere('friend_id', $user_id)->get();
        
        $requestsSentByMe     = Contacts::where('user_id', $user_id)->where('status', 'pending')->get();
        $requestsRecievedByMe = Contacts::Where('friend_id', $user_id)->where('status', 'pending')->get();

        if($requestsRecievedByMe->count() OR $requestsSentByMe->count()):
            if($requestsRecievedByMe->count()):
            foreach($requestsRecievedByMe as $request):
                
                if($request->friend_id == $user_id):
                    $user = User::find($request->user_id);
                elseif($request->user_id == $user_id):
                    $user = User::find($request->friend_id);
                endif;
                $request->frnd = $user;
            endforeach;
            else:
                $requestsRecievedByMe = '';
            endif;
            if($requestsSentByMe->count()):
            foreach($requestsSentByMe as $request):
                if($request->friend_id == $user_id):
                    $user = User::find($request->user_id);
                elseif($request->user_id == $user_id):
                    $user = User::find($request->friend_id);
                endif;
                $request->frnd = $user;
            endforeach;
            else:
                $requestsSentByMe = '';
            endif;
            
        return response()->json(['status'=>'Success', "message" => 'Requests Found', 'requestsSentByMe' => $requestsSentByMe, 'requestsRecievedByMe' => $requestsRecievedByMe]);
        else:
            return response()->json(['status'=>'Success', "message" => 'No Requests Found']);
        endif;
    }
    
    public function sendRequest(Request $request)
    {
        $header = $request->header('Authorization');
        if(!$header)
        {
            return response()->json(['Failed'=>'Unauthenticated']);
        }
        
        $user_id   = request('user_id');
        $friend_id = request('friend_id');
        
        $ContactCheck = Contacts::where('user_id', $user_id)->where('friend_id', $friend_id)->first();
        if($ContactCheck){
            return response()->json(['status'=>'Failed', "message" => 'You already sent request']);
        }
        $ContactCheck1 = Contacts::where('friend_id', $user_id)->where('user_id', $friend_id)->first();
        if($ContactCheck1){
            return response()->json(['status'=>'Failed', "message" => 'You already recieved request']);
        }
        
        
        $Contacts = new Contacts([
            'user_id'    => $user_id,
            'friend_id'  => $friend_id,
        ]);

        if($Contacts->save()):
            
        $friend = User::find($friend_id);
        $Activity = new Activity([
            'user_id'            => $user_id,
            'activity'          => 'You sent request to '.$friend->firstname.' '.$friend->lastname,
        ]);
        $Activity->save();
        
        $user = User::find($user_id);
        $Activity = new Activity([
            'user_id'            => $friend->id,
            'activity'          => $user->firstname.' '.$user->lastname. ' sent you a request',
        ]);
        $Activity->save();
        
        $Contacts->status = 200;

        return response()->json(['status'=>'Success', "message" => 'Successfully sent your request']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Failed to send your request']);
        endif;
    }
    
    
    public function myContacts(Request $request)
    {
        $header = $request->header('Authorization');
        if(!$header)
        {
            return response()->json(['Failed'=>'Unauthenticated']);
        }
        
        $user_id = request('user_id');
        $myContacts1 = Contacts::where('user_id', $user_id)->orWhere('friend_id', $user_id)->Where('status', 'Accepted')->get()->toArray();
        
        $myContacts = [];
        foreach($myContacts1 as $myContact):
            if($myContact['status'] == 'Accepted'):
                array_push($myContacts, $myContact);
            endif;
        endforeach;
        
        //$myContacts = Contacts::where('user_id', $user_id)->Where('status', 'Accepted')->get()->toArray();
        
        $newContact = [];
        if(!empty($myContacts)):
            foreach($myContacts as $Contact):
                
                if($Contact['user_id'] == $user_id):
                    $user = User::find($Contact['friend_id']);
                elseif($Contact['friend_id'] == $user_id):
                    $user = User::find($Contact['user_id']);
                endif;
                
                $Contact['user'] = $user;
                array_push($newContact, $Contact);
            endforeach;
            
        return response()->json(['status'=>'Success', "message" => 'Contacts Found', 'contacts' => $newContact]);
        else:
            return response()->json(['status'=>'Success', "message" => 'No contacts Found']);
        endif;
    }
    
    public function acceptRequest(Request $request)
    {
        $header = $request->header('Authorization');
        if(!$header)
        {
            return response()->json(['Failed'=>'Unauthenticated']);
        }
        
        $request_id = request('request_id');
        
        $Contacts = Contacts::find($request_id);
        $Contacts->status = 'Accepted';
        
        if($Contacts->save()):
            
            $user_id = $Contacts->user_id;
            $friend_id = $Contacts->friend_id;
            
            $friend = User::find($friend_id);
            $Activity = new Activity([
                'user_id'            => $user_id,
                'activity'          => 'You accept request of '.$friend->firstname.' '.$friend->lastname,
            ]);
            $Activity->save();
            
            $user = User::find($user_id);
            $Activity = new Activity([
                'user_id'            => $friend->id,
                'activity'          => $user->firstname.' '.$user->lastname. ' accept your request',
            ]);
            $Activity->save();
            
        return response()->json(['status'=>'Success', "message" => 'Successfully accepted request']);
        else:
            return response()->json(['status'=>'Success', "message" => "Can't accepte request"]);
        endif;
    }
    
    public function deleteRequest(Request $request)
    {
        $header = $request->header('Authorization');
        if(!$header)
        {
            return response()->json(['Failed'=>'Unauthenticated']);
        }
        
        $request_id = request('request_id');
        
        $Contacts = Contacts::find($request_id);
        if($Contacts->delete()):
        return response()->json(['status'=>'Success', "message" => 'Successfully deleted request']);
        else:
            return response()->json(['status'=>'Success', "message" => "Can't delete request"]);
        endif;
    }
    
    public function deleteUser(Request $request)
    {
        $header = $request->header('Authorization');
        if(!$header)
        {
            return response()->json(['Failed'=>'Unauthenticated']);
        }
        
        $user_id = request('user_id');
        
        $User = User::find($user_id);
        if($User->delete()):
        return response()->json(['status'=>'Success', "message" => 'Successfully deleted user']);
        else:
            return response()->json(['status'=>'Failed', "message" => "Can't delete user"]);
        endif;
    }
    
    public function listOfReferals(Request $request)
    {   
        $user_id = request('user_id');
        if(isset($user_id)):
            $referalUser = User::find($user_id);
            
            if(isset($referalUser)):
                $Bonusamount = Bonusamount::where('referal_code', $referalUser->refer_code)->latest()->get();
            else:
                return response()->json(['status'=>'Failed', "message" => "No User Found"]);
            endif;
        else:
            $Bonusamount = Bonusamount::latest()->get();
        endif;
        
        $referal1 = [];
        $referal2 = [];
        foreach($Bonusamount as $amount):
                $user = User::find($amount->user_id);
                $user2 = User::where('refer_code', $amount->referal_code)->first();
                
                if(isset($user2)):
                    $referal1["referal_email"] = $user2->email;
                endif;
                
                $referal1["whoPurchased"] = $user->email;
             
                $package = Package::find($user->package_id);
                
                $referal1["ReferalCode"] =  $amount->referal_code;
                
                if(isset($package)):
                    $referal1["totalPackageAmount"] =  $package->price;
                    
                    // Bonus 50% if amount <=100
                    // And 20%if greater then 100
                    // if($package->price <= 100):
                    //     $referal1["bonusAmount"] =  ($package->price/100)*50;
                    // else:
                    //     $referal1["bonusAmount"] =  ($package->price/100)*20;
                    // endif;
                    $referal1["bonusAmount"] =  number_format(($package->price/100)*50, 2);
                else:
                    $referal1["totalPackageAmount"] =  0;
                    $referal1["bonusAmount"] = 0;
                endif;
             
                $referal1["status"] =  $amount->status;
                $referal1["PurchasedDate"] =  substr($amount->created_at, 0, 10);
                $referal1["id"] =  $amount->id;
                array_push($referal2, $referal1);
        endforeach;
        return response()->json(['status'=>'Success', "message" => "Found", 'listOfReferals' => $referal2]);
    }
    
    public function changeStatus(Request $request)
    {
        $id = request('id');
        if(!isset($id)):
            return response()->json(['status'=>'Failed', "message" => "ID is required."]);
        endif;
        
        $Bonusamount = Bonusamount::find($id);
        $Bonusamount->status = 'Paid';
        if($Bonusamount->save()):
            return response()->json(['status'=>'Success', "message" => "Status changed to paid."]);
        else:
            return response()->json(['status'=>'Failed', "message" => "Failed to change status to paid."]);
        endif;
    }
    
    public function savePaymentInfo(Request $request)
    {
        $user_id        = request('user_id');
        $payment_id     = request('payment_id');
        $payment_type   = request('payment_type');
        
        if(!isset($user_id)):
            return response()->json(['status'=>'Failed', "message" => "User id is required."]);
        endif;
        if(!isset($payment_id)):
            return response()->json(['status'=>'Failed', "message" => "Payment id is required."]);
        endif;
        if(!isset($payment_type)):
            return response()->json(['status'=>'Failed', "message" => "Payment type is required."]);
        endif;
        
        $User = User::find($user_id);
        $User->payment_id     = $payment_id;
        $User->payment_method = $payment_type;
        
        if($User->save()):
            return response()->json(['status'=>'Success', "message" => "Payment Info Updated."]);
        else:
            return response()->json(['status'=>'Failed', "message" => "Failed to update payment info."]);
        endif;
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
