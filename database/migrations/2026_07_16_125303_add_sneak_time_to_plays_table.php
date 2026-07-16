<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSneakTimeToPlaysTable extends Migration
{
    public function up()
    {
        Schema::table('plays', function (Blueprint $table) {
            $table->decimal('sneak_time', 8, 2)
                ->default(0)
                ->after('stamina_item_count');
        });
    }

    public function down()
    {
        Schema::table('plays', function (Blueprint $table) {
            $table->dropColumn('sneak_time');
        });
    }
}