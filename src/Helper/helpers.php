<?php

use Hyperf\Context\ApplicationContext;
use Psr\SimpleCache\CacheInterface;

if (!function_exists('uuid')) {
    function uuid($length)
    {
        if (function_exists('random_bytes')) {
            $uuid = bin2hex(\random_bytes($length));
        } else if (function_exists('openssl_random_pseudo_bytes')) {
            $uuid = bin2hex(\openssl_random_pseudo_bytes($length));
        } else {
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $uuid = substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
        }
        return $uuid;
    }
}

if (!function_exists('p')) {
    function p($val, $title = null, $starttime = '')
    {
        print_r('[ ' . date('Y-m-d H:i:s') . ']:');
        if ($title != null) {
            print_r('[' . $title . ']:');
        }
        print_r($val);
        print_r("\r\n");
    }
}


if (!function_exists('cache')) {
    function cache(): CacheInterface
    {
        return ApplicationContext::getContainer()->get(CacheInterface::class);
    }
}

if (!function_exists('cache_has_set')) {
    function cache_has_set(string $key, $callback, $tll = 3600)
    {
        $data = cache()->get($key);
        if ($data || $data === false) {
            return $data;
        }
        $data = call_user_func($callback);
        if ($data === null) {
            p('设置空缓存防止穿透');
            cache()->set($key, false, 10);
        } else {
            cache()->set($key, $data, $tll);
        }
        return $data;
    }
}
