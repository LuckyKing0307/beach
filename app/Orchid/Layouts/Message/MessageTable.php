<?php

namespace App\Orchid\Layouts\Message;

use App\Models\AdminConfigs;
use App\Models\Booking;
use App\Models\Message;
use App\Models\TelegramUser;
use Carbon\Carbon;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class MessageTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'messages';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('User', 'USERNAME')
                ->render(function (Message $message) {
                    $active = TelegramUser::where(['user_id'=>$message->user_id])->get()->first();
                    return $active->username;
                }),
            TD::make('message', 'MESSAGE')->render(function (Message $message){
                $text = $message->data['text'];
                return $text;
            }),

            TD::make('message', 'MESSAGES TREE')->render(function (Message $message){
                $link = route('platform.message.list', ['message'=>$message->id]);
                return "<a href='{$link}' target='_blank'>Open Messages List</a>";
            }),
            TD::make('day', 'Time')->render(function (Message $booking) {
                $day = Carbon::parse($booking->created_at)->format('d M H:i:s');
                return $day;
            }),
            TD::make('BLOCK')
                ->alignCenter()
                ->render(function (Message $message) {
                    return ModalToggle::make('Answer')
                        ->modal('editConfig')
                        ->modalTitle('Send Answer')
                        ->method('sendText')
                        ->asyncParameters([
                            'message' => $message->id
                        ]);
                }),
        ];
    }
}
