<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Screen\AsSource;

class Message extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'id',
        'user_id',
        'message_id',
        'data',
        'type',
    ];
    protected $casts = [
        'data' => 'array',
    ];

    public function user(): hasMany
    {
        $user =  $this->hasMany(TelegramUser::class, 'user_id', 'user_id');
        return $user;
    }
}
