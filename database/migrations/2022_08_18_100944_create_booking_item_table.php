<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('booking');
            $table->foreignId('item_id')->constrained('item');
            $table->foreignId('status_id')->constrained('status');
            $table->string('note_user');
            $table->string('note_owner');
            $table->float('amount')->default(1);
            $table->integer('updated_by')->default(0);
            $table->date('return_date')->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_item');
    }
}
