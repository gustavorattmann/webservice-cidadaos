<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('cidadaos');
        
        Schema::create('cidadaos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')
                ->unique();
            $table->string('cpf', 11)
                ->unique();
            $table->boolean('sexo')
                ->default(true);
            $table->timestamps();
            $table->index(['cpf']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cidadaos');
    }
};