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

    protected $appends = ['to_open','to_close','to_system_close','can_cancel'];
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
        $system_from = Carbon::parse($this->attributes['system_reservation_from']);
        $diff = $system_from->diffInSeconds($today);

        if ($today >= $system_from) {
            return 0;
        }

        return $diff;
    }

    public function getToCloseAttribute()
    {
        $today = Carbon::now();

        $system_to = Carbon::parse($this->attributes['reservation_to']);

        $diff = $system_to->diffInSeconds($today);

        if ($today >= $system_to) {
            return 0;
        }

        return $diff;
    }

    public function getToSystemCloseAttribute()
    {
        $today = Carbon::now();

        $system_to = Carbon::parse($this->attributes['system_reservation_to']);

        $diff = $system_to->diffInSeconds($today);

        if ($today >= $system_to) {
            return 0;
        }

        return $diff;
    }

    public function getCanCancelAttribute()
    {
        $today = Carbon::now();

        $system_to = Carbon::parse($this->attributes['system_reservation_from']);

        $diff = $system_to->diffInMinutes($today);
        Log::info("diff ----------------- diff");
        Log::info($diff);
        if ($diff < 60) {
            return false;
        }
        return true;
    }
}
