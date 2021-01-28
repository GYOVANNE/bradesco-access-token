<?php
namespace Bradesco;

use Bradesco\Helpers\Fixer;

class BoletoApiService
{
    public static function create(array $data, bool $fix = true)
    {
        if ($fix) {
            Fixer::fixAll($data);
        }
        $api = new Api();
        return $api->post($data);
    }
}