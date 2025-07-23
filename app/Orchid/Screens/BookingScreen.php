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
            'bookings' => Booking::whereNotNull('day')->orderBy('created_at','desc')->paginate(10),
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
            'bg' => 'Честито! Вашата резервация е потвърдена! Тук скоро ще се появят параметри на услугата – местоположение и телефон за връзка.
Моля, изпратете Вашия телефонен номер чрез бутона по-долу.',
            'en' => 'Congratulations! Your reservation is confirmed! The service details — location and contact phone — will appear here soon.
Please send your phone number using the button below.',
            'tel.bg' => 'Изпрати телефонния номер',
            'tel.en' => 'Send phone number',
        ];
        $user = TelegramUser::where(['user_id' => $booking->user_id])->get()->first();

        $booking->active = 1;
        $booking->save();


        $data = [
            'user_id' => $booking->user_id,
            'text' => $text[$user->language],
            'reply_markup' => [
                'keyboard' => [
                    [
                        [
                            'text' => $text['tel'.$user->language],
                            'request_contact' => true,
                        ]
                    ]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]
        ];

        $messanger = new MessageController();
        $messanger->sendAdminText($data, $user);
    }
}
