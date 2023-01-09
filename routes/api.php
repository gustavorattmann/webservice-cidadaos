<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CidadaoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/cidadao/{id?}', [CidadaoController::class, 'consultar'])->where('id', '[0-9]+');
Route::post('/cidadao/cadastro', [CidadaoController::class, 'cadastrar']);
Route::patch('/cidadao/alterar/{id}', [CidadaoController::class, 'alterar'])->where('id', '[0-9]+');
Route::delete('/cidadao/deletar/{id}', [CidadaoController::class, 'deletar'])->where('id', '[0-9]+');
Route::any('{url}', function(){
    return response([
        'mensagem' => 'Rota não encontrada!'
    ], 404);
})->where('url', '.*');