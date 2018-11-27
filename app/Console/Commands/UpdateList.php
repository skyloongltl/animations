<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use App\Library\Mongodb;

class UpdateList extends Command
{
    private $client = null;
    private $animations_collection;
    private $headers = [];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:list {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function init()
    {
        $db = resolve(Mongodb::class);
        switch ($this->argument('type')) {
            case "bilibili":
                $this->animations_collection = $db->selectCollection('bilibili_animations');
                break;
            case "aqiyi":
                $this->animations_collection = $db->selectCollection('aqiyi_animations');
                break;
            case "tengxun":
                $this->animations_collection = $db->selectCollection('tengxun_animations');
                break;
            case "youku":
                $this->animations_collection = $db->selectCollection('youku_animations');
                break;
        }
        $this->headers = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Referer' => 'https://www.bilibili.com/anime/index/',
                'Origin' => 'https://www.bilibili.com',
                'Host' => 'bangumi.bilibili.com',
                'Accept' => 'application/json, text/plain, */*',
                'Accept-Language' => 'zh-CN,zh;q=0.9',
            ]
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $page = 1;
        switch ($this->argument('type')) {
            case 'all':
                $this->bilibili($page);
                $this->aqiyi($page);
                $this->tengxun($page);
                $this->youku($page);
                break;
            case "bilibili":
                $this->bilibili($page);
                break;
            case "aqiyi":
                $this->aqiyi($page);
                break;
            case "tengxun":
                $this->tengxun($page);
                break;
            case "youku":
                $this->youku($page);
                break;
        }
    }

    private function bilibili($page)
    {
        $size = 20;
        $is_init = false;
        while ($page) {
            $animations_url = "/media/web_api/search/result?season_version=-1&area=-1&is_finish=-1&copyright=-1&season_status=-1&season_month=-1&pub_date=-1&style_id=-1&order=3&st=1&sort=0&season_type=1&pagesize={$size}&page=";
            $response = $this->client->request('GET', $animations_url . $page, $this->headers);
            $result = json_decode($response->getBody()->getContents(), true);
            if (!empty($result['result']) && !empty($result['result']['data'])) {
                $this->animations_collection->insertMany($result['result']['data']);
            }
            if ($is_init === false) {
                $is_init = true;
                $page = ceil($result['result']['page']['total'] / $size);
            }
            $page--;
            usleep(200000);
        }
    }

    private function aqiyi($page)
    {

    }

    private function tengxun($page)
    {

    }

    private function youku($page)
    {

    }
}
