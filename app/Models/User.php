<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $appends = ['has_ticket'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function reservations()
    {
        return $this->hasMany('App\Models\Reservation');
    }

    public function tickets()
    {
        return $this->hasMany('App\Models\Ticket');
    }

    public function getHasTicketAttribute(){
        $ticket = Ticket::query()->where('user_id', $this->attributes['id'])->where('is_finished',false)->first();

        if($ticket){
        return true;
        }
        return false;
    }
}
