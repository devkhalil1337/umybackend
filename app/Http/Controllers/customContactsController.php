<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\customContacts;
use DB;
use App\Models\Activity;

class customContactsController extends Controller
{
    public function saveCustomContact(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $user_id      = request('user_id');
        $firstname    = request('firstname');
        $lastname     = request('lastname');
        $tags         = request('tags');
        $phone        = request('phone');
        $email        = request('email');
        $address      = request('address');
        $gender       = request('gender');
        $company      = request('company');
        $position     = request('position');
        $website      = request('website');
        $facebook     = request('facebook');
        $twitter      = request('twitter');
        $linkedin     = request('linkedin');

        $customContacts = new customContacts([
            'user_id'      => $user_id,
            'firstname'    => $firstname,
            'lastname'     => $lastname,
            'tags'         => $tags,
            'phone'        => $phone,
            'email'        => $email,
            'address'      => $address,
            'gender'       => $gender,
            'company'      => $company,
            'position'     => $position,
            'website'      => $website,
            'facebook'     => $facebook,
            'twitter'      => $twitter,
            'linkedin'     => $linkedin,
        ]);

        if($customContacts->save()):
            
        $Activity = new Activity([
            'user_id'            => $user_id,
            'activity'          => 'You save contact successfully',
        ]);
        $Activity->save();
        
        $customContacts->status = 200;

        $phone = json_decode($customContacts->phone, true);
        $email = json_decode($customContacts->email, true);
        $address = json_decode($customContacts->address, true);
        $website = json_decode($customContacts->website, true);
        
        $customContacts->phone = $phone;
        $customContacts->email = $email;
        $customContacts->address = $address;
        $customContacts->website = $website;
        
        return response()->json(['status'=>'Success', "message" => 'Successfully saved Custom Contact', 'Contact' => $customContacts]);
        else:
            return response()->json(['status'=>'Success', "message" => 'Failed to post Contact']);
        endif;
    }
    
    
    
    public function getCustomContact(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $user_id = request('user_id');

        $customContacts = customContacts::where('user_id', $user_id)->orderBy('id', 'DESC')->get();
        
        foreach($customContacts as $Contacts):
            
            $phone = json_decode($Contacts->phone, true);
            $email = json_decode($Contacts->email, true);
            $address = json_decode($Contacts->address, true);
            $website = json_decode($Contacts->website, true);
            
            $Contacts->phone = $phone;
            $Contacts->email = $email;
            $Contacts->address = $address;
            $Contacts->website = $website;
            
        endforeach;

        return response()->json(['status'=>'Success', 'customContacts' => $customContacts]);
    }
    
    public function getCustomContactSingle(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $id = request('id');

        $Contacts = customContacts::find($id);
       
        // foreach($customContacts as $Contacts):
            
            $phone = json_decode($Contacts->phone, true);
            $email = json_decode($Contacts->email, true);
            $address = json_decode($Contacts->address, true);
            $website = json_decode($Contacts->website, true);
            
            $Contacts->phone = $phone;
            $Contacts->email = $email;
            $Contacts->address = $address;
            $Contacts->website = $website;
            
        // endforeach;

        return response()->json(['status'=>'Success', 'customContact' => $Contacts]);
    }
    
     public function deleteCustomContact(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $id = request('id');

        $customContacts = customContacts::find($id);
        
            if(!empty($customContacts)):
            $user_id = $customContacts->user_id;
    
            if($customContacts->delete()):
            
            $Activity = new Activity([
                'user_id'            => $user_id,
                'activity'          => 'You deleted custom Contacts successfully',
            ]);
            $Activity->save();
            
            return response()->json(['status'=>'Success', "message" => 'Successfully Deleted Custom Contacts']);
            else:
                return response()->json(['status'=>'Success', "message" => 'Failed to delete Custom Contacts']);
            endif;
        else:
            return response()->json(['status'=>'Success', "message" => 'Failed to delete Custom Contacts']);
        endif;
    }
    
    
    
    
    public function updateCustomContact(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $id = request('id');
        if($id == ''):
            return response()->json(['status'=>'Failed', "message" => 'Please provide id']);
        endif;


        $customContacts = customContacts::find($id);

        $firstname    = request('firstname');
        $lastname     = request('lastname');
        $tags         = request('tags');
        $phone        = request('phone');
        $email        = request('email');
        $address      = request('address');
        $gender       = request('gender');
        $company      = request('company');
        $position     = request('position');
        $website      = request('website');
        $facebook     = request('facebook');
        $twitter      = request('twitter');
        $linkedin     = request('linkedin');
        
        
        $customContacts->firstname    = $firstname;
        $customContacts->lastname     = $lastname;
        $customContacts->tags         = $tags;
        $customContacts->phone        = $phone;
        $customContacts->email        = $email;
        $customContacts->address      = $address;
        $customContacts->gender       = $gender;
        $customContacts->company      = $company;
        $customContacts->position     = $position;
        $customContacts->website      = $website;
        $customContacts->facebook     = $facebook;
        $customContacts->twitter      = $twitter;
        $customContacts->linkedin     = $linkedin;
        
        if($customContacts->save()):
        
        $user_id = $customContacts->user_id;
        
        $Activity = new Activity([
            'user_id'            => $user_id,
            'activity'          => 'You updated custom Contacts successfully',
        ]);
        $Activity->save();
        
        $customContacts->status = 200;

        $phone = json_decode($customContacts->phone, true);
        $email = json_decode($customContacts->email, true);
        $address = json_decode($customContacts->address, true);
        $website = json_decode($customContacts->website, true);
        
        $customContacts->phone = $phone;
        $customContacts->email = $email;
        $customContacts->address = $address;
        $customContacts->website = $website;
        
        return response()->json(['status'=>'Success', "message" => 'Successfully updated custom Contacts', 'customContacts' => $customContacts]);
        else:
            return response()->json(['status'=>'Success', "message" => 'Failed to update custom Contacts']);
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
