<?php

namespace App\Listeners;

use App\Event\UpdateAllAnimations;
use App\Library\Mongodb;
use App\Models\Animation;
use App\Models\Animations;
use App\Models\WebType;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateAnimations
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
     * @param  UpdateAllAnimations  $event
     * @return void
     */
    public function handle(UpdateAllAnimations $event)
    {
        switch ($event->type) {
            case "all":
                $this->all($event->update_list);
                break;
            case "bilibili":
                $this->bilibili($event->update_list);
                break;
            case "aqiyi":
                $this->aqiyi($event->update_list);
                break;
            case "tengxun":
                $this->tengxun($event->update_list);
                break;
            case "youku":
                $this->youku($event->update_list);
                break;
        }
    }

    private function all($update_list)
    {

    }

    private function bilibili(array $update_list)
    {
        $list = $update_list['bilibili'];
        if (empty($list)) {
            return;
        }

        $type = WebType::select('id')->where('name', 'bilibili')->first();

        $animations_collection = $this->db->selectCollection('bilibili_animations');
        $animation_collection = $this->db->selectCollection('bilibili_animation_information');

        foreach ($list as $item) {
            $animation_information = $animations_collection->findOne([
                'media_id' =>  $item['media_id']
            ], [
                'projection' => [
                    'index_show' => 1,
                    'cover' => 1,
                    'order' => 1,
                    'is_finish' => 1,
                    'title' => 1,
                ]
            ]);
            Animations::query()
                ->where('md5_name', crc32(md5($animation_information->title)))
                ->update([
                    'image' => $animation_information->cover,
                    'index_show' => $animation_information->index_show,
                    'play' => $animation_information->order['play'],
                    'episodes' => $item['episode'],
                    'is_finish' => $animation_information->is_finish,
            ]);

            $episodes = $animation_collection->findOne([
                'media_id' => $item['media_id']
            ], [
                'projection' => [
                    'episodes' => 1
                ]
            ]);

            $animations_id = Animations::select('id')
                ->where('md5_name', crc32(md5($animation_information->title)))
                ->first()
                ->id;

            if (!empty($episodes->episodes)) {
                $episode = end($episodes->episodes);
                $time = date('Y-m-d H:i:s', time());
                Animation::query()
                    ->insert([
                        'animations_id' => $animations_id,
                        'url' => $this->play_url . $episode->ep_id,
                        'index' => $episode->index_title,
                        'web_type_id' => $type->id,
                        'created_at' => $time,
                        'update_at' => $time,
                    ]);
            }
        }
    }

    private function aqiyi($update_list)
    {

    }

    private function tengxun($update_list)
    {

    }

    private function youku($update_list)
    {

    }
}
