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

    public function isMenuExists($text)
    {
        var_dump('asdasd');
        $user = TelegramUser::where(['user_id' => $this->request->chat?->id])->first();
        $menuFields = [];
        $config = AdminConfigs::where('trigger_'.$user->language, $text)->first();
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

    public function sendInlineMenu($menuFields)
    {
        $keysBoards = [];
        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);
        foreach ($menuFields['fields'] as $field){
            if (count($keysBoards)==3){
                $reply_markup = $this->addRows($reply_markup, $keysBoards);
                $keysBoards = [];
            }
            if (gettype($field['day'])!='array'){
                $keysBoards[] = Keyboard::button(['text' => $field['day'], 'callback_data' => json_encode($field)]);
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
        $menuItemFields = [
            'item_id' => $menuFields['item_id'],
            'language' => $menuFields['language'],
        ];
        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(false)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => $itemMenu['photo'][$menuFields['language']], 'callback_data' => json_encode(array_merge($menuItemFields, ['type'=>'photo']))]),
                Keyboard::button(['text' => $itemMenu['price'][$menuFields['language']], 'callback_data' => json_encode(array_merge($menuItemFields, ['type'=>'price']))]),
                Keyboard::button(['text' => $itemMenu['booking'][$menuFields['language']], 'callback_data' => json_encode(array_merge($menuItemFields, ['type'=>'booking']))]),
            ])->row([
                Keyboard::button(['text' => $itemMenu['menu'][$menuFields['language']], 'callback_data' => 'menu']),
                Keyboard::button(['text' => $itemMenu['help'][$menuFields['language']], 'callback_data' => json_encode(['function'=>'help'])]),
            ]);
        $messageData = [
            'chat_id' => $menuFields['id'],
            'text' => $menuFields['text'],
            'reply_markup' => $reply_markup,
        ];
        $message = $this->telegram::sendMessage($messageData);
    }

    protected function addRows($rows,$keysBoards)
    {
        $reply_markup = $rows->row($keysBoards);
        return $reply_markup;
    }
}
