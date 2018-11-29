<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class GetAqiyiAnimations extends Command
{
    private $animations_collection;
    private $animation_collection;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:aqiyi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取爱奇艺番剧信息';

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
        $goutte_client = new GoutteClient();
        $guzzle_client = new Client();

        $is_end = false;
        $url = config('animations.aqiyi_url')['list_url'];

        while (!$is_end) {
            $crawler = $goutte_client->request('GET', $url);
            $next_node = $crawler->filter('div.mod-page')->children()->getNode(11);
            $next_node->nodeName !== 'span' ? : $is_end = true;
            $url = $next_node->getAttribute('href');
            $elements = $crawler->filter('a.site-piclist_pic_link');
            $animations = $elements->each(function (Crawler $crawler) {
                $animation_information = [];
                $animation_information['href'] = $crawler->attr('href');
                $animation_information['name'] = $crawler->attr('title');
                $animation_information['img'] = $crawler->filter('img')->attr('src');
                $animation_information['albumId'] = $crawler->attr('data-qidanadd-albumid');

                return $animation_information;
            });
            empty($animations) ? : $this->animations_collection->insertMany($animations);
            $crawler->clear();

            foreach ($animations as $animation) {
                $page = 1;
                $total = 1;
                $page_size = 50;
                //获取url中域名后的字母,a为有集数的动画，v为电影
                $type = mb_substr($animation['href'], strlen('http://www.iqiyi.com/'), 1);
                $animation_information = [];
                $animation_information['albumId'] = $animation['albumId'];
                $animation_information['list'] = [];
                if ($type === 'a') {
                    while ($page <= $total) {
                        echo '--------开始获取 ' . $animation['name'] . " 第{$page}页的数据--------" . PHP_EOL;
                        $animation_url = "http://cache.video.iqiyi.com/jp/avlist/{$animation['albumId']}/{$page}/{$page_size}/?albumId={$animation['albumId']}1&pageNum={$page_size}&pageNo={$page}&callback=window.Q.__callbacks__.cbbtcgtx";
                        $response = $guzzle_client->request('GET', $animation_url);
                        preg_match('|"data":(.*}),|s', $response->getBody()->getContents(), $match);
                        if (empty($match[0])) {
                            $error_urls[] = $animation['href'];
                            $this->animation_collection->insertOne($animation_information);
                            $page++;
                            echo "获取{$animation['name']}第{$page}页出现错误" . PHP_EOL;
                            continue;
                        } else {
                            $json = json_decode($match[1], true);
                        }
                        if ($page === 1) {
                            $total = ceil(intval($json['pt']) / $page_size);
                        }
                        $animation_information['list'] = array_merge($animation_information['list'], $json['vlist']);
                        $page++;
                        echo '--------获取 ' . $animation['name'] . " 第{$page}页的数据结束--------" . PHP_EOL;
                        if (error_get_last() !== null) {
                            echo "获取{$animation['name']}第{$page}页时出现错误： " . implode('-', error_get_last()) . PHP_EOL;
                        }
                        usleep(200000);
                    }
                } elseif ($type === 'v') {
                    $animation_information['list'][] = $animation['href'];
                }
                empty($animation_information) ? : $this->animation_collection->insertOne($animation_information);
                $this->animations_collection->updateOne(
                    [
                        'albumId' => strval($animation['albumId'])
                    ], [
                        '$set' => ['episodes' => count($animation_information['list'])]
                    ]
                );
                unset($json);
            }
        }
    }

    private function init()
    {
        $db = resolve(\App\Library\Mongodb::class);
        $this->animations_collection = $db->selectCollection('aqiyi_animations');
        $this->animation_collection = $db->selectCollection('aqiyi_animation_information');
    }
}
