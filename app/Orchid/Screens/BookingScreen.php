<?php

namespace App\Orchid\Screens;

use App\Http\Controllers\MessageController;
use App\Models\Booking;
use App\Models\TelegramUser;
use App\Orchid\Layouts\Booking\BooknigTable;
use Orchid\Screen\Screen;
use Telegram\Bot\Keyboard\Keyboard;

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
            'bg' => 'Благодарим! Получихме вашето запитване за избраната услуга на посочената дата.
Започваме обработката му веднага.
Наш оператор ще се свърже с вас скоро, за да уточни детайлите — час, брой хора и специални изисквания.
Ще направим всичко възможно, за да организираме услугата удобно и безпроблемно за вас.',
            'en' => "Thank you! We've received your request for the selected activity on your chosen date.
    We’re starting to process it right away.
    Our operator will contact you shortly to clarify the details - time, number of people, and any special requests.
    We’ll make sure everything is smoothly arranged with the service provider for your comfort and convenience.",
            'tel-bg' => 'Изпрати телефонния номер',
            'tel-en' => 'Send phone number',
        ];
        $user = TelegramUser::where(['user_id' => $booking->user_id])->get()->first();

        $booking->active = 1;
        $booking->save();
        $keyboard = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button([
                    'text' => $text['tel-'.$user->language],
                    'request_contact' => true
                ])
            ]);
        $data = [
            'user_id' => $booking->user_id,
            'text' => $text[$user->language],
//            'reply_markup' => $keyboard
        ];

        $messanger = new MessageController();
        $messanger->sendAdminText($data, $user);
    }
}
