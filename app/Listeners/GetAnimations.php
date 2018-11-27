<?php

namespace App\Listeners;

use App\Event\GetAllAnimations;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class GetAnimations
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
     * @param  GetAllAnimations  $event
     * @return void
     */
    public function handle(GetAllAnimations $event)
    {
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
    }

    private function all()
    {

    }

    private function bilibili()
    {

    }

    private function aqiyi()
    {

    }

    private function tengxun()
    {

    }

    private function youku()
    {

    }
}
