<?php

namespace App\Http\Controllers;

use App\Http\Controllers\EnderecoController;
use App\Models\Cidadao;
use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CidadaoController extends Controller
{
    /**
     * Exibe uma lista com um cidadão específico ou todos.
     *
     * @return \Illuminate\Http\Response
     */
    public function consultar($id = null)
    {
        var_dump('Teste');
    }

    /**
     * Realiza o cadastro do cidadão.
     *
     * @return \Illuminate\Http\Response
     */
    public function cadastrar(Request $request)
    {
        $campos = array(
            'nome',
            'cpf',
            'cep',
            'numero',
            'sexo'
        );

        try {
            // Valida se campos existem e não estão nulos
            if ($request->has($campos) &&
                $request->filled($campos)) {
                // Faz um select na tabela cidadaos pelo nome preenchido no campo respectivos
                $cidadaoDB = Cidadao::where('nome', $request->input('nome'))
                                        ->first();
                
                if (empty($cidadaoDB)) {
                    $cep = preg_replace('/[^0-9]/', '', $request->input('cep'));

                    $enderecoApi = new EnderecoController;
                    $enderecoResultado = $enderecoApi->buscar($cep);

                    if ($enderecoResultado !== false) {
                        $cidadao = new Cidadao;

                        $cidadao->nome = $request->input('nome');
    
                        // Remove todos os caracteres que não forem números
                        $cpf = preg_replace('/[^0-9]/', '', $request->input('cpf'));
                        $cidadao->cpf = $cpf;
    
                        switch (strtolower($request->input('sexo'))) {
                            case 'm':
                                $cidadao->sexo = true;
    
                                break;
                            case 'f':
                                $cidadao->sexo = false;
    
                                break;
                            default:
                                return response([
                                    'mensagem' => "Sexo preenchido de forma inválida! Favor informar se é M (masculino) ou F (feminino)."
                                ], 400);
                        }

                        if ($cidadao->save()) {
                            $cidadaoDB = Cidadao::where('nome', $request->input('nome'))
                                                    ->first();

                            if (!empty($cidadaoDB)) {
                                $endereco = new Endereco;

                                $complemento = null;

                                if ($request->has('complemento') &&
                                    $request->filled('complemento')) {
                                    $complemento = $request->input('complemento');
                                }

                                $endereco->cep = $cep;
                                $endereco->endereco = $enderecoResultado['endereco'];
                                $endereco->numero = $request->input('numero');
                                $endereco->complemento = $complemento;
                                $endereco->bairro = $enderecoResultado['bairro'];
                                $endereco->cidade = $enderecoResultado['cidade'];
                                $endereco->uf = $enderecoResultado['uf'];
                                // $endereco->id_cidadao = $cidadaoDB->id;

                                // var_dump('Aqui');

                                try {
                                    if ($endereco->save()) {
                                        var_dump('Não aqui');
                                        return response([
                                            'mensagem' => 'Cadastro realizado com sucesso!'
                                        ], 201);
                                    }
                                } catch (Exception $error) {
                                    $cidadao->delete();
                                    Cidadao::truncate();
                        
                                    var_dump('Erro');
                        
                                    return response($error, 500);
                                }
                            }
                        } else {
                            $cidadao->delete();
                            Cidadao::truncate();
                        }
                    }

                    return response([
                        'mensagem' => 'Não foi possível realizar cadastro!'
                    ], 400);
                }

                return response([
                    'mensagem' => 'Cidadão já está cadastrado!'
                ], 400);
            }

            return response([
                'mensagem' => 'Faltando parâmetros!'
            ], 400);
        } catch (Exception $error) {
            $cidadao->delete();
            Cidadao::truncate();

            var_dump('Erro');

            return response($error, 500);
        }
    }

    /**
     * Altera o cidadão, se existir.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function alterar($id)
    {
        //
    }

    /**
     * Deleta o cidadão, se existir.
     *
     * @param  \App\Models\Cidadao  $cidadao
     * @return \Illuminate\Http\Response
     */
    public function deletar($id)
    {
        //
    }
}
