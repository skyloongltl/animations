<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnimationsInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('animations_information', function (Blueprint $table) {
            $table->increments('id');
            $table->string('image');
            $table->string('name');
            $table->string('index_show')->default('')->comment('类似：全10话');
            $table->string('play')->default('')->comment('播放量');
            $table->tinyInteger('is_finish')->default('0');
            $table->integer('episodes')->comment('共有几集');
            $table->integer('md5_name');
            $table->index('md5_name');
            $table->integer('web_type_id')->comment('决定更新的时候由哪个网站的信息更新');
            //$table->unique('name');
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
        Schema::dropIfExists('animations_information');
    }
}
