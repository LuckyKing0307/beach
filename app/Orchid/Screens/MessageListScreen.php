<?php

namespace App\Orchid\Screens;

use App\Models\Message;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class MessageListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    /**
     * @var Message
     */
    public $message;
    public function query(Message $message): iterable
    {
        return [
            'messages' => Message::where(['user_id'=>$message->user_id])->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'MessageListScreen';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::view('realtimechat'),
        ];
    }
}
