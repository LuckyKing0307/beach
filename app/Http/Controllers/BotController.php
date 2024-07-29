<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\AdminConfigs as Configs;

class BotController extends Controller
{
    protected Api $telegram;
    protected Telegram $telegramAPI;

    /**
     * Create a new controller instance.
     *
     * @param  Api  $telegram
     */
    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
        $this->telegramAPI = new Telegram;
    }

    /**
     * Show the bot information.
     */
    public function show()
    {
        $response = $this->telegram->getMe();

        return $response;
    }

    /**
     * Update telegram message.
     */
    public function updates()
    {
        $updates = '';
        while (true){
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
}







