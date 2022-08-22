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
        Schema::create('scenes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('character_id');
            $table->unsignedInteger('prop_id');
            $table->unsignedInteger('first_page');
            $table->unsignedInteger('final_page');
            $table->boolean('usage');
            $table->timestamps();

            $table->foreign('character_id')->references('id')->on('characters');
            $table->foreign('prop_id')->references('id')->on('props');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scenes');
    }
};
