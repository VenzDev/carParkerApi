<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;

class CheckParkingController extends Controller
{
    public function checkParking(Request $request){

        $delta = 10;

       /* $from = Carbon::parse($request['from']);
        $to = Carbon::parse($request['to']);
        $to_minutes = $to->minute;
        $iterations = ($to_minutes/$delta)-1;
        $from_delta = Carbon::parse($request['from'])->addMinutes($delta);

        $good_array = [];

        for ($i=0; $i < $iterations; $i++) { 
            if(count($this->checkSides($from,$from_delta,"8"))===0)
            {
                array_push($good_array,[$from,$from_delta]);
            }
            $from->addMinutes(10);
            $from_delta->addMinutes(10);
        }*/
        $from_carbon = Carbon::parse($request['from']);

        $to_carbon = Carbon::parse($request['to']);
        Log::info($to_carbon->diffInMinutes($from_carbon));
        
        $from = $request['from'];
        $to = $request['to'];

        $reservations_between =  Reservation::all()
        ->where('status','RESERVED')
        ->where('system_reservation_from','>=',date($from))
        ->where('system_reservation_to','<=',date($to));

        $reservations_left =  Reservation::all()
        ->where('status','RESERVED')
        ->filter(function($item) use(&$from) {
            if(Carbon::parse($from)->between($item->system_reservation_from,$item->system_reservation_to))
            {
                return $item;
            }
          });

        $reservations_right =  Reservation::all()
        ->where('status','RESERVED')
        ->filter(function($item) use(&$to) {
            if(Carbon::parse($to)->between($item->system_reservation_from,$item->system_reservation_to))
            {
                return $item;
            }
          });

          $merged  =array_merge(
            $reservations_left->toArray(), 
            $reservations_right->toArray(),
            $reservations_between->toArray()
        );

        $unique = array_unique($merged,SORT_REGULAR);
        $final = array_merge($unique,array());


        return response($final);
    }

    public function reserveSlot(Request $request){

        $output = new ConsoleOutput();

        $system_from = Carbon::parse($request['from']);
        $system_to = Carbon::parse($request['to']);

        if ($system_from->minute===0)
        {
            $system_from->subHour();
            $system_from->setMinutes(55);
        }
        else
        {
            $system_from->subMinutes(5);
        }

        $system_to->addMinutes(5);

        
        $slot = new Reservation();
        $slot->user_id = $request['user_id'];
        $slot->reservation_from = $request['from'];
        $slot->system_reservation_from = $system_from;
        $slot->system_reservation_to = $system_to;
        $slot->reservation_to = $request['to'];
        $slot->parking_slot_id = $request['parking_slot_id'];
        $slot->status='RESERVED';

        $slot->save();

        $output->writeln("<info>$system_from $system_to</info>");

        return response()->json(['status'=>'success']);
    }

    private function generalCheck(Request $request)
    {

        $from = $request['from'];
        $to = $request['to'];

        $reservations_between =  Reservation::all()
        ->where('status','RESERVED')
        ->where('system_reservation_from','>=',date($from))
        ->where('system_reservation_to','<=',date($to));

        $reservations_left =  Reservation::all()
        ->where('status','RESERVED')
        ->filter(function($item) use(&$from) {
            if(Carbon::parse($from)->between($item->system_reservation_from,$item->system_reservation_to))
            {
                return $item;
            }
          });

        $reservations_right =  Reservation::all()
        ->where('status','RESERVED')
        ->filter(function($item) use(&$to) {
            if(Carbon::parse($to)->between($item->system_reservation_from,$item->system_reservation_to))
            {
                return $item;
            }
          });

        $array =array_merge(
            $reservations_left->toArray(), 
            $reservations_right->toArray(),
            $reservations_between->toArray()
        );

        return $array;
    }

    private function checkSides(Carbon $from, Carbon $to,$parking_id)
    {
        $reservations_left =  Reservation::all()
        ->where('status','RESERVED')
        ->where('parking_slot_id',$parking_id)
        ->filter(function($item) use(&$from) {
            if($from->between($item->system_reservation_from,$item->system_reservation_to))
            {
                return $item;
            }
          });

        $reservations_right =  Reservation::all()
        ->where('status','RESERVED')
        ->where('parking_slot_id',$parking_id)
        ->filter(function($item) use(&$to) {
            if($to->between($item->system_reservation_from,$item->system_reservation_to))
            {
                return $item;
            }
          });

          return array_unique(array_merge($reservations_left->toArray(),$reservations_right->toArray()),SORT_REGULAR);
        
    }
}
