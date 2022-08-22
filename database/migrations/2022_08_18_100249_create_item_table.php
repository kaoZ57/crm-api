<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('store');
            $table->string('name');
            $table->string('description');
            $table->tinyInteger('is_active')->default(0);
            $table->tinyInteger('is_not_return')->default(0);
            $table->integer('updated_by')->default(0);
            $table->float('amount')->default(0);
            $table->timestamp('amount_update_at')->default(null);
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
        Schema::dropIfExists('item');
    }
}
