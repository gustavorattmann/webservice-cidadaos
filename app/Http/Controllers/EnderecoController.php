<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use BrasilApi;

class EnderecoController extends Controller
{
    /**
     * Função para buscar um CEP e retornar seus dados de endereço.
     *
     * @return resultado - retorna o resultado da busca da API
     * @return false
     */
    public function buscar($cep = null)
    {
        if ($cep !== null) {
            $endereco = BrasilApi::cep($cep);

            $resultado = array(
                "endereco" => $endereco['street'],
                "bairro" => $endereco['neighborhood'],
                "cidade" => $endereco['city'],
                "uf" => $endereco['state'],
            );

            return $resultado;
        }
        
        return false;
    }

    /**
     * Função para cadastrar um endereço e vinculá-lo ao cidadão cadastrado.
     *
     * @return exceção - retorna um texto de exceção
     * @return true
     * @return false
     */
    public function cadastrar($parametros)
    {
        $cidadao = $parametros['cidadao'];
        $cep = $parametros['cep'];
        $numero = $parametros['numero'];
        $complemento = $parametros['complemento'];

        try {
            $enderecoApi = $this->buscar($cep);

            if ($enderecoApi !== false) {
                $endereco = new Endereco;
    
                $endereco->cep = $cep;
                $endereco->endereco = $enderecoApi['endereco'];
                $endereco->numero = $numero;
                $endereco->complemento = $complemento;
                $endereco->bairro = $enderecoApi['bairro'];
                $endereco->cidade = $enderecoApi['cidade'];
                $endereco->uf = $enderecoApi['uf'];
                $endereco->cidadao()->associate($cidadao);
    
                DB::beginTransaction();
    
                if ($endereco->save()) {
                    DB::commit();
    
                    return true;
                }
    
                DB::rollBack();
    
                return false;
            }
        } catch (\Exception $error) {
            return 'Endereço incorreto!';
        }
    }
}
