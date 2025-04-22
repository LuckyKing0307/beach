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
    public $itemMenu;
    public function __construct($request, TelegramBot $telegram)
    {
        $this->request = $request;
        $this->telegram = $telegram;
        $this->itemMenu = [
            'menu' => ['en' => 'Menu', 'bg' => 'Главно меню'],
            'back' => ['en' => 'Back', 'bg' => '↩️Назад'],
            'chat' => ['en' => "You've opened a chat with the admin", 'bg' => 'Отворихте чат с администратора'],
        ];
    }

    public function isMenuExists($text)
    {
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
        if ($text=='Help' || $text=='Задай въпрос'){
            $menuFields['id'] = $user->user_id;
            $menuFields['item_id'] = $config->id;
            $menuFields['language'] = $user->language;
            $this->help($menuFields);
        }
    }

    public function sendMenu($menuFields)
    {
        $user = TelegramUser::where(['user_id'=>$menuFields['id']])->get()->first();
        $user->on_chat = 0;
        $user->save();
        $keysBoards = [];
        $help = ['en'=>'Help','bg'=>'Задай въпрос'];
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
        $reply_markup = $this->addRows($reply_markup, $help[$user->language]);

        $this->telegram::sendMessage([
            'chat_id' => $menuFields['id'],
            'text' => $menuFields['text'],
            'reply_markup' => $reply_markup
        ]);
    }

    public function sendInlineMenu($menuFields)
    {
        $keysBoards = [];

        $itemMenu = [
            'menu' => ['en'=>'Menu','bg'=>'Меню'],
            'back' => ['en'=>'Back','bg'=>'Назад'],
        ];
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
        $backMenu = [];

        $backMenu[] = Keyboard::button(['text' => $itemMenu['back'][$menuFields['language']], 'callback_data' => $menuFields['trigger']]);
        $backMenu[] = Keyboard::button(['text' => $itemMenu['menu'][$menuFields['language']], 'callback_data' => $menuFields['trigger']]);

        $reply_markup = $this->addRows($reply_markup, $keysBoards);
        $reply_markup = $this->addRows($reply_markup, $backMenu);

        $this->telegram::sendMessage([
            'chat_id' => $menuFields['id'],
            'text' => $menuFields['text'],
            'reply_markup' => $reply_markup
        ]);
    }

    public function help($menuFields)
    {
        $user = TelegramUser::where(['user_id' => $menuFields['id']])->get()->first();
        $user->on_chat = 1;
        $user->save();
        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(false)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => $this->itemMenu['menu'][$user->language], 'callback_data' => 'menu']),
            ]);
        $messageData = [
            'chat_id' => $menuFields['id'],
            'reply_markup' => $reply_markup,
            'text' => $this->itemMenu['chat'][$user->language]
        ];
        $this->telegram::sendMessage(($messageData));
    }

    public function sendItemDetails($menuFields, $config){
        $itemMenu = [
            'photo' => ['en'=>'Photo','bg'=>'Cнимка'],
            'price' => ['en'=>'Price','bg'=>'Цена'],
            'booking' => ['en'=>'Booking','bg'=>'Резерв'],
            'menu' => ['en'=>'Menu','bg'=>'Главно меню'],
            'help' => ['en'=>'Help','bg'=>'Задай въпрос'],
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
