<?php

namespace App\Orchid\Screens\TelegramUser;

use App\Orchid\Layouts\TelegramUsers\TelegramUsersTable;
use Orchid\Screen\Screen;
use Orchid\Screen\Sight;
use Orchid\Support\Facades\Layout;
use App\Models\TelegramUser as TelegramUserModel;

class TelegramUser extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
//        return [
//            'user' => User::firstOrFail(),
//        ];

        return [
            'telegram_users' => TelegramUserModel::paginate(10),
            'user' => TelegramUserModel::firstOrFail(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'TelegramUser';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
//            ModalToggle::make('Open User')
//                ->modal('user')
//                ->method('create')
//                ->icon('plus'),
            ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            TelegramUsersTable::class,
            Layout::modal('taskModal',
                Layout::legend('user', [
                    Sight::make('id'),
                    Sight::make('username'),
                    Sight::make('first_name'),
                    Sight::make('language'),
                    Sight::make('block')
                        ->render(function (TelegramUserModel $user) {
                            $active = $user->block ? 'Blocked' : 'Not Blocked';
                            return $active;
                        }),
                ]))->async('asyncGetUser'),
//            Layout::view('realtimechat'),
        ];
    }

    public function asyncGetUser(TelegramUserModel $user): array
    {
        return [
            'user' => $user
        ];
    }
}
