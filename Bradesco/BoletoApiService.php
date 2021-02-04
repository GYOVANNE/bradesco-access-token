<?php
namespace Bradesco;

use Bradesco\Helpers\Fixer;

class BoletoApiService
{
    public static function create(array $data = [])
    {
        Fixer::fixAll($data);
        $api = new Api();
        return $api->post($data);
    }
}