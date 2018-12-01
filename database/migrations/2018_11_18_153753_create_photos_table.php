<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('google_id')->nullable();
            $table->integer('album_id')->nullable();
            $table->integer('sort_order')->nullable();
            $table->string('title');
            $table->string('desc')->nullable();
            $table->string('filename')->nullable();
            $table->string('type')->nullable();
            $table->text('keywords')->nullable();
            $table->integer('google_album_id')->nullable();
            $table->double('geo_latitude',11,2)->nullable();
            $table->double('geo_longitude',11,2)->nullable();
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
        Schema::dropIfExists('photos');
    }
}
