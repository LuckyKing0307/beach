<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class MessageController extends Controller
{
    public function store($request){
        $data = [
            'user_id' => $request->message->chat?->id,
            'message_id' => $request->message->message_id,
            'data' => $request->message,
        ];
        Message::create($data);
        return response()->json([
            'status' => true,
        ]);
    }

    public function sendAdminText($data, TelegramUser $user){
        $messageData = [
            'chat_id' => $data['user_id'],
            'text' => $data['text'],
        ];
        Telegram::sendMessage($messageData);
//        $user->on_chat = 0;
//        $user->save();
    }
}
