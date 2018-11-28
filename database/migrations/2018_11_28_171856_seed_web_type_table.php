<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedWebTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            [
                'name' => 'bilibili',
                'icon' => ''
            ],
            [
                'name' => 'aqiyi',
                'icon' => ''
            ],
            [
                'name' => 'tengxun',
                'icon' => ''
            ],
            [
                'name' => 'youku',
                'icon' => ''
            ]
        ];
        DB::table('web_type')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('web_type')->truncate();
    }
}
