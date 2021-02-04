<?php
namespace Bradesco\BradescoAccessToken;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Bradesco\Exceptions\BradescoRequestException;

class AccessToken
{
    protected $client;
    protected $token;
    protected $privateKey;

    const CLIENT_ID                 = 'BRADESCO_CLIENT_ID';
    const BRADESCO_CERT_PATH_JWT    = 'BRADESCO_CERT_PATH_JWT';
    const BRADESCO_GRANT_TYPE       = 'urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Ajwt-bearer';

    public function __construct()
    {
        ini_set('default_socket_timeout', 120);
        $token = self::post();
        $this->setToken($token);
    }

    public function setToken($token) {
        $this->token = $token;
    }

    public function getToken() {
        return $this->token;
    }

    public function getClientKey() {
        $this->clientKey = getenv(static::CLIENT_ID);
        return $this->clientKey;
    }

    public function getPrivateKey() {
        $this->privateKey = getenv(static::BRADESCO_CERT_PATH_JWT);
        return $this->privateKey;
    }

    public function post()
    {

        $options = [
            'body' => $this->encryptBodyData()
        ];

        return $this->request('POST', $options);
    }

    private function request($method, array $options = [])
    {
        $body = \json_decode($options['body']);
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => BradescoAccessToken::getApiUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'assertion='.$body->assertion.'&grant_type='.self::BRADESCO_GRANT_TYPE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
        } catch (RequestException $e) {
            if (!$e->hasResponse()) {
                throw new BradescoRequestException($e->getMessage());
            }
            $response = $e->getResponse();
        }
        return json_decode($response);
    }

    public function encryptBodyData()
    {
        $date = new \DateTime();
        $dateCurrent = $date->getTimestamp();
        $dateCurrentAddMonth = $date->getTimestamp() +2592000;
        $milliseconds = (integer) round(microtime(true) * 100000000);
        // $milliseconds = (integer) round(microtime(true) * 1000);
        $clientId = $this->getClientKey();
        $privateKey   = $this->getPrivateKey();
        $header     =   \base64_encode(json_encode(["alg"=>"RS256","typ"=>"JWT"]));

        $jsonPayload = json_encode([
            "aud"=>BradescoAccessToken::getApiUrl(),
            "sub"=>$clientId,
            "iat"=>$dateCurrent,
            "exp"=>$dateCurrentAddMonth,
            "jti"=>$milliseconds,
            "ver"=>"1.1"
        ]);
        $payload =\base64_encode($jsonPayload);
        $output = shell_exec('echo -n "'.$header.'.'.$payload.'" | openssl dgst -sha256 -keyform pem -sign '.$privateKey." -binary  | openssl base64 -e -A | sed 's/\//_/g' | sed 's/\+/-/g' | sed -E s/=+$//");
        $headerPayloadSignature = $header.'.'.$payload.'.'.$output;

        return json_encode([
            'grant_type' =>'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'=>$headerPayloadSignature
        ], JSON_UNESCAPED_UNICODE);
    }
}
