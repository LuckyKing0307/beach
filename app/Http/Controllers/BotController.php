<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
                        $callback = new CallBackController($update->callback_query, $this->telegramAPI);
                        $callback->store();
                    }if (!isset($update->entities) and $update->isType('message')){
                        $menu = new MenuController($update->message, $this->telegramAPI);
                        $menu->isMenuExists();
                    }
                }
            }catch (\Exception $e){
                $updates = $e->getMessage();
            }
       }
    }
}







