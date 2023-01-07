<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/cidadao/{id?}', [Cidadao::class, 'consultar'])->where('id', '[0-9]+');
Route::post('/cidadao/cadastro', [Cidadao::class, 'cadastrar']);
Route::post('/cidadao/alterar/{id}', [Cidadao::class, 'alterar'])->where('id', '[0-9]+');
Route::post('/cidadao/deletar/{id}', [Cidadao::class, 'deletar'])->where('id', '[0-9]+');