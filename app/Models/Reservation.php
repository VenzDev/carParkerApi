<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['parking_slot_id,reservation_from,reservation_to,status,user_id'];

    protected $appends = ['to_open','to_close','to_system_close'];
    /**
     * @var mixed
     */

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getToOpenAttribute()
    {
        $one_day_seconds = 86400;
        $today = Carbon::now();
        $system_from =Carbon::parse($this->attributes['system_reservation_from']);
        $diff = $system_from->diffInSeconds($today);

        Log::info($diff);

        if($today > $system_from)
        {
            return null;
        }
        else
        {
            if($diff>=$one_day_seconds)
            {
                return gmdate('d H:i:s',$diff);
            }
            else
            {
                return gmdate('H:i:s',$diff);
            }
        }
    }

    public function getToCloseAttribute()
    {
        $today = Carbon::now();

        $system_to = Carbon::parse($this->attributes['reservation_to']);

        $diff = $system_to->diffInSeconds($today);

        if($today > $system_to)
        {
            return null;
        }
        else
        {
            return gmdate('H:i:s',$diff);
        }
    }

    public function getToSystemCloseAttribute()
    {
        $today = Carbon::now();

        $system_to = Carbon::parse($this->attributes['system_reservation_to']);

        $diff = $system_to->diffInSeconds($today);

        if($today > $system_to)
        {
            return null;
        }
        else
        {
            return gmdate('H:i:s',$diff);
        }
    }

}
