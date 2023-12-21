<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\liveChatting;
use App\Models\liveChat;
use Mail;
use App\Mail\SendMail;
use App\Models\User;

class liveChatController extends Controller
{
    public function startLiveChat(Request $request)
    {
        $name = $request->name;
        $email = $request->email;
        
        if(!isset($name)):
            return response()->json(['status'=>'Failed', "message" => 'Name is required']);
        endif;
        if(!isset($email)):
            return response()->json(['status'=>'Failed', "message" => 'Email is required']);
        endif;
    
        $token = (rand(999999,9999999999));

        $today = date('Y-m-d');
        $checkIfAlreadyHaveChat = liveChat::where('name', $name)->where('email', $email)->first();
        if(isset($checkIfAlreadyHaveChat) AND $checkIfAlreadyHaveChat->status != 'closed'):
            $checkForOnline = User::where('user_type', '!=', 'user')->where('login', 'true')->count();
        
            if($checkForOnline > 0):
                $online = 'true';
            else:
                $online = 'false';
            endif;
            
            return response()->json(['status'=>'Success', "message" => 'Live Chat Started.', 'LiveChat' => $checkIfAlreadyHaveChat, 'online'=>$online]);
        endif;

        $liveChat = new liveChat([
            'name'       => $name,
            'email'      => $email,
            'token'      => $token,
            'dated'        => date('Y-m-d'),
        ]);
        
        // check if admin or chat_support is live or not
        $checkForOnline = User::where('user_type', '!=', 'user')->where('login', 'true')->count();
        
        if($checkForOnline > 0):
            $online = 'true';
        else:
            $online = 'false';
        endif;
    
        if($liveChat->save()):
            return response()->json(['status'=>'Success', "message" => 'Live Chat Started.', 'LiveChat' => $liveChat, 'online'=>$online]);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }
    
    public function saveLiveChat(Request $request)
    {
            
        $chat = $request->chat;
        $chat_id = $request->chat_id;
        $respond_by = $request->respond_by_id; //admin or support user name
        
        $message_type     = $request->message_type;
        
        if(!isset($message_type)):
            return response()->json(['status'=>'Failed', "message" => 'Message Type is required.']);
        endif;
        
        if(!isset($chat)):
            return response()->json(['status'=>'Failed', "message" => 'Chat is required']);
        endif;
        if(!isset($chat_id)):
            return response()->json(['status'=>'Failed', "message" => 'Chat id is required']);
        endif;
        
        if(!isset($respond_by) AND $message_type == 'recieved'):
            return response()->json(['status'=>'Failed', "message" => 'Responder id is required.']);
        endif;
        
        $liveChatting = new liveChatting([
            'chat_id'           => $chat_id,
            'user_message'      => $chat,
            'message_response'  => '',
            'message_type'      => $message_type,
        ]);
        

        if($liveChatting->save()):
            if($message_type == 'recieved'): //$chat_id
                $liveChat = liveChat::find($chat_id);
                
                $liveChat->respond_by = $respond_by;
                $liveChat->status = 'process';
                $liveChat->save();
                $user = User::find($respond_by);
                $username = $user->firstname.' '.$user->lastname;
            else:
                $liveChat = liveChat::find($chat_id);
                $user = User::find($liveChat->respond_by);
                if($liveChat->respond_by != ''):
                    $username = $user->firstname.' '.$user->lastname;
                else:
                    $username = '';
                endif;
            endif;
            
            $chats = liveChatting::select('id', 'chat_id', 'user_message', 'created_at', 'message_type')->where('chat_id', $chat_id)->get();
            return response()->json(['status'=>'Success', "message" => 'Sent Message.', 'messages' => $chats, 'respond_by_name'=>$username]);
        else:
            $chats = liveChatting::where('chat_id', $chat_id)->get();
            return response()->json(['status'=>'Failed', "message" => 'Failed to send Message.', 'messages' => $chats]);
        endif;
    }
    
    public function getLiveChat(Request $request)
    {
        $chat_id = $request->chat_id;
        if(!isset($chat_id)):
            return response()->json(['status'=>'Failed', "message" => 'Chat id is required']);
        endif;
        
        $liveChat = liveChat::find($chat_id);
        
        if(!empty($liveChat->respond_by)):
            $user = User::find($liveChat->respond_by);
            $username = $user->firstname.' '.$user->lastname;
        else:
            $username = '';
        endif;
        
        $chats = liveChatting::select('id', 'chat_id', 'user_message', 'created_at', 'message_type')->where('chat_id', $chat_id)->get();
        
        if($chats):
            return response()->json(['status'=>'Success', "message" => 'Chat Found.', 'messages' => $chats, 'respond_by_name'=>$username]);
        else:
            $chats = liveChatting::where('chat_id', $chat_id)->get();
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;    
    }
    
    public function closeChat(Request $request)
    {
        $chat_id = $request->chat_id;
        if(!isset($chat_id)):
            return response()->json(['status'=>'Failed', "message" => 'Chat id is required']);
        endif;
        
        $liveChat = liveChat::find($chat_id);
        
        if(!$liveChat):
            return response()->json(['status'=>'Failed', "message" => 'Chat not found.']);
        endif;
        
        $liveChat->status = 'closed';
        if($liveChat->save()):
            return response()->json(['status'=>'Success', "message" => 'Chat Closed.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }
    
    
    public function getOpenChat(Request $request)
    {
        $support_agent_id = $request->support_agent_id;
        if(!isset($support_agent_id)):
            return response()->json(['status'=>'Failed', "message" => 'Support Agent id is required']);
        endif;
        
        $chats1 = liveChat::where('respond_by', $support_agent_id)->orWhere('status', 'open')->latest()->get();
        
        // include only that chats which have messages sent
        
        if($chats1):
            $chats = [];
            foreach($chats1 as $chat):
                $liveChatting = liveChatting::where('chat_id', $chat->id)->count();
                if($liveChatting > 0):
                    array_push($chats,$chat);
                endif;
            endforeach;
            
            if(!empty($chats)):
                return response()->json(['status'=>'Success', "message" => 'Chat Found.', 'messages' => $chats]);
            else:
                return response()->json(['status'=>'Failed', "message" => 'No Chats Found.']);
            endif;
            return response()->json(['status'=>'Success', "message" => 'Chat Found.', 'messages' => $chats]);
        else:
            $chats = liveChatting::where('chat_id', $chat_id)->get();
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }
    
    
    public function getOpenChatAdmin(Request $request)
    {
        $chats1 = liveChat::where('status', 'open')->orWhere('status', 'process')->latest()->get();
        
        // include only that chats which have messages sent
        if($chats1):
            $chats = [];
            foreach($chats1 as $chat):
                $liveChatting = liveChatting::where('chat_id', $chat->id)->count();
                if($liveChatting > 0):
                    array_push($chats,$chat);
                endif;
            endforeach;
            
            if(!empty($chats)):
                return response()->json(['status'=>'Success', "message" => 'Chat Found.', 'messages' => $chats]);
            else:
                return response()->json(['status'=>'Failed', "message" => 'No Chats Found.']);
            endif;
        else:
            $chats = liveChatting::where('chat_id', $chat_id)->get();
            return response()->json(['status'=>'Failed', "message" => 'No Chats Found.']);
        endif;
    }
    
    public function getChatRecord(Request $request)
    {
        $chats = liveChat::select('id', 'name', 'email')->latest()->get();
        
        if($chats):
            return response()->json(['status'=>'Success', "message" => 'Chat Found.', 'Record' => $chats]);
        else:
            $chats = liveChatting::where('chat_id', $chat_id)->get();
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }
    
    public function deleteChat(Request $request)
    {
        $chat_id = $request->chat_id;
        if(!isset($chat_id)):
            return response()->json(['status'=>'Failed', "message" => 'Chat id is required']);
        endif;
        
        $chat = liveChat::find($chat_id);
        
        if($chat->delete()):
            $chats = liveChatting::where('chat_id', $chat_id)->get();
            foreach($chats as $chat1):
                $chat1->delete();
            endforeach;
            
            return response()->json(['status'=>'Success', "message" => 'Chat Deleted.']);
        else:
            $chats = liveChatting::where('chat_id', $chat_id)->get();
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }
    
    public function emailChats(Request $request)
    {
        $chat_id = $request->chat_id;
        if(!isset($chat_id)):
            return response()->json(['status'=>'Failed', "message" => 'Chat id is required']);
        endif;
        $chat = liveChat::find($chat_id);
        $chats = liveChatting::where('chat_id', $chat_id)->get();
        
        // send email and attach all chats
        $data = [
               'name'   => $chat->name,
               'chats'  => $chats,
            ];
            Mail::send('Email.sendChat', $data , function ($m) use ($chat){
                $m->to($chat->email, 'Chat')->from('recovery@umyocards.com')->subject('Chat Messages.');
            });
            return (['status'=>'Success', 'message'=>'Email Sent Successfully']);
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
