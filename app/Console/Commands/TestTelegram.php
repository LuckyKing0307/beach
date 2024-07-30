<?php

namespace App\Console\Commands;

use App\Http\Controllers\BotController;
use Illuminate\Console\Command;
use Telegram\Bot\Api;

class TestTelegram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-telegram';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start test telegram bot';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $resp = new BotController();
        $response = $resp->setWebhook();
        var_dump($response);
    }
}
