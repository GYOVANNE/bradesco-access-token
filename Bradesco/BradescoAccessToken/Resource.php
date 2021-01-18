<?php
namespace Bradesco\BradescoAccessToken;

abstract class Resource
{
    protected static $api = null;

    public static function reconfig()
    {
        static::$api = new Api();
    }

    public static function api()
    {
        if (is_null(static::$api)) {
            static::reconfig();
        }

        return static::$api;
    }

    public static function create()
    {
        $data = static::api()->post();
        return $data;
    }
}