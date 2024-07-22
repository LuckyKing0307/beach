<?php

namespace App\Telegram\Commands;


use App\Http\Controllers\TelegramUserController;
use Telegram\Bot\Commands\Command;

use Telegram\Bot\Keyboard\Keyboard;


class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Start Command to get you started';
    protected TelegramUserController $tg_user;


    public function __construct()
    {
    }

    public function handle()
    {
        $reply_markup = Keyboard::make()->inline()
            ->setResizeKeyboard(false)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text' => '🇧🇬', 'callback_data' => json_encode(['function' => 'language', 'value' => 'bg'])]),
                Keyboard::button(['text' => '🇬🇧', 'callback_data' => json_encode(['function' => 'language', 'value' => 'en'])]),
            ]);
        $message = $this->getUpdate()->getMessage();
        $this->tg_user = new TelegramUserController($message->from?->id);
        $userName = $message->from->first_name;

        $createdUser = $this->tg_user->createTelegramUser($message);
        $response = $this->replyWithMessage([
            'text' => "Hey, {$userName} Welcome to our bot!",
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
    }
}
