<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function messages()
    {
        return $this->hasMany('App\Models\Message');
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($ticket) {
            $ticket->messages()->each(function ($message) {
                $message->delete();
            });
        });
    }
}
