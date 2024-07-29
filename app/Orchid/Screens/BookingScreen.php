<?php

namespace App\Orchid\Screens;

use App\Http\Controllers\MessageController;
use App\Models\Booking;
use App\Models\TelegramUser;
use App\Orchid\Layouts\Booking\BooknigTable;
use Orchid\Screen\Screen;

class BookingScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'bookings' => Booking::orderBy('created_at')->paginate(10),
            'booking' => Booking::firstOrFail(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'BookingScreen';
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
            BooknigTable::class,
        ];
    }


    public function confirm(Booking $booking)
    {

        $text = [
            'bg' => 'Ваша заявка принята и подтверждена с вами свяжется наш специалист',
            'en' => 'We have booked your request our employee will contact you',
        ];
        $user = TelegramUser::where(['user_id' => $booking->user_id])->get()->first();

        $booking->active = 1;
        $booking->save();


        $data = [
            'user_id' => $booking->user_id,
            'text' => $text[$user->language],
        ];

        $messanger = new MessageController();
        $messanger->sendAdminText($data, $user);
    }
}
