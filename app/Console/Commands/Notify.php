<?php

namespace App\Console\Commands;

use App\Mail\NotificationMail;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Notify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command sending notification when reservation time is coming';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = Carbon::now()->tz('Europe/Warsaw');
        $reservations = Reservation::all()
        ->where('status', 'RESERVED')
        ->where('notify_sent', false)
        ->filter(function ($item) use (&$date) {
            if (Carbon::parse($item->system_reservation_from)->diffInMinutes($date) <= 60) {
                return $item;
            }
        });

        foreach ($reservations as $reservation) {
            $reservation->notify_sent = true;
            $reservation->save();

            $details = [
                'title' => 'Your parking slot is waiting for you.',
                'body' => "Reservation from 
                $reservation->reservation_from to $reservation->reservation_to, 
                parking slot: $reservation->parking_slot_id"
            ];

            $user = User::all()->where('id', $reservation->user_id)->first();
            Mail::to($user->email)->send(new NotificationMail('Reservation - Parker', $details));
        }

        return 0;
    }
}
