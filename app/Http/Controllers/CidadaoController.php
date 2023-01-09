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
     * @param  $id - informar o id do cidadão (opcional)
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
        } catch (\Exception $error) {
            return response([
                'mensagem' => $error
            ], 500);
        }
    }

    /**
     * Realiza o cadastro do cidadão.
     * 
     * @param Illuminate\Http\Request $request
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
                    $cpf = $this->removerMascara($request->input('cpf'));

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

                        $cep = $this->removerMascara($request->input('cep'));

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
     * @param  $id - informar o id do cidadão
     * @return \Illuminate\Http\Response
     */
    public function alterar($id, Request $request)
    {
        $cidadao = Cidadao::find($id);

        if (!empty($cidadao)) {
            try {
                $campos = array(
                    'nome',
                    'cpf',
                    'cep',
                    'numero',
                    'sexo'
                );

                if ($request->hasAny($campos)) {
                    $quantidadeCampos = count($campos);
        
                    for ($i = 0; $i < $quantidadeCampos; $i++) {
                        // Verifica se não foi preenchido null como string no campo
                        if (strtolower($request->input($campos[$i])) === 'null') {
                            return response([
                                'mensagem' => "Campo {$campos[$i]} preenchido incorretamente!"
                            ], 500);
                        }
                    }

                    if (!$request->missing('nome')) $cidadao->nome = $request->input('nome');

                    if (!$request->missing('cpf')) {
                        // Remove todos os caracteres que não forem números
                        $cpf = $this->removerMascara($request->input('cpf'));

                        if (strlen($cpf) <> 11) {
                            return response([
                                'mensagem' => 'CPF inválido!'
                            ], 500);
                        }

                    }
                    
                    if (!$request->missing('sexo')) {
                        switch (strtolower($request->input('sexo'))) {
                            case 'm':
                                $cidadao->cpf = true;
    
                                break;
                            case 'f':
                                $cidadao->sexo = false;
    
                                break;
                            default:
                                return response([
                                    'mensagem' => "Sexo preenchido de forma inválida! Favor informar se é M (masculino) ou F (feminino)."
                                ], 400);
                        }
                    }
                    
                    DB::beginTransaction();

                    if ($cidadao->save()) {
                        $endereco = new EnderecoController;

                        // Se número ou complemento forem preenchidos e cep estiver vazio,
                        // então é obtido o cep atual do cidadão na variável.
                        // Se cep estiver preenchido, então passa o valor pra variável.
                        // Caso número, complemento e cep não forem preenchidos, então a variável será false
                        if ((!$request->missing('numero') || !$request->missing('complemento')) &&
                            $request->missing('cep')) {
                            $cep = $this->removerMascara($cidadao->endereco->cep);
                        } else if (!$request->missing('cep')) {
                            if (!$request->missing('cep')) $cep = $this->removerMascara($request->input('cep'));
                        } else {
                            $cep = false;
                        }

                        $numero = $cidadao->endereco->numero;
                        $complemento = $cidadao->endereco->complemento;

                        if (!$request->missing('numero')) $numero = $request->input('numero');
                        if (!$request->missing('complemento')) $complemento = $request->input('complemento');

                        $parametros = array(
                            "cidadao" => $cidadao,
                            "cep" => $cep,
                            "numero" => $numero,
                            "complemento" => $complemento
                        );

                        $retornoEndereco = $endereco->alterar($parametros);

                        if ($retornoEndereco !== true && $retornoEndereco !== false) {
                            return response([
                                'mensagem' => $retornoEndereco
                            ], 404);
                        } else if ($retornoEndereco === true) {
                            DB::commit();

                            return response([
                                'mensagem' => 'Cidadão alterado com sucesso!'
                            ], 201);
                        }
                    }
                    
                    DB::rollBack();

                    return response([
                        'mensagem' => 'Não foi possível alterar cidadão!'
                    ], 400);
                }

                return response([
                    'mensagem' => 'Necessário preencher pelo menos um campo para realizar a alteração!'
                ], 500);
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

    /**
     * Deleta o cidadão, se existir.
     *
     * @param  $id - informar o id do cidadão
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

    public function removerMascara($valor)
    {
        return preg_replace('/[^0-9]/', '', $valor);
    }
}
