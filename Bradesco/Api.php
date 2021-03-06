<?php
namespace Bradesco;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Bradesco\Exceptions\BradescoRequestException;
use Bradesco\BradescoAccessToken\AccessToken;

class Api
{
    protected $client;
    protected $accessToken;
    protected $nonce;
    protected $timestamp;

    const BRADESCO_REQUEST_PATH         = 'BRADESCO_FOLDER_PATH';
    const BRADESCO_CERT_PATH_JWT        = 'BRADESCO_CERT_PATH_JWT';
    const SIGNATURE_URL                 = '/v1/boleto/registrarBoleto';
    const ACCOUNT_AGENCY                = '';
    const SIGNATURE_ALG                 = 'SHA256';

    public function __construct()
    {
        ini_set('default_socket_timeout', 120);
        $this->accessToken = new AccessToken();
    }

    public function post(array $params = [])
    {
        $body = $this->encryptBodyData($params);
        $options = [
            'body' => $body,
            'signature' => $this->encryptSignatureData($body),
            'access_token' => $this->accessToken->getToken()->access_token
        ];

        return $this->request('POST', $options);
    }

    private function request( $method, $options = [])
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => Bradesco::getApiUrl(),
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>$options['body'],
              CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$options['access_token'],
                'X-Brad-Nonce: '.$this->getNonce(),
                'X-Brad-Timestamp: '.$this->getTimestamp(),
                'X-Brad-Algorithm: '.self::SIGNATURE_ALG,
                'X-Brad-Signature: '.$options['signature'],
                'Content-Type: application/json'
              ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);

            $resposta = json_decode($response);
            if(isset($resposta->codigo) && (int) $resposta->codigo){
                abort(409,'Erro na geração do boleto');
            }
            // $response = $this->client->request($method, $endpoint, $options);
        } catch (RequestException $e) {
            \Log::info("Erro na requisição do registro do boleto", [$e]);
            abort(409,'Erro na geração do boleto');
            if (!$e->hasResponse()) {
                throw new BradescoRequestException($e->getMessage());
            }
            $response = $e->getResponse();
        }

        return $response;
    }

    private function encryptSignatureData($body)
    {
        $post = 'POST';
        $url = self::SIGNATURE_URL;
        $params = self::ACCOUNT_AGENCY;
        $access_token = $this->accessToken->getToken()->access_token;
        $nonce = (integer) round(microtime(true) * 100000000);
        // $nonce = (integer) round(microtime(true) * 1000);
        $this->setNonce($nonce);
        $date = new \DateTime();
        $timestamp = $date->format('Y-m-d\TH:i:s').'-03:00';
        $this->setTimestamp($timestamp);
        $alg = self::SIGNATURE_ALG;
        $privateKey = getenv(static::BRADESCO_CERT_PATH_JWT);
        $char = PHP_EOL;
        $result = $post.$char.$url.$char.$params.$char.$body.$char.$access_token.$char.$nonce.$char.$timestamp.$char.$alg;
        $path = getenv(static::BRADESCO_REQUEST_PATH).'request_'.$nonce.'.txt';
        file_put_contents($path, $result);
        // $output = shell_exec('echo -n "'.$result.'" | openssl dgst -sha256 -keyform pem -sign '.$privateKey.' -binary  | openssl base64 -e -A '."| sed 's/\//_/g' | sed 's/\+/-/g' | sed -E s/=+$//");
        $output = shell_exec('echo -n "$(cat '.$path.')" | openssl dgst -sha256 -keyform pem -sign '.$privateKey.' -binary  | openssl base64 -e -A '."| sed 's/\//_/g' | sed 's/\+/-/g' | sed -E s/=+$//");
        unlink($path);
        return $output;
    }

    public function encryptBodyData($params)
    {
        $message = json_encode($params, JSON_UNESCAPED_UNICODE);
        return $message;
    }

    private function setNonce($nonce)
    {
        $this->nonce = $nonce;
    }

    private function getNonce()
    {
        return $this->nonce;
    }

    private function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    private function getTimestamp()
    {
        return $this->timestamp;
    }

}
