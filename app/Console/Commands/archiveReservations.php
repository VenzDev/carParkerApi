<?php

namespace App\Console\Commands;

use App\Models\ArchivedReservation;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\ConsoleOutput;

class ArchiveReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command archives reservations when "to" time expired';

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
        $reservations = Reservation::all()->where('status', '=', 'RESERVED')->where('reservation_to', '<', $date);
        foreach ($reservations as $reservation) {
            ArchivedReservation::archiveReservation($reservation);
        }
        return 0;
    }
}
