<?php

namespace App\Http\Controllers;

use App\Http\Controllers\EnderecoController;
use App\Models\Cidadao;
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
        try {
            if (empty($id)) {
                $cidadaos = Cidadao::all();

                
                if (count($cidadaos) > 0) {
                    foreach ($cidadaos as $cidadao) {
                        $resultado = $this->criarObjetoConsulta($cidadao);

                        return response($resultado, 200);
                    }
                }
                
                return response([
                    'mensagem' => 'Nenhum cidadão encontrado!'
                ], 404);
            }
            
            $cidadao = Cidadao::where('id', $id)->first();

            if (empty($cidadao)) {
                return response([
                    'mensagem' => 'Cidadão não encontrado!'
                ], 404);
            }

            $resultado = $this->criarObjetoConsulta($cidadao);

            return response($resultado, 200);
        } catch (Exception $error) {
            return response([
                'mensagem' => $error
            ], 500);
        }
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

        $quantidadeCampos = count($campos);

        for ($i = 0; $i < $quantidadeCampos; $i++) {
            // Verifica se não foi preenchido null como string no campo
            if (strtolower($request->input($campos[$i])) === 'null') {
                return response([
                    'mensagem' => "Campo {$campos[$i]} preenchido incorretamente!"
                ], 500);
            }
        }

        // Valida se campos existem
        if (!$request->missing($campos)) {
            // Verifica se estão preenchidos
            if ($request->filled($campos)) {
                // Faz um select na tabela cidadaos pelo nome preenchido no campo respectivos
                $cidadaoDB = Cidadao::where('nome', $request->input('nome'))->first();
                
                if (empty($cidadaoDB)) {
                    $cidadao = new Cidadao;

                    $cidadao->nome = $request->input('nome');

                    // Remove todos os caracteres que não forem números
                    $cpf = preg_replace('/[^0-9]/', '', $request->input('cpf'));

                    if (strlen($cpf) <> 11) {
                        return response([
                            'mensagem' => 'CPF inválido!'
                        ], 500);
                    }

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

                    DB::beginTransaction();

                    if ($cidadao->save()) {
                        $endereco = new EnderecoController;

                        $cep = preg_replace('/[^0-9]/', '', $request->input('cep'));

                        $complemento = null;

                        if ($request->has('complemento') &&
                            $request->filled('complemento')) {
                            $complemento = $request->input('complemento');
                        }

                        $parametros = array(
                            "cidadao" => $cidadao,
                            "cep" => $cep,
                            "numero" => $request->input('numero'),
                            "complemento" => $complemento
                        );

                        $retornoEndereco = $endereco->cadastrar($parametros);

                        if ($retornoEndereco !== true && $retornoEndereco !== false) {
                            return response([
                                'mensagem' => $retornoEndereco
                            ], 404);
                        } else if ($retornoEndereco === true) {
                            DB::commit();

                            return response([
                                'mensagem' => 'Cadastro realizado com sucesso!'
                            ], 201);
                        } 
                    }
                    
                    DB::rollBack();

                    return response([
                        'mensagem' => 'Não foi possível realizar cadastro!'
                    ], 400);
                }

                return response([
                    'mensagem' => 'Cidadão já está cadastrado!'
                ], 400);
            }

            return response([
                'mensagem' => 'Campo(s) vazio(s)!'
            ], 500);
        }

        return response([
            'mensagem' => 'Faltando parâmetros!'
        ], 500);
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
        $cidadao = Cidadao::find($id);

        if (!empty($cidadao)) {
            try {
                Cidadao::destroy($id);

                return response([
                    'mensagem' => 'Cidadão deletado com sucesso!'
                ], 200);
            } catch (\Exception $error) {
                return response([
                    'mensagem' => $error
                ], 500);
            }
        }

        return response([
            'mensagem' => 'Cidadão não encontrado!'
        ], 404);
    }

    public function criarObjetoConsulta($cidadao)
    {
        $cpf = $this->criarMascara($cidadao->cpf, '###.###.###-##');
        $cep = $this->criarMascara($cidadao->endereco->cep, '#####-###');
        $complemento = $cidadao->endereco->complemento;
        $sexo = "Masculino";

        if ($complemento === null) $complemento = "";
        if (!$cidadao->sexo) $sexo = "Feminino";

        $resultado = array(
            "id" => $cidadao->id,
            "nome" => $cidadao->nome,
            "cpf" => $cpf,
            "cep" => $cep,
            "endereco" => $cidadao->endereco->endereco,
            "numero" => $cidadao->endereco->numero,
            "complemento" => $complemento,
            "bairro" => $cidadao->endereco->bairro,
            "cidade" => $cidadao->endereco->cidade,
            "uf" => $cidadao->endereco->uf,
            "sexo" => $sexo
        );

        return $resultado;
    }

    public function criarMascara($valor, $digitos)
    {
        $mascara = '';

        $indiceValor = 0;

        $quantidadeDigitos = strlen($digitos);

        for ($i = 0; $i <= $quantidadeDigitos - 1; ++$i) {
            if ($digitos[$i] === '#') {
                if (isset($valor[$indiceValor])) {
                    $mascara .= $valor[$indiceValor++];
                }
            } else {
                if (isset($digitos[$i])) {
                    $mascara .= $digitos[$i];
                }
            }
        }

        return $mascara;
    }
}
