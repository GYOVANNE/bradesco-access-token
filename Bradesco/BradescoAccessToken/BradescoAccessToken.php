<?php
namespace App\Services\Financeiro\Bradesco\BradescoAccessToken;

use Bradesco\Exceptions\BradescoParameterException;

class BradescoAccessToken
{
    const SANDBOX                 = 'BRADESCO_SANDBOX_JWT';

    private static $apiUrl        = null;
    private static $sandbox       = null;

    private static $defIsSandbox  = true;
    
    private static $urlHomologation     = 'https://proxy.api.prebanco.com.br/auth/server/v1.1/token';
    private static $urlProduction       = 'https://openapi.bradesco.com.br/auth/server/v1.1/token';

    public static function setIsSandbox(bool $enable = null)
    {
        static::$sandbox = $enable;

        if (static::$sandbox === null) {
            static::$sandbox = getenv(static::SANDBOX);

            if (static::$sandbox === false) {
                static::$sandbox = static::$defIsSandbox;
            }
        }

        if (is_string(static::$sandbox)) {
            static::$sandbox = (static::$sandbox == 'false' ? false : true);
        }
    }

    public static function isSandbox()
    {
        if (static::$sandbox === null) {
            static::setIsSandbox();
        }

        return static::$sandbox;
    }

    public static function setApiUrl(string $url = null)
    {
        static::$apiUrl = $url;

        if (static::$apiUrl === null) {
            static::$apiUrl = static::isSandbox() ? static::$urlHomologation : static::$urlProduction;
        }
    }

    public static function getApiUrl()
    {
        if (static::$apiUrl === null) {
            static::setApiUrl();
        }

        return static::$apiUrl;
    }

}