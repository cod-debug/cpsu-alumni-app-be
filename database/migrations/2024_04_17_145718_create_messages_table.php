<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->longText('message');
            $table->bigInteger('user_from')->unsigned();
            $table->bigInteger('user_to')->unsigned();
            $table->foreign('user_from')->references('id')->on('users');
            $table->foreign('user_to')->references('id')->on('users');
            $table->string('status')->default('sent');
            $table->boolean('is_edited')->default(false);
            $table->boolean('is_seen')->default(false);
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
        Schema::dropIfExists('messages');
    }
};
