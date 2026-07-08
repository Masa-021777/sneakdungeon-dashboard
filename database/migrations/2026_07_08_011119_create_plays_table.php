<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
  public function up()
{
    Schema::create('plays', function (Blueprint $table) {
        $table->id();
        $table->string('session_id', 36);
        $table->string('player_name', 100);
        $table->float('clear_time')->nullable();
        $table->integer('mission_count')->default(0);
        $table->boolean('mission1_done')->default(false);
        $table->boolean('mission2_done')->default(false);
        $table->boolean('mission3_done')->default(false);
        $table->integer('death_count')->default(0);
        $table->integer('punch_count')->default(0);
        $table->integer('chat_count')->default(0);
        $table->integer('stamina_item_count')->default(0);
        $table->string('room_id', 20);
        $table->dateTime('played_at');
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
        Schema::dropIfExists('plays');
    }
}
