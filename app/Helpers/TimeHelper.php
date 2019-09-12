<?php

namespace App\Helpers;

class TimeHelper
{
    public static function server_elapsed_time()
    {
        $elapsed = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        return floor($elapsed * 1000);
    }
}
