<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Models\Activity;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $user_id   = request('user_id');
        $friend_id = request('friend_id');
        $chat      = request('chat');
        
        $Message = new Message([
            'user_id'    => $user_id,
            'friend_id'  => $friend_id,
            'chat'       => $chat,
        ]);

        if($Message->save()):
            
        $user = User::find($user_id);
        $Activity = new Activity([
            'user_id'   => $friend_id,
            'activity'  => $user->firstname.' '.$user->lastname. ' sent a message to you.',
        ]);
        
        $Activity->save();
        
        $Message->status = 200;

        return response()->json(['status'=>'Success', "message" => 'Successfully sent your Message']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Failed to send your Message']);
        endif;
    }
    
    public function getMessages(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $user_id   = $request->get('user_id');
        $friend_id = $request->get('friend_id');

        if (!isset($user_id)) {
            return (['status'=>'Failed', 'message'=>'User is required']);
        }
        if (!isset($friend_id)) {
            return (['status'=>'Failed', 'message'=>'Friend is required']);
        }

        $chats = Message::where(function($query) use ($request){
            $query->where('user_id', '=', $request->user_id)->where('friend_id', '=', $request->friend_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('user_id', '=', $request->friend_id)->where('friend_id', '=', $request->user_id);
        })->orderBy('id', 'ASC')->get();
        
        // foreach($chats as $chat):
        //     if($chat->user_id == $user_id):
        //     $Message = Message::find($chat->id);
        //     $Message->read = '1';
        //     $Message->save();
        //     endif;
        // endforeach;

        if ($chats->count()) { 
            return response()->json(['status'=>'Success', 'message'=>'Messages Found.', 'Chat'=>$chats]);
        }else{
            return (['status'=>'Failed', 'message'=>'No Messages Found']);
        }
        
    }
    
    public function deleteMessage(Request $request)
    {
        // $header = $request->header('Authorization');
        // if(!$header)
        // {
        //     return response()->json(['Failed'=>'Unauthenticated']);
        // }
        
        $message_id = request('message_id');
        
        $Message = Message::find($message_id);
        if($Message->delete()):
        return response()->json(['status'=>'Success', "message" => 'Successfully deleted Message']);
        else:
            return response()->json(['status'=>'Success', "message" => "Can't delete Message"]);
        endif;
    }
    
    public function connectedUserMsg(Request $request)
    {
        $id   = $request->get('user_id');
        $msgConnectUsersIds = Message::where(function($query) use ($id){
            $query->where('user_id', '=', $id);
        })->orWhere(function ($query) use ($id) {
            $query->where('friend_id', '=', $id);
        })->orderBy('id', 'ASC')->get();

        $userID = [];
        foreach ($msgConnectUsersIds as $userIDs):
            if($userIDs->user_id != $id):
                array_push($userID, $userIDs->user_id);
            else:
                array_push($userID, $userIDs->friend_id);
            endif;
        endforeach;
        
        $msgConnectUsers = User::whereIn('id', $userID)->orderBy('id', 'DESC')->get();
        
        if ($msgConnectUsers->count()) { 
            return response()->json(['status'=>'Success', 'message'=>'Messages Found.', 'ConnectedUser'=>$msgConnectUsers]);
        }else{
            return (['status'=>'Failed', 'message'=>'No Messages Found']);
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
