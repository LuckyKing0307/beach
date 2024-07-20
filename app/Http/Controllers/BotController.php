<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Telegram\Bot\Api;

class BotController extends Controller
{
    protected $telegram;

    /**
     * Create a new controller instance.
     *
     * @param  Api  $telegram
     */
    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
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
     * Show the bot information.
     */
    public function updates()
    {
        $updates = collect($this->telegram->getUpdates())->last();
        $userMessage = $updates->getMessage()->getText();
        $fromUser = $updates->getChat()->getId();

        $response = $this->telegram->sendMessage([
            'chat_id' => $fromUser,
            'text' => $userMessage
        ]);

        return $response;
    }
}
