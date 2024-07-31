<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class BotController extends Controller
{
    protected Api $telegram;
    protected Telegram $telegramAPI;
    public function __construct()
    {
        $this->telegramAPI = new Telegram;
    }
    public function setWebhook(){
//        return $this->telegramAPI::removeWebhook();
//        return $this->telegramAPI::setWebhook(['url' => 'https://beach.learn-solve.com/api/webhook']);

        $file = new InputFile('https://beach.learn-solve.com/storage/2024/07/30/1f34e34445cbb5a75c441533a7a2f0b7617ecb63.jpg');
        $messageData = [
            'chat_id' => 151617513,
            'caption' => 'Photo',
            'photo' => $file,
        ];
        Telegram::sendPhoto($messageData);
    }

    /**
     * Update telegram message.
     */
    public function updates(Request $request)
    {
        Telegram::commandsHandler(true);
        $update = Update::make($request);

        try {
            if ($update->isType('callback_query')){
                $user = TelegramUser::where(['user_id' => $update->callback_query->message->chat?->id])->get()->first();
                if (!$user->block){
                    $callback = new CallBackController($update->callback_query, $this->telegramAPI);
                    $callback->closeCallback();
                    if (gettype(json_decode($update->callback_query->data,1))!='array'){
                        $menu = new MenuController($update->callback_query->message, $this->telegramAPI);
                        $menu->isMenuExists($update->callback_query->data);
                    }else{
                        $callback->store();
                    }
                }
            }if (!isset($update->entities) and $update->isType('message')){
                $user = TelegramUser::where(['user_id' => $update->message->chat?->id])->get()->first();
                if (!$user->block){
                    if ($user->on_chat){
                        $menu = new MessageController();
                        $menu->store($update);
                    }else{
                        $menu = new MenuController($update->message, $this->telegramAPI);
                        $menu->isMenuExists($update->message->text);
                    }
                }
            }
        }catch (\Exception $e){
            $updates = $e->getMessage();
        }
    }
}







