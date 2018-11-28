<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMongodbCollections extends Migration
{
    private  $db;

    public function __construct()
    {
        $this->db = resolve(\App\Library\Mongodb::class);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*$this->db->createCollection('bilibili_animations');
        $this->db->createCollection('bilibili_animation_information');

        $this->db->createCollection('aqiyi_animations');
        $this->db->createCollection('aqiyi_animation_information');

        $this->db->createCollection('tengxun_animations');
        $this->db->createCollection('tengxun_animation_information');

        $this->db->createCollection('youku_animations');
        $this->db->createCollection('youku_animation_information');*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*$this->db->dropCollection('bilibili_animations');
        $this->db->dropCollection('bilibili_animation_information');

        $this->db->dropCollection('aqiyi_animations');
        $this->db->dropCollection('aqiyi_animation_information');

        $this->db->dropCollection('tengxun_animations');
        $this->db->dropCollection('tengxun_animation_information');

        $this->db->dropCollection('youku_animations');
        $this->db->dropCollection('youku_animation_information');*/
    }

    public function isExistsCollection($name)
    {
        foreach ($this->db->listCollections() as $collectionInfo) {
            if ($name === $collectionInfo->name)
                return true;
        }
        return false;
    }
}
