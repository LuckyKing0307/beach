<?php

namespace App\Orchid\Screens;

use App\Http\Controllers\MessageController;
use App\Models\AdminConfigs as AdConfigs;
use App\Models\Message;
use App\Models\TelegramUser;
use App\Orchid\Layouts\Admin\EditConfigRows;
use App\Orchid\Layouts\Message\MessageSend;
use App\Orchid\Layouts\Message\MessageTable;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class MessageScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'messages' => Message::where([
                ['type','!=','admin']
            ])->paginate(10),
            'message' => Message::firstOrFail(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Message Screen';
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
            MessageTable::class,
            Layout::modal('editConfig', MessageSend::class)->async('asyncGetMessage'),
        ];
    }

    public function asyncGetMessage(Message $message): array
    {
        return [
            'message' => $message
        ];
    }

    public function sendText(Request $request){

        $message = Message::find($request->input('message.id'));
        $user = TelegramUser::where(['user_id'=>$message->user_id])->get()->first();

        $data = [
            'user_id' => $message->user_id,
            'message_id' => $message->id,
            'type' => 'admin',
            'data' => ['text' => $request->input('text')],
        ];
        Message::create($data);

        $data = [
            'user_id' => $message->user_id,
            'text' => $request->input('text')
        ];
        $messanger = new MessageController();
        $messanger->sendAdminText($data, $user);
    }
}
