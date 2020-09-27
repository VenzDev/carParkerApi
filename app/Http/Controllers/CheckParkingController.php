<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Symfony\Component\Console\Output\ConsoleOutput;

class CheckParkingController extends Controller
{
    public function checkParking(Request $request){

        $output = new ConsoleOutput();

        $date = Carbon::createFromFormat('Y-m-d H:i:s',$request['from']);


        $from = $request['from'];
        $to = $request['to'];

        $reservations =  Reservation::all()
        ->where('status','RESERVED')
        ->where('reservation_from','>=',date($from))
        ->where('reservation_to','<=',date($to));


        $output->writeln("<info>------------------------------</info>");
        $output->writeln("<info>$date $from</info>");

        return response()->json($reservations);
    }

    public function reserveSlot(Request $request){

        $slot = new Reservation();
        $slot->user_id = $request['user_id'];
        $slot->reservation_from = $request['from'];
        $slot->reservation_to = $request['to'];
        $slot->parking_slot_id = $request['parking_slot_id'];
        $slot->status='RESERVED';

        $slot->save();

        return response()->json(['status'=>'success']);
    }
}
