# Bradesco Access Token
#### Manual v2.0 2020

### Bradesco integration to obtain Access Token for online registration of bill of pay

## How to use

- Change the .ENV parameters with your access data

```
BRADESCO_SANDBOX=true
BRADESCO_SANDBOX_JWT=true
BRADESCO_CLIENT_ID="xxx..."
BRADESCO_CERT_PATH_JWT=/<path>/certificate.key.pem
BRADESCO_FOLDER_PATH=/<path>/storage/app/public/Bradesco/
```

## Implementation

```php
use Bradesco\Bradesco;
use Bradesco\BoletoApiService;
use Bradesco\Exceptions\BradescoApiException;
use Bradesco\Exceptions\BradescoRequestException;
use DateTime;
use App;

class BoletoBradescoService
{
    public function registrar($boleto)
    {
        $dataVencimento = new DateTime($boleto->data_vencimento);
        $dataEmissao = new DateTime($boleto->data_emissao);

        $data = [
            // "nuCPFCNPJ" => $boleto->beneficiario_documento,
            "nuCPFCNPJ" => 'xxxxxxxx/xxxx-xx',
            // "filialCPFCNPJ" => "xxxx",
            "filialCPFCNPJ" => "xxxx",
            // "ctrlCPFCNPJ" => "xx",
            "ctrlCPFCNPJ" => "xx",
            "idProduto" => "xx",
            "nuNegociacao" => "xxxxxxxxxxxxxxxxxx",
            "nuCliente" => $boleto->id,
            "dtEmissaoTitulo" => $dataEmissao,
            "dtVencimentoTitulo" => $dataVencimento,
            "vlNominalTitulo" => (float) $boleto->valor_nominal,
            "cdEspecieTitulo" => "04",
            "nomePagador" => $boleto->pagador_nome,
            "logradouroPagador" => $boleto->pagador_endereco,
            "nuLogradouroPagador" => "0",
            "complementoLogradouroPagador" => null,
            "cepPagador" => substr($boleto->pagador_cep, 0, 5) ,
            "complementoCepPagador" => substr($boleto->pagador_cep, -3),
            "bairroPagador" => $boleto->pagador_bairro,
            "municipioPagador" => $boleto->pagador_cidade,
            "ufPagador" => $boleto->pagador_uf,
            "cdIndCpfcnpjPagador" => "1",
            "nuCpfcnpjPagador" => $boleto->pagador_documento
          ];

        try {
            $boleto = BoletoApiService::create($data);
            print_r($boleto);
        } catch (BradescoApiException $e) { // errors returned by API Bradesco
            echo sprintf("%s (%s)", $e->getMessage(), $e->getErrorCode());
        } catch (BradescoRequestException $e) { // server errors (errors HTTP 4xx e 5xx)
            echo sprintf("%s (%s)", $e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) { // other errors
            echo $e->getMessage();
        }
        return $boleto;
    }
}
```

#### Required Laravel >= 5.6/ PHP >= 5.4
