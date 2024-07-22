<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram as TelegramBot;
use Telegram\Bot\Api as TelegramBotApi;
use App\Models\AdminConfigs;

class MenuController extends Controller
{
    protected $request;
    protected TelegramBot $telegram;
    public function __construct($request, TelegramBot $telegram)
    {
        $this->request = $request;
        $this->telegram = $telegram;
    }

    public function isMenuExists()
    {
        $user = TelegramUser::where(['user_id' => $this->request->from?->id])->first();
        $menuFields = [];
        $config = AdminConfigs::where('trigger_'.$user->language, $this->request->text)->first();
        if ($user and $config->type!='section_item'){
            $menuData = json_decode($config->data);
            $menuFields['id'] = $user->user_id;
            $menuFields['text'] = $menuData->text->{$user->language};
            $fields[] = [];
            foreach ($menuData->fields as $menuItem){
                $fields[] = $menuItem->{$user->language};
            }
            $menuFields['fields'] = $fields;

            $this->sendMenu($menuFields);
        }
        if ($config->type==='section_item'){
            var_dump($config->type);
            $menuData = json_decode($config->data);
            $menuFields['id'] = $user->user_id;
            $menuFields['text'] = $menuData->text->{$user->language};
            $menuFields['item_id'] = $config->id;
            $menuFields['language'] = $user->language;
            $this->sendItemDetails($menuFields, $config);
        }
    }

    public function sendMenu($menuFields)
    {
        $keysBoards = [];
        $reply_markup = Keyboard::make()
            ->setResizeKeyboard(false)
            ->setOneTimeKeyboard(true);
        foreach ($menuFields['fields'] as $field){
            if (count($keysBoards)==3){
                $reply_markup = $this->addRows($reply_markup, $keysBoards);
                $keysBoards = [];
            }
            if (gettype($field)!='array'){
                $keysBoards[] = Keyboard::button($field);
            }
        }

        $reply_markup = $this->addRows($reply_markup, $keysBoards);

        $message = $this->telegram::sendMessage([
            'chat_id' => $menuFields['id'],
            'text' => $menuFields['text'],
            'reply_markup' => $reply_markup
        ]);
    }

    public function sendItemDetails($menuFields, $config){
        $itemMenu = [
            'photo' => ['en'=>'Photo','bg'=>'Cнимка'],
            'price' => ['en'=>'Price','bg'=>'Цена'],
            'booking' => ['en'=>'Booking','bg'=>'Резервация'],
            'menu' => ['en'=>'Menu','bg'=>'Меню'],
            'help' => ['en'=>'Help','bg'=>'Помогне'],
        ];
        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(false)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => $itemMenu['photo'][$menuFields['language']], 'callback_data' => json_encode($menuFields)]),
                Keyboard::button(['text' => $itemMenu['price'][$menuFields['language']], 'callback_data' => json_encode($menuFields)]),
                Keyboard::button(['text' => $itemMenu['booking'][$menuFields['language']], 'callback_data' => json_encode($menuFields)]),
            ])->row([
                Keyboard::button(['text' => $itemMenu['menu'][$menuFields['language']], 'callback_data' => json_encode($menuFields)]),
                Keyboard::button(['text' => $itemMenu['help'][$menuFields['language']], 'callback_data' => json_encode($menuFields)]),
            ]);
        $reply_markup2 = Keyboard::make()
            ->setResizeKeyboard(false)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => 'help']),
                Keyboard::button(['text' => 'menu']),
            ]);
        $messageData = [
            'chat_id' => $menuFields['id'],
            'caption' => $menuFields['text'],
            'reply_markup' => $reply_markup,
        ];
        if (strlen($config->photo)<5){
            $messageData['photo'] = new InputFile('http://127.0.0.1:8000'.$config->attachment()->first()?->getRelativeUrlAttribute());
        }
        $message = $this->telegram::sendPhoto($messageData);
        var_dump($message->getMessageId());
    }

    protected function addRows($rows,$keysBoards)
    {
        $reply_markup = $rows->row($keysBoards);
        return $reply_markup;
    }
}
