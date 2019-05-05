<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $stats = <<<JSON
{
    "games_played" : 0,
    "games_win" : 0,
    "games_host" : 0,
    "rounds_played" : 0,
    "rounds_guesser" : 0,
    "rounds_passed_guesser" : 0,
    "rounds_win_guesser" : 0,
    "rounds_win_helper" : 0,
    "words_submitted" : 0,
    "words_definition" : 0
}
JSON;
        Schema::create('users', function (Blueprint $table) use ($stats) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('image')->default("guest.png");
            $table->json('stats')->default($stats);
            $table->boolean('isGuest')->default(false);
            $table->tinyInteger('active')->default(1)->unsigned();
            $table->uuid('confirmation_code')->nullable();
            $table->boolean('confirmed')->default(config('access.users.confirm_email') ? false : true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
