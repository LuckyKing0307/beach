<?php

namespace App\Http\Controllers;

use App\Models\AdminConfigs;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
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
        $this->telegramUser = new TelegramUserController($request->message->chat->id);
    }

    public function store(){
        var_dump($this->request->data);
            $data = json_decode($this->request->data,1);

        if (isset($data['function'])){
            $function = $data['function'];
            $send = self::$function($this->request);
            return $function;
        }
        if (isset($data["item_id"])){
            $data['user_id'] = $this->request->message->chat->id;
            $function = $data['type'].'Send';

            $send = self::$function($data);
            return $data;
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function language($request)
    {
        $data = json_decode($request->data,1);
        $this->telegramUser->setLang($data['value']);
        return $this->request;
    }

    protected function photoSend($request)
    {
        $itemMenu = [
            'menu' => ['en'=>'Menu','bg'=>'Меню'],
        ];
        $config = AdminConfigs::find($request['item_id']);

        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(false)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => $itemMenu['menu'][$request['language']], 'callback_data' => 'menu']),
            ]);
        if ($config->exists()){
            var_dump($request);
            if (strlen($config->function)<5){
                $messageData = [
                    'chat_id' => $request['user_id'],
                    'caption' => 'Photo',
                    'reply_markup' => $reply_markup,
                    'photo' => new InputFile('http://127.0.0.1:8000'.$config->attachment()->first()?->getRelativeUrlAttribute())
                ];
                $this->telegram::sendPhoto($messageData);
            }
            if (strlen($config->function)>=5){
                $messageData = [
                    'chat_id' => $config->user_id,
                    'reply_markup' => $reply_markup,
                    'text' => 'There is no photo'
                ];
                $this->telegram::sendMessage(($messageData));
            }
        }
    }

    public function closeCallback()
    {
            $params = [
                'callback_query_id' => $this->request->id,
                'text' => 'success',
            ];
            $this->telegram::answerCallbackQuery($params);
    }

    protected function priceSend($request)
    {
        $itemMenu = [
            'menu' => ['en'=>'Menu','bg'=>'Меню'],
        ];
        $config = AdminConfigs::find($request['item_id']);

        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(false)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => $itemMenu['menu'][$request['language']], 'callback_data' => 'menu']),
            ]);
        if ($config->exists()){
            $data = json_decode($config->data,1);
            $price_text = "Price\n";
            foreach ($data['price'] as $price){
                $price_text .= "{$price['time']}h - {$price['price']}\n";
            }

            var_dump($price_text);
            $messageData = [
                'chat_id' => $request['user_id'],
                'reply_markup' => $reply_markup,
                'text' => $price_text
            ];
            $this->telegram::sendMessage(($messageData));
        }

    }


    protected function bookingSend()
    {

    }
    public function help()
    {
        return 'asd';
    }
}
