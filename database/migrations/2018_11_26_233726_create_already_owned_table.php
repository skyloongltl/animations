<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlreadyOwnedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('already_owned', function (Blueprint $table) {
            $table->increments('id');
            $table->increments('web_type_id');
            $table->string('a_id')->comment('每个网站给番剧生成的id，不是animations的id');
            $table->increments('animations_id');
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
        Schema::dropIfExists('already_owned');
    }
}
