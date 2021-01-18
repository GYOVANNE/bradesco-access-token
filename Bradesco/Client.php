<?php
namespace Bradesco;

use Bradesco\Exceptions\BradescoParameterException;

class Client
{
    protected $fullUrl;
    protected $certKey;
    protected $privateKey;
    protected $token;

    public function __construct(array $config = [])
    {
        $this->setCertKeys();
        $this->setToken();
        // parent::__construct($config);
    }

    public function setToken() {
        $this->token = JwtApiService::create();
    }

    public function getToken() {
        return $this->token;
    }

    public function setCertKeys()
    {
        $certPassword = Bradesco::getCertPassword();
        $certPath     = Bradesco::getCertPath();
        if (!file_exists($certPath)) {
            throw new BradescoParameterException('Certificate file .pfx not found');
        }

        $certFile = file_get_contents($certPath);

        if (!openssl_pkcs12_read($certFile, $result, $certPassword)) {
            throw new BradescoParameterException('Unable to read certificate file .pfx. Please check the certificate password.');
        }

        $this->certKey    = openssl_x509_read($result['cert']);

        $this->privateKey = openssl_pkey_get_private($result['pkey'], $certPassword);
    }

    public function getFullUrl()
    {
        return $this->fullUrl;
    }

    public function getCertKey()
    {
        return $this->certKey;
    }

    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    public function getFolderPath()
    {
        return Bradesco::getFolderPath();
    }
}