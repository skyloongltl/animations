<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client;

class GetAqiyiUpdates extends Command
{
    private $animations_collection;
    private $animation_collection;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:aqiyi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取爱奇艺更新的番剧信息';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url = config('animations.aqiyi_url')['update_url'];
        $goutte_client = new GoutteClient();

        $crawler = $goutte_client->request('GET', $url);
        $res = $crawler->filterXPath('//*[@data-seq="5"]');
        var_dump($res->each(function ($node) {
            echo addcslashes($node->text(), "\n\r\t");
        }));
    }

    private function init()
    {
        $db = resolve(\App\Library\Mongodb::class);
        $this->animations_collection = $db->selectCollection('aqiyi_animations');
        $this->animation_collection = $db->selectCollection('aqiyi_animation_information');
    }
}
