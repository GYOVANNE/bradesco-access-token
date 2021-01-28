<?php
namespace Bradesco;

use Bradesco\Exceptions\BradescoParameterException;

class Bradesco
{
    const SANDBOX                 = 'BRADESCO_SANDBOX';

    private static $apiUrl        = null;
    private static $sandbox       = null;

    private static $defIsSandbox  = true;
    
    private static $sandboxUrl          = 'https://proxy.api.prebanco.com.br/v1/boleto/registrarBoleto';
    private static $productionUrl       = 'https://openapi.bradesco.com.br/v1/boleto/registrarBoleto';

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
            static::$apiUrl = static::isSandbox() ? static::$sandboxUrl : static::$productionUrl;
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