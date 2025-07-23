<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class MessageController extends Controller
{
    public function store($request,$type='message'){
        $data = [
            'user_id' => $request->message->chat?->id,
            'message_id' => $request->message->message_id,
            'data' => $request->message,
            'type' => $type,
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
            'reply_markup' => $data['reply_markup'] ?? null, // безопаснее через null
        ];
        Telegram::sendMessage($messageData);
//        $user->on_chat = 0;
//        $user->save();
    }
}
