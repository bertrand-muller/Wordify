<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWordsTable extends Migration {

    /**
     * Run the migrations.
     * @return void
     */
    public function up() {
        Schema::create('words', function (Blueprint $table) {

            $table->increments('id');
            $table->string('french');
            $table->string('english');
            $table->string('frenchDefinition');
            $table->string('englishDefinition');
            $table->string('picture');
            $table->integer('user_id')->unsigned();
            $table->timestamps();

            $table->foreign('user_id', 'fk_foreign_words')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

        });
    }


    /**
     * Reverse the migrations.
     * @return void
     */
    public function down() {
        Schema::dropIfExists('words');
    }

}
