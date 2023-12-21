<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Validator;

class SupportAgentController extends Controller
{
    public function addSupportAgent(Request $request)
    {
        $name     = request('name');
        $email    = request('email');
        $password = request('password');
        if(!isset($name)):
            return response()->json(['status'=>'Failed', "message" => 'Name is required']);
        endif;
        if(!isset($email)):
            return response()->json(['status'=>'Failed', "message" => 'Email is required']);
        endif;
        if(!isset($password)):
            return response()->json(['status'=>'Failed', "message" => 'Password is required']);
        endif;
        
        $rules1 = [
            'email' => 'unique:users',
        ];

        $Validator = Validator::make($request->all(), $rules1);

        if ($Validator->fails()) {
            return response()->json(['status'=>'Failed', 'message'=>'The email has already been taken.']);
        }

        $rules2 = [
            'password' => 'min:6',
        ];

        $Validator = Validator::make($request->all(), $rules2);
        if ($Validator->fails()) {
            return response()->json(['status'=>'Failed', 'message'=>'The password must be at least 6 characters.']);
        }
        
        $User = new User([
            'firstname'  => $request->get('name'),
            'lastname'   => '',
            'email'      => $request->get('email'),
            'password'   => bcrypt($request->get('password')),
            'is_verified'   => 'Yes',
            'user_type' => 'support',
        ]);
        
        if ($User->save()) {
            return response()->json(['status'=>'Success', 'message'=>'Support Agent added successfully.']);
        }
    }
    
    public function updateSupportAgent(Request $request)
    {
        $name     = request('name');
        $email    = request('email');
        $id       = request('id');
        if(!isset($id)):
            return response()->json(['status'=>'Failed', "message" => 'Id is required']);
        endif;
        
        $user = User::find($id);
        if(!isset($user)):
            return response()->json(['status'=>'Failed', "message" => 'User not found.']);
        endif;
        
        if(isset($email)):
            $rules1 = [
                'email' => 'unique:users',
            ];
    
            $Validator = Validator::make($request->all(), $rules1);
    
            if ($Validator->fails()) {
                return response()->json(['status'=>'Failed', 'message'=>'The email has already been taken.']);
            }
            $user->email = $email;
        endif;
        
        if(isset($name)):
            $user->firstname = $name;
        endif;
        
        if ($user->save()) {
            return response()->json(['status'=>'Success', 'message'=>'Support Agent updated successfully.']);
        }
    }
    
    public function deleteSupportAgent(Request $request)
    {
        $id       = request('id');
        if(!isset($id)):
            return response()->json(['status'=>'Failed', "message" => 'Id is required']);
        endif;
        
        $user = User::find($id);
        if(!isset($user)):
            return response()->json(['status'=>'Failed', "message" => 'User not found.']);
        endif;
        if ($user->delete()) {
            return response()->json(['status'=>'Success', 'message'=>'Support Agent deleted successfully.']);
        }
    }
    
    public function getSupportAgent()
    {
        $agents = User::select('firstname as name', 'email', 'id')->where('user_type', 'support')->latest()->get();
        
        if($agents):
            return response()->json(['status'=>'Success', 'message'=>'Support Agents Found.', 'Agents' => $agents]);
        else:
            return response()->json(['status'=>'Failed', 'message'=>'No Support Agent Found.']);
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
    
        
    public function admin_text(Request $request)
    {
        $admin_text       = request('admin_text');
        // if(!isset($admin_text)):
        //     return response()->json(['status'=>'Failed', "message" => 'Admin Text is required']);
        // endif;
        
        $admin = User::find(10);
        
        $admin->admin_text = $admin_text;
        
        $admin->save();
        
        return response()->json(['status'=>'Success', 'message'=> $admin->admin_text]);
    }
    
    public function GetAdminText()
    {
        $admin = User::find(10);
        
        return response()->json(['status'=>'Success', 'message'=> $admin->admin_text]);
    }
}
