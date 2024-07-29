<?php

namespace App\Orchid\Layouts\TelegramUsers;

use App\Models\TelegramUser;
use Orchid\Screen\Actions\Button;
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
            TD::make('user_id', 'TELEGRAM ID'),
            TD::make('username', 'UserName'),
//            TD::make('block', 'STATUS')
//                ->render(function (TelegramUser $user) {
//                    $active = $user->block ? 'Blocked' : 'Not Blocked';
//                    return $active;
//                }),
//            TD::make('language', 'Language')->render(function (TelegramUser $user) {
//                $active = $user->language=='bg' ? 'Bulgarian' : 'English';
//                return $active;
//            }),
            TD::make('Show information')->render(function (TelegramUser $user) {
                return ModalToggle::make('Open')
                    ->modal('taskModal')
                    ->modalTitle('Information about the user')
                    ->asyncParameters([
                        'user' => $user->id
                    ]);

            }),
            TD::make('Block')
                ->alignCenter()
                ->render(function (TelegramUser $user) {
                    $text = 'Block User';
                    if ($user->block){
                        $text = 'UnBlock User';
                    }
                    return Button::make($text)
                        ->confirm('After deleting, the task will be gone forever.')
                        ->method('block', ['user' => $user->id]);
                }),
        ];
    }
}
