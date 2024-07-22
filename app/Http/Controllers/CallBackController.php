<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram as TelegramBot;

class CallBackController extends Controller
{
    protected $request;
    protected TelegramBot $telegram;
    protected TelegramUserController $telegramUser;
    public function __construct($request, TelegramBot $telegram)
    {
        $this->request = $request;
        $this->telegram = $telegram;
        info($request->message->from);
        $this->telegramUser = new TelegramUserController($request->message->chat->id);
    }

    public function store(){
        $function = json_decode($this->request->data,1)['function'];
        $send = self::$function($this->request);
        return $function;
    }

    /**
     * @return mixed
     */
    public function language($request)
    {
        $data = json_decode($request->data,1);
        $this->telegramUser->setLang($data['value']);
        $params = [
            'callback_query_id' => $request->id,
            'text' => 'success',
        ];
        $this->telegram::answerCallbackQuery($params);
        return $this->request;
    }
}
