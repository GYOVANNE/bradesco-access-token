<?php
namespace Bradesco;

use Bradesco\Helpers\Fixer;

class BoletoApiService extends Resource
{
    public static function create(array $data, bool $fix = true)
    {
        if ($fix) {
            Fixer::fixAll($data);
        }

        $response = parent::create($data);

        return $response;
    }
}