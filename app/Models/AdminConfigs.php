<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\AsSource;

class AdminConfigs extends Model
{
    use HasFactory, AsSource;
    protected $fillable = [
        'name',
        'function',
        'trigger_bg',
        'trigger_en',
        'type',
        'data',
    ];
    protected $casts = [
        'data' => 'array',
        'trigger' => 'array',
    ];

    public function attachment()
    {
        return $this->hasOne(Attachment::class, 'id', 'function')->get();
    }
}
