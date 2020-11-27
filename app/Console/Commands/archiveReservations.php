<?php

namespace App\Console\Commands;

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
    protected $signature = 'minute:update';

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
        $output = new ConsoleOutput();
        $date = Carbon::now()->tz('Europe/Warsaw');
        $reservations = Reservation::all()->where('status', '=', 'RESERVED')->where('reservation_to', '<', $date);
        $output->writeln("<info>------------------------------</info>");
        $output->writeln("<info>${date}</info>");
        foreach ($reservations as $reservation) {
            $reservation->status = 'ARCHIVED';
            $reservation->save();
        }
        return 0;
    }
}
