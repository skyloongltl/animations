<?php

namespace App\Console\Commands;

use App\Library\Mongodb;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class GetBilibiliAnimations extends Command
{
    private $animations_collection;
    private $animations_information_collection;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:bilibili';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取哔哩哔哩的番剧信息';

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
        $base_url = config('animations.bilibili')['base_url'];
        $page = 1;
        $size = 20;
        $headers = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Referer' => 'https://www.bilibili.com/anime/index/',
                'Origin' => 'https://www.bilibili.com',
                'Host' => 'bangumi.bilibili.com',
                'Accept' => 'application/json, text/plain, */*',
                'Accept-Language' => 'zh-CN,zh;q=0.9',
            ]
        ];
        $client = new Client([
            'base_uri' => $base_url
        ]);

        $is_init = false;

        while ($page) {
            $result = $this->get_animation_list($page, $headers, $client);
            if (!empty($result['result']) && !empty($result['result']['data'])) {
                $this->animations_collection->insertMany($result['result']['data']);
                $this->animations_information_collection->insertMany($this->get_animation_information($result, $headers, $client));
            }
            if ($is_init === false) {
                $is_init = true;
                $page = ceil($result['result']['page']['total'] / $size);
            }
            $page--;
            usleep(200000);
        }
        event(new \App\Event\GetAllAnimations('bilibili'));
    }

    public function init()
    {
        $db = resolve(Mongodb::class);
        $this->animations_collection = $db->selectCollection('bilibili_animations');
        $this->animations_information_collection = $db->selectCollection('bilibili_animation_information');
    }

    public function get_animation_list($page, $headers, ClientInterface $client, $size = 20)
    {
        $animations_url = "/media/web_api/search/result?season_version=-1&area=-1&is_finish=-1&copyright=-1&season_status=-1&season_month=-1&pub_date=-1&style_id=-1&order=3&st=1&sort=0&season_type=1&pagesize={$size}&page=";
        $response = $client->request('GET', $animations_url . $page, $headers);
        $result = json_decode($response->getBody()->getContents(), true);
        return $result;
    }

    public function get_animation_information($result, $headers, ClientInterface $client)
    {
        $detail_url = '/view/web_api/season?media_id=';
        $information = [];
        foreach ($result['result']['data'] as $res) {
            $response = $client->request('GET', $detail_url . $res['media_id'], $headers);
            $animation = json_decode($response->getBody()->getContents(), true);
            if ($animation['code'] === 0) {
                $information[] = $animation['result'];
            }
            usleep(200000);
        }
        return $information;
    }
}
