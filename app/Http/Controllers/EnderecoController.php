<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BrasilApi;

class EnderecoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
}
