<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    protected $fillable = [
        'telegram_id', 'username', 'first_name', 'last_name',
    ];
    
    protected $casts = [
        'telegram_id' => 'integer',
        'is_bot' => 'boolean',
        'is_premium' => 'boolean',
        'birthday' => 'date',
        'extra' => 'array', // Store arbitrary data as JSON
    ];
}
