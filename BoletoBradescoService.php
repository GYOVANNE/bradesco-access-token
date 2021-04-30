<?php

use Bradesco\BoletoApiService;
use Bradesco\Exceptions\BradescoApiException;
use Bradesco\Exceptions\BradescoRequestException;

use DateTime;
use DateInterval;
use App;

class BoletoBradescoService
{

    private function concatNuNegociacao($agc, $cta) {
        $cta = str_pad($cta, 7, '0', STR_PAD_LEFT);
        return $agc.'0000000'.$cta;
    }

    public function registrar($boleto)
    {
        $dataVencimento = new DateTime($boleto->data_vencimento);
        $dataEmissao = new DateTime($boleto->data_emissao);
        $dataLimitePagamento = new DateTime($boleto->data_emissao);
        $dataLimitePagamento->add(new DateInterval('P5D')); // + 5 dias

        $data = [
            "nuCPFCNPJ" => 'xxxxxxxx/xxxx-xx',
            // "filialCPFCNPJ" => "xxxx",
            "filialCPFCNPJ" => "xxxx",
            // "ctrlCPFCNPJ" => "xx",
            "ctrlCPFCNPJ" => "xx",
            "idProduto" => "xx",
            "nuNegociacao" => "xxxxxxxxxxxxxxxxxx",
            "idProduto" => "09",
            "nuNegociacao" => $this->concatNuNegociacao($boleto->agencia, $boleto->conta),//Composição 4 dig da agencia, 7 posi 0, a conta em 7 posi sem o digito
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
            "cdIndCpfcnpjPagador" => (\strlen($boleto->pagador_documento) > 14)?'2':'1',
            "nuCpfcnpjPagador" => $boleto->pagador_documento,
            "nuTitulo" => $boleto->nosso_numero_seq,
            // "nuTitulo" => "0"
            "vlDesconto1" => $boleto->valor_desconto?:"0",
            "dataLimiteDesconto1" => (intval($boleto->valor_desconto) > 0)?$dataVencimento:"",
            "dtLimitePagamentoBoleto" => $dataLimitePagamento
          ];

        try {
            $boleto = BoletoApiService::create($data);
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