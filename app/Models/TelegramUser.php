<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class TelegramUser extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'id',
        'user_id',
        'first_name',
        'username',
        'language',
        'active',
        'block',
        'on_chat',
        'phone'
    ];

}
