<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use Telegram\Bot\Objects\Message;

class TelegramUserController extends Controller
{
    public string $user_id;
    public function __construct($user_id=null)
    {
        $this->user_id = $user_id;
    }

    public function createTelegramUser($request){
        $user = TelegramUser::where(['user_id' => $request->from?->id])->first();
        if (!$user){
            $user = TelegramUser::create([
                'user_id' => $request->from?->id,
                'first_name' => $request->from?->first_name,
                'username' => $request->from?->username,
            ]);
        }
        return $user;
    }

    public function setLang($lang){
        $user = TelegramUser::where(['user_id' => $this->user_id]);
        if ($user->exists()){
            $user->first()->update(['language' => $lang]);
        }
        return $user;
    }
}
