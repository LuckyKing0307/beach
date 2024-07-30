<?php

namespace App\Http\Controllers;

use App\Models\AdminConfigs;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
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

        $itemMenu = [
            'menu' => ['en'=>'Menu','bg'=>'Меню'],
        ];
        $config = AdminConfigs::find(6);

        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(false)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => $itemMenu['menu']['en'], 'callback_data' => 'menu']),
            ]);
        $photoLink = str_replace('//','/',$config->attachment()->first()?->getRelativeUrlAttribute());
        $remoteImage = 'https://beach.learn-solve.com'.$photoLink;
        var_dump($config->attachment()->first()?->getRelativeUrlAttribute());
        if (strlen($config->function)<5) {
            $file = InputFile::create($remoteImage, 'uploaded.jpg');
            var_dump($file);
            $messageData = [
                'chat_id' => 151617513,
                'caption' => 'Photo',
                'reply_markup' => $reply_markup,
                'photo' => $file,
            ];
            Telegram::sendPhoto($messageData);
        }
        return true;
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
                    if ($update->callback_query->data==='menu'){
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







