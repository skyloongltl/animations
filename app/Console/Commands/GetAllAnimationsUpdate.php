<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Event\UpdateAllAnimations;
use Illuminate\Support\Facades\Cache;

class GetAllAnimationsUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:all';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('update:bilibili');
        $this->call('update:aqiyi');
        $this->call('update:tengxun');
        $this->call('update:youku');
        $update_list = Cache::get('update_list');
        Cache::forget('update_list');
        if (!empty($update_list))
            event(new UpdateAllAnimations('all', json_decode($update_list, true)));
    }
}
