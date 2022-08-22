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
        Schema::create('scenes_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('scene_id');
            $table->text('memo');
            $table->timestamps();

            $table->foreign('scene_id')->references('id')->on('scenes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scenes_comments');
    }
};
