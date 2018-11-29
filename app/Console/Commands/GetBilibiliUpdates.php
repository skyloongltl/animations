<?php

namespace App\Console\Commands;

use App\Event\UpdateAllAnimations;
use App\Library\Mongodb;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class GetBilibiliUpdates extends Command
{
    private $animations_collection;
    private $animations_information_collection;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:bilibili';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取本周bilibili更新情况';

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
        $update_url = config('animations.bilibili', '')['update_url'];
        $client = new Client([
            'base_uri' => config('animations.bilibili')['base_url']
        ]);
        $crawler = $client->request('GET', $update_url);
        $update_data = json_decode($crawler->getBody()->getContents(), true);
        $today_index = 6;
        if (!empty($update_data['result'])
            && $update_data['result'][$today_index]['is_today'] != 1) {
            foreach ($update_data['result'] as $key => $data) {
                if ($data['is_today'] == 1) {
                    $today_index = $key;
                    break;
                }
            }
        }

        if (!empty($update_data['result'][$today_index]['seasons'])) {
            $this->update($update_data, $today_index, $headers, $client);
        }
        $client = null;
    }

    public function update($update_data, $today_index, $headers, $client)
    {
        Cache::forget('update_list');
        $update_list = [
            'bilibili' => [],
            'aiqiyi' => [],
            'tengxun' => [],
            'youku' => []
        ];
        $detail_url = config('animations.bilibili')['update_detail_url'];
        $i = 0;
        foreach ($update_data['result'][$today_index]['seasons'] as $value) {
            if (strtotime($value['pub_time']) < get_now_hours() && strtotime($value['pub_time']) >= get_last_hours()) {
                $res = $this->animations_collection->findOne([
                    'season_id' => $value['season_id']
                ], [
                    'projection' => [
                        'media_id' => 1,
                    ]
                ]);

                if ($res === null) {
                    //TODO 如果是新番，该怎么更新list
                    continue;
                }

                $this->animations_collection->updateOne([
                    'media_id' => $res->media_id,
                ], [
                    '$set' => [
                        'index_show' => '更新至' . $value['pub_index']
                    ]
                ]);
                $response = $client->request('GET', $detail_url . $res->media_id, $headers);
                $this->animations_information_collection->replaceOne([
                    'media_id' => $res->media_id,
                ], json_decode($response->getBody()->getContents(), true)['result']);
                $update_list['bilibili'][$i]['media_id'] = $res->media_id;
                preg_match('|\d+|', $value['pub_index'], $match);
                $update_list['bilibili'][$i]['episode'] = $match[0];
                $i++;
            }
        }
        Cache::forever('update_list', json_encode($update_list));
    }

    public function init()
    {
        $db = resolve(Mongodb::class);
        $this->animations_collection = $db->selectCollection('bilibili_animations');
        $this->animations_information_collection = $db->selectCollection('bilibili_animation_information');
    }
}
