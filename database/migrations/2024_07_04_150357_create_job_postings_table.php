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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('title');
            $table->longText('description');
            $table->bigInteger('nature_of_work_id')->unsigned()->nullable();
            $table->string('location')->nullable();
            $table->string('shift')->nullable();
            $table->string('status')->nullable();
            $table->double('salary')->nullable();
            $table->string('salary_type')->nullable();
            $table->bigInteger('added_by')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('added_by')->references('id')->on('users');
            $table->foreign('nature_of_work_id')->references('id')->on('natures_of_work');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_postings');
    }
};
