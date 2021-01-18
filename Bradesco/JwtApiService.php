<?php
namespace Bradesco;

use Bradesco\BradescoAccessToken\Resource;

class JwtApiService extends Resource
{
    public static function create()
    {
        $response = parent::create();
        return $response;
    }
}