<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Booking extends Model
{
    use HasFactory, AsSource;

    protected $fillable  = [
        'user_id',
        'item_id',
        'active',
        'checked',
        'day',
    ];
}
