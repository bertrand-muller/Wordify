<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('game', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->unique();
            $table->string('currentWord')->nullable();
            $table->json('playersWord')->nullable();
            $table->json('data');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     * @return void
     */
    public function down() {
        Schema::dropIfExists('game');
    }
}
