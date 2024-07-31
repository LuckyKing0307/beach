<?php

namespace App\Http\Controllers;

use App\Models\AdminConfigs;
use App\Models\Booking;
use App\Models\TelegramUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Laravel\Facades\Telegram as TelegramBot;

class CallBackController extends Controller
{
    protected $request;
    protected TelegramBot $telegram;
    protected TelegramUserController $telegramUser;

    public $itemMenu;
    public function __construct($request, TelegramBot $telegram)
    {
        $this->request = $request;
        $this->telegram = $telegram;
        $this->itemMenu = [
            'menu' => ['en'=>'Menu','bg'=>'Меню'],
            'back' => ['en'=>'Back','bg'=>'Назад'],
        ];
        $this->telegramUser = new TelegramUserController($request->message->chat->id);
    }

    public function store(){
            $data = json_decode($this->request->data,1);

        if (isset($data['function'])){
            $function = $data['function'];
            self::$function($this->request);
            return $function;
        }
        if (isset($data["item_id"])){
            $data['user_id'] = $this->request->message->chat->id;
            $function = $data['type'].'Send';

            self::$function($data);
            return $data;
        }
        if (isset($data["type"]) and $data["type"]=='booking_day'){
            $data['user_id'] = $this->request->message->chat->id;
            $function = $data['type'];
            self::$function($data);
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
        $menu = new MenuController($request->message, $this->telegram);
        $menu->isMenuExists('menu');
        return $this->request;
    }

    protected function photoSend($request)
    {
        $config = AdminConfigs::find($request['item_id']);
        $trigger = 'trigger_'.$request['language'];
        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(false)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => $this->itemMenu['back'][$request['language']], 'callback_data' => $config->$trigger]),
                Keyboard::button(['text' => $this->itemMenu['menu'][$request['language']], 'callback_data' => 'menu']),
            ]);
        if ($config->exists()){
            $photoLink = str_replace('//','/',$config->attachment()->first()?->getRelativeUrlAttribute());
            $remoteImage = 'https://beach.learn-solve.com'.$photoLink;
            if (strlen($config->function)<5){
                $file = InputFile::create($remoteImage,'uploaded.jpg');
                $messageData = [
                    'chat_id' => $request['user_id'],
                    'caption' => 'Photo',
                    'reply_markup' => $reply_markup,
                    'photo' => $file,
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
        $trigger = 'trigger_'.$request['language'];
        $config = AdminConfigs::find($request['item_id']);

        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(false)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => $this->itemMenu['back'][$request['language']], 'callback_data' => $config->$trigger]),
                Keyboard::button(['text' => $this->itemMenu['menu'][$request['language']], 'callback_data' => 'menu']),
            ]);
        if ($config->exists()){
            $data = json_decode($config->data,1);
            $price_text = "Price\n";
            foreach ($data['price'] as $price){
                $price_text .= "{$price['time']}h - {$price['price']}\n";
            }
            $messageData = [
                'chat_id' => $request['user_id'],
                'reply_markup' => $reply_markup,
                'text' => $price_text
            ];
            $this->telegram::sendMessage(($messageData));
        }

    }


    protected function booking_day($request)
    {
        $text = [
            'bg' => 'Ваша заявка принята',
            'en' => 'We have booked your request',
        ];
        $booking = Booking::find($request['id']);
        $user = TelegramUser::where(['user_id' => $booking->user_id])->get()->first();
        if ($booking->exists()){
            $booking->day = $request['day'];
        }
        $booking->save();

        $messageData = [
            'chat_id' => $booking->user_id,
            'text' => $text[$user->language],
        ];
        Telegram::sendMessage($messageData);
    }


    protected function bookingSend($request)
    {
        $booking = Booking::where([
            'user_id' => $this->request->message->chat?->id,
            'item_id' => $request['item_id'],
            'day' => null,
        ]);
        if ($booking->exists()){
            $booking = $booking->get()->first();
        }else{
            $booking = Booking::create([
                'user_id' => $this->request->message->chat?->id,
                'item_id' => $request['item_id'],
            ]);
        }
        $currentDate = Carbon::now();
        $endOfMonth = $currentDate->addDays(8);
        $daysLeft = $endOfMonth->diffInDays($currentDate->toDate());
        $remainingDays = [];
        for ($i = 0; $i <= intval($daysLeft)*(-1); $i++) {
            if ($i!=0){
                $days = [];
                $days['id'] = $booking->id;
                $days['type'] = 'booking_day';
                $days['day'] = $currentDate->copy()->addDays($i)->toDate()->format('d');
                $remainingDays['fields'][] = $days;
            }
        }
        $remainingDays['id'] = $this->request->message->chat?->id;
        $remainingDays['text'] = 'Booking';
        $menu = new MenuController($this->request->message, $this->telegram);
        $menu->sendInlineMenu($remainingDays);
    }
    public function help($request)
    {
        $user = TelegramUser::where(['user_id' => $request->message->chat?->id])->get()->first();
        $user->on_chat = 1;
        $user->save();
        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(false)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => $this->itemMenu['menu'][$user->language], 'callback_data' => 'menu']),
            ]);
        $messageData = [
            'chat_id' => $request->message->chat?->id,
            'reply_markup' => $reply_markup,
            'text' => 'Вы открыли чат с администратором'
        ];
        $this->telegram::sendMessage(($messageData));
    }
}
