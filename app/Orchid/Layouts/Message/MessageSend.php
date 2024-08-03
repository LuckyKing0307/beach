<?php

namespace App\Orchid\Layouts\Message;

use App\Models\Message;
use App\Models\TelegramUser;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class MessageSend extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        $user = TelegramUser::where(['user_id' => $this->query->get('message.user_id')])->get()->first();
        $text = $this->query->get('message.data')['text'];
        return [
            Input::make('message.id')->hidden(),
            Input::make('message.name')->title('User Name')->disabled()->value($user->username),
            Input::make("message.text")->title('User Text')->disabled()->value($text),
            TextArea::make('text')->required(1)->rows(10)->title('Answer'),
        ];
    }
}
