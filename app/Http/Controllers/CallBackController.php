<?php

namespace App\Http\Controllers;

use App\Models\AdminConfigs;
use App\Models\Booking;
use App\Models\Message;
use App\Models\TelegramUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;
use Orchid\Attachment\Models\Attachment;
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
            'menu' => ['en' => 'Menu', 'bg' => 'Главно меню'],
            'back' => ['en' => 'Back', 'bg' => '↩️ Обратно'],
            'chat' => ['en' => "You've opened a chat with the admin", 'bg' => 'Отворихте чат с администратора'],
        ];
        $this->telegramUser = new TelegramUserController($request->message->chat->id);
    }

    public function store()
    {
        $data = json_decode($this->request->data, 1);

        if (isset($data['function'])) {
            $function = $data['function'];
            self::$function($this->request);
            return $function;
        }
        if (isset($data["item_id"])) {
            $data['user_id'] = $this->request->message->chat->id;
            $function = $data['type'] . 'Send';

            self::$function($data);
            return $data;
        }
        if (isset($data["type"]) and $data["type"] == 'booking_day') {
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
        $data = json_decode($request->data, 1);
        $this->telegramUser->setLang($data['value']);
        $menu = new MenuController($request->message, $this->telegram);
        $menu->isMenuExists('menu');
        return $this->request;
    }

    protected function photoSend($request)
    {

        $config = AdminConfigs::find($request['item_id']);
        $trigger = 'trigger_' . $request['language'];
        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => $this->itemMenu['back'][$request['language']], 'callback_data' => $config->$trigger]),
                Keyboard::button(['text' => $this->itemMenu['menu'][$request['language']], 'callback_data' => 'menu']),
            ]);
        $user = TelegramUser::where(['user_id' => $request['user_id']])->get()->first();
        if ($config->exists()) {
            $medias = [];
            $attachments = json_decode($config->function);
            if (gettype($attachments) == 'array' and count($attachments) > 1) {
                foreach ($attachments as $attachment) {
                    $medias[] = [
                        'type' => 'photo',
                        'media' => 'https://beach.learn-solve.com'.str_replace('//', '/', Attachment::find($attachment)?->getRelativeUrlAttribute())
                    ];
                }
                $messageData = [
                    'chat_id' => $request['user_id'],
                    'media' => json_encode($medias),
                ];
                $text = ['en' => 'List of photos', 'bg' => 'Cнимки'];
                $text = $text[$user->language];
                $this->telegram::sendMediaGroup($messageData);
            }
            if (count($attachments) < 2 or gettype($attachments) != 'array') {
                $text = ['en' => 'There is no photo', 'bg' => 'Няма снимка'];
                $text = $text[$user->language];
            }
            $messageData = [
                'chat_id' => $request['user_id'],
                'reply_markup' => $reply_markup,
                'text' => $text
            ];
            info(json_encode($messageData));
            $this->telegram::sendMessage($messageData);
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
        $trigger = 'trigger_' . $request['language'];
        $config = AdminConfigs::find($request['item_id']);

        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => $this->itemMenu['back'][$request['language']], 'callback_data' => $config->$trigger]),
                Keyboard::button(['text' => $this->itemMenu['menu'][$request['language']], 'callback_data' => 'menu']),
            ]);
        if ($config->exists()) {
            $data = json_decode($config->data, 1);
            $price_text = "Price\n";
            foreach ($data['price'] as $price) {
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
        $text = ['en' => "Thank you! you booking is received. Wait please for confirmation during 4 hours", 'bg' => 'Благодаря! Вашето заявление е прието. Изчакайте потвърждение в рамките на 4 часа'];

        $booking = Booking::find($request['id']);
        $user = TelegramUser::where(['user_id' => $booking->user_id])->get()->first();
        if ($booking->exists()) {
            $booking->day = $request['day'];
            $data = [
                'user_id' => $booking->user_id,
                'message_id' => $booking->id,
                'type' => 'message',
                'data' => ['text' => 'Забранировал на '.$request['day']],
            ];
            Message::create($data);
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
        $trigger = 'trigger_' . $request['language'];
        if ($booking->exists()) {
            $booking = $booking->get()->first();
        } else {
            $booking = Booking::create([
                'user_id' => $this->request->message->chat?->id,
                'item_id' => $request['item_id'],
            ]);
        }
        $config = AdminConfigs::find($request['item_id']);
        $currentDate = Carbon::now();
        $endOfMonth = $currentDate->copy()->addDays(7);
        $daysLeft = $endOfMonth->diffInDays($currentDate->toDate());
        $remainingDays = [];
        for ($i = 0; $i <= intval($daysLeft) * (-1); $i++) {
            if ($i != 0) {
                $days = [];
                $days['id'] = $booking->id;
                $days['type'] = 'booking_day';
                $days['day'] = $currentDate->copy()->addDays($i)->toDate()->format('d M');
                $remainingDays['fields'][] = $days;
            }
        }
        $remainingDays['id'] = $this->request->message->chat?->id;
        $remainingDays['text'] = 'Booking';
        $remainingDays['trigger'] = $config->$trigger;
        $remainingDays['language'] = $request['language'];
        $menu = new MenuController($this->request->message, $this->telegram);
        $menu->sendInlineMenu($remainingDays);
    }

    public function help($request)
    {
        $user = TelegramUser::where(['user_id' => $request->message->chat?->id])->get()->first();
        $user->on_chat = 1;
        $user->save();
        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => $this->itemMenu['menu'][$user->language], 'callback_data' => 'menu']),
            ]);
        $messageData = [
            'chat_id' => $request->message->chat?->id,
            'reply_markup' => $reply_markup,
            'text' => $this->itemMenu['chat'][$user->language]
        ];
        $this->telegram::sendMessage(($messageData));
    }
}
