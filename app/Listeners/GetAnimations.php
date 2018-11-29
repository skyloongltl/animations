<?php

namespace App\Listeners;

use App\Event\GetAllAnimations;
use App\Library\Mongodb;
use App\Models\Animation;
use App\Models\Animations;
use App\Models\WebType;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use MongoDB\BSON\ObjectId;
use Illuminate\Support\Facades\DB;

class GetAnimations
{
    private $db;
    private $play_url;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->db = resolve(Mongodb::class);
        $this->play_url = config('animations.bilibili')['play_url'];
    }

    /**
     * Handle the event.
     *
     * @param  GetAllAnimations $event
     * @return void
     */
    public function handle(GetAllAnimations $event)
    {
        DB::beginTransaction();
        switch ($event->type) {
            case "all":
                $this->all();
                break;
            case "bilibili":
                $this->bilibili();
                break;
            case "aqiyi":
                $this->aqiyi();
                break;
            case "tengxun":
                $this->tengxun();
                break;
            case "youku":
                $this->youku();
                break;
        }
        DB::commit();
    }

    private function all()
    {
        $this->bilibili();
    }

    private function bilibili()
    {
        $animations_collection = $this->db->selectCollection('bilibili_animations');
        $animation_collection = $this->db->selectCollection('bilibili_animation_information');

        $type = WebType::query()
            ->select('id')
            ->where('name', 'bilibili')
            ->first();

        $animations_result = $animations_collection->findOne(
            [], [
            'projection' => [
                '_id' => 1,
                'cover' => 1,
                'index_show' => 1,
                'is_finish' => 1,
                'media_id' => 1,
                'order' => 1,
                'title' => 1
            ],
        ]);
        preg_match('|\d+|', $animations_result->index_show, $match);

        $animations_id = Animations::insertGetId([
            'name' => $animations_result->title,
            'image' => $animations_result->cover,
            'index_show' => $animations_result->index_show,
            'is_finish' => $animations_result->is_finish,
            'play' => $animations_result->order->play,
            'episodes' => empty($match[0]) ? 0 : $match[0],
            'web_type_id' => $type->id,
            'md5_name' => crc32(md5($animations_result->title))
        ]);
        $this->handle_bilibili($animations_result->media_id, $animation_collection, $type->id, $animations_id);
        $id = $animations_result->_id;

        while (true) {
            $animations_result = $animations_collection->find(
                [
                    '_id' => [
                        '$gt' => $id
                    ]
                ],
                [
                    'projection' => [
                        '_id' => 1,
                        'cover' => 1,
                        'index_show' => 1,
                        'is_finish' => 1,
                        'media_id' => 1,
                        'order' => 1,
                        'title' => 1
                    ],
                    'limit' => 300,
                    'sort' => ['_id' => 1]
                ]
            );

            if ($animations_result->isDead()) {
                break;
            }

            foreach ($animations_result as $item) {
                if (isset($item->index_show)) {
                    preg_match('|\d+|', $item->index_show, $match);
                }

                $animations_id = Animations::query()
                    ->insertGetId([
                        'name' => $item->title,
                        'image' => $item->cover,
                        'index_show' => empty($item->index_show) ? '' : $item->index_show,
                        'is_finish' => $item->is_finish,
                        'play' => $item->order->play,
                        'episodes' => empty($match[0]) ? 0 : $match[0],
                        'web_type_id' => $type->id,
                        'md5_name' => crc32(md5($item->title))
                    ]);
                $this->handle_bilibili($item->media_id, $animation_collection, $type->id, $animations_id);
                $id = $item->_id;
            }
        }
    }

    private function handle_bilibili($media_id, $collection, $type_id, $animations_id)
    {
        $result = $collection->findOne([
            'media_id' => $media_id
        ], [
            'projection' => [
                'episodes' => 1,
            ],
        ]);

        if (!is_null($result)) {
            foreach ($result->episodes as $episode) {
                Animation::create([
                    'animations_id' => $animations_id,
                    'url' => $this->play_url . $episode->ep_id,
                    'index' => $episode->index_title,
                    'web_type_id' => $type_id
                ]);
            }
        }
    }

    private function aqiyi()
    {
        $animations_collection = $this->db->selectCollection('aqiyi_animations');
        $animation_collection = $this->db->selectCollection('aqiyi_animation_information');

        $type_id = WebType::where('name', 'aqiyi')
            ->first()
            ->id;

        $animation_result = $animations_collection->findOne(
            [], [
            'projection' => [
                '_id' => 1,
                'img' => 1,
                'name' => 1,
                'albumId' => 1,
                'episodes' => 1
            ]
        ]);

        $time = date('Y-m-d H:i:s', time());
        if (!animation_existed(crc32(md5($animation_result->name)))) {
            $animations_id = Animations::insertGetId([
                'image' => $animation_result->img,
                'name' => $animation_result->name,
                'index_show' => "全{$animation_result->episodes}话",
                'episodes' => $animation_result->episodes,
                'md5_name' => crc32(md5($animation_result->name)),
                'web_type_id' => $type_id,
                'created_at' => $time,
                'updated_at' => $time
            ]);
            $this->handle_aqiyi($animation_result->albumId, $animation_collection, $type_id, $animations_id);
        }
        $id = $animation_result->_id;

        while (true) {
            $animations_result = $animations_collection->find([
                '_id' => ['$gt' => $id]
            ], [
                'projection' => [
                    '_id' => 1,
                    'img' => 1,
                    'name' => 1,
                    'albumId' => 1,
                    'episodes' => 1
                ],
                'limit' => 300,
                'sort' => ['_id' => 1]
            ]);

            if ($animations_result->isDead()) {
                break;
            }

            foreach ($animations_result as $result) {
                if (!animation_existed(crc32(md5($result->name)))) {
                    $id = Animations::insertGetId([
                        'image' => $result->img,
                        'name' => $result->name,
                        'index_show' => "全{$result->episodes}话",
                        'episodes' => $result->episodes,
                        'md5_name' => crc32(md5($result->name)),
                        'web_type_id' => $type_id,
                        'created_at' => $time,
                        'updated_at' => $time
                    ]);
                    $this->handle_aqiyi($result->albumId, $animation_collection, $type_id, $id);
                }
                $id = $result->_id;
            }
        }
    }

    private function handle_aqiyi($album_id, $collection, $type_id, $animations_id)
    {
        $time = date('Y-m-d H:i:s', time());
        $result = $collection->findOne([
            'albumId' => $album_id
        ], [
            'projection' => [
                'list' => 1
            ]
        ]);

        if (!empty($result->list)) {
            $insert = [];
            foreach ($result->list as $item) {
                $insert[] = [
                    'animations_id' => $animations_id,
                    'url' => empty($item->vurl) ? $item : $item->vurl,
                    'index' => empty($item->vt) ? '' : $item->vt,
                    'web_type_id' => $type_id,
                    'created_at' => $time,
                    'updated_at' => $time
                ];
            }

            Animation::insert($insert);
        }
    }

    private function tengxun()
    {

    }

    private function youku()
    {

    }
}
