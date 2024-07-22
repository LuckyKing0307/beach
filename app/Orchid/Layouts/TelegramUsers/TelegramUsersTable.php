<?php

namespace App\Orchid\Layouts\TelegramUsers;

use App\Models\TelegramUser;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class TelegramUsersTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'telegram_users';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('user_id', 'ID'),
            TD::make('username', 'UserName'),
            TD::make('created_at', 'Registration on bot date'),
            TD::make('Show information')->render(function (TelegramUser $user) {
                return ModalToggle::make('Open')
                    ->modal('taskModal')
                    ->method('update')
                    ->modalTitle('Information about the user')
                    ->asyncParameters([
                        'user' => $user->id
                    ]);

            }),
        ];
    }
}
