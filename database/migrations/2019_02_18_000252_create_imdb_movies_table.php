<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImdbMoviesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imdb_movies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 191)->unique();
            $table->string('title_of_movie', 191)->nullable();
            $table->text('main_picture')->nullable();
            $table->string('rate', 4)->nullable();
            $table->text('summary')->nullable();
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
        Schema::dropIfExists('imdb_movies');
    }
}
