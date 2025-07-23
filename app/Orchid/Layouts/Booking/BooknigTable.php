<?php

namespace App\Orchid\Layouts\Booking;

use App\Models\AdminConfigs;
use App\Models\Booking;
use App\Models\TelegramUser;
use Carbon\Carbon;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class BooknigTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'bookings';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('active', 'STATUS')
                ->render(function (Booking $booking) {
                    $active = $booking->active ? 'Active Order' : 'Not Active Order';
                    return $active;
                }),
            TD::make('item', 'ORDER')->render(function (Booking $booking){
                $item = AdminConfigs::find($booking->item_id);
                return $item?->name;
            }),
            TD::make('day', 'DAY')->render(function (Booking $booking) {
                return $booking->day;
            }),
            TD::make('username', 'USERNAME')->render(function (Booking $booking) {
                $user = TelegramUser::where(['user_id' => $booking->user_id])->get()->first();
                return $user->username;
            }),
            TD::make('phone', 'PHONE')->render(function (Booking $booking) {
                $user = TelegramUser::where(['user_id' => $booking->user_id])->get()->first();
                return $user->phone;
            }),
            TD::make('confirmation','CONFIRMATION')
                ->alignCenter()
                ->render(function (Booking $booking) {
                    if (!$booking->active){
                        return Button::make('Confirm booking')
                            ->confirm('Do you confirm booking?')
                            ->method('confirm', ['booking' => $booking->id]);
                    }else{
                        return 'Confirmed';
                    }
                }),
            ];
    }
}
