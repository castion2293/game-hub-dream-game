<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRawDreamGameTicketsIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->dropIfExists(config('dream_game.db_collection_name'));
        
        Schema::connection('mongodb')->create(config('dream_game.db_collection_name'), function ($collection) {
            $collection->unique('uuid');
            $collection->index(['betTime', 'is_converted']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->dropIfExists(config('dream_game.db_collection_name'));
    }
}