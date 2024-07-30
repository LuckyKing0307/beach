<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;

class BotController extends Controller
{
    protected Api $telegram;
    protected Telegram $telegramAPI;
    public function __construct()
    {
        $this->telegramAPI = new Telegram;
    }
    public function setWebhook(){
        $this->telegramAPI::setWebhook(['url' => 'https://beach.learn-solve.com/webhook']);
    }

    /**
     * Update telegram message.
     */
    public function updates()
    {
        info('asd');
        try {
            $updates = $this->telegramAPI::commandsHandler(false);
            foreach ($updates as $update) {
                if ($update->isType('callback_query')){
                    $user = TelegramUser::where(['user_id' => $update->callback_query->message->chat?->id])->get()->first();
                    if (!$user->block){
                        $callback = new CallBackController($update->callback_query, $this->telegramAPI);
                        if ($update->callback_query->data==='menu'){
                            $menu = new MenuController($update->callback_query->message, $this->telegramAPI);
                            $menu->isMenuExists($update->callback_query->data);
                        }else{
                            $callback->store();
                        }
                        $callback->closeCallback();
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
            }
        }catch (\Exception $e){
            $updates = $e->getMessage();
        }
    }
}







