<?php

namespace App\Http\Controllers;

use App\Models\AdminConfigs;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Orchid\Attachment\Models\Attachment;
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
        return $this->telegramAPI::setWebhook(['url' => 'https://beach.learn-solve.com/api/webhook']);
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
                    $menu = new MenuController($update->callback_query->message, $this->telegramAPI);
                    $callback->closeCallback();
                    if (gettype(json_decode($update->callback_query->data,1))!='array'){
                        $menu->isMenuExists($update->callback_query->data);
                    }else{
                        $callback->store();
                    }
                    $menu = new MessageController();
                    $menu->store($update->callback_query->message,'callback');
                }
            }if (!isset($update->entities) and $update->isType('message')){
                $user = TelegramUser::where(['user_id' => $update->message->chat?->id])->get()->first();
                if (!$user->block){
                    $menu = new MenuController($update->message, $this->telegramAPI);
                    if (isset($update->message->contact)) {
                        $contact = $update->message->contact;
                        $phoneNumber = $contact['phone_number'];
                        if ($user) {
                            $user->phone = $phoneNumber;
                            $user->save();
                        }
                        $text = ['en' => 'Thank you! Your phone number has been saved', 'bg' => 'Благодарим! Вашият телефонен номер беше запазен'];
                        $this->telegramAPI::sendMessage([
                            'chat_id' => $update->message->chat?->id,
                            'text' => "✅ ".$text[$user->language].": $phoneNumber"
                        ]);

                        $menu->isMenuExists('menu');
                        return;
                    }
                    if ($user->on_chat){
                        $message = new MessageController();
                        $message->store($update);
                    }
                    $menu->isMenuExists($update->message->text);
                }
            }
        }catch (\Exception $e){
            $updates = $e->getMessage();
        }
    }
}







