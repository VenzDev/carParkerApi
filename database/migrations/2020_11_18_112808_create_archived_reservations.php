<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivedReservations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archived_reservations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('parking_slot_id',20);
            $table->boolean('notify_sent')->default(true);
            $table->dateTime('reservation_from');
            $table->dateTime('reservation_to');
            $table->dateTime('system_reservation_from');
            $table->dateTime('system_reservation_to');
            $table->string('status',20);
            $table->bigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archived_reservations');
    }
}
