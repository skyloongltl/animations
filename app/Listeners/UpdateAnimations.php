<?php

namespace App\Listeners;

use App\Event\UpdateAllAnimations;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateAnimations
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $this->bilibili($update_list['bilibili']);
        $this->aqiyi($update_list['aqiyi']);
        $this->tengxun($update_list['tengxun']);
        $this->youku($update_list['youku']);
    }

    private function bilibili($update_list)
    {

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
