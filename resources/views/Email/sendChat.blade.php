<h3>Dear {{ $name }}, </h3>

@if($chats)
<h3>Below is your chat.</h3>

@foreach($chats as $chat)
<p>{{$chat->user_message}}</p>
@endforeach

@endif