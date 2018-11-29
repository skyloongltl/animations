<?php
if (function_exists('get_last_hours')) {
    function get_last_hours()
    {
        return floor(time() / 3600) * 3600 - 3600;
    }
}

if (function_exists('get_now_hours')) {
    function get_now_hours()
    {
        return floor(time() / 3600) * 3600;
    }
}

function animation_existed($md5_name)
{
    $result = \App\Models\Animations::where('md5_name', $md5_name)->first();
    if (is_null($result)) {
        return false;
    }
    return true;
}