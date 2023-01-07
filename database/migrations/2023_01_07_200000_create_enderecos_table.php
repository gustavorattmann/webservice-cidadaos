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
        Schema::dropIfExists('enderecos');
        
        Schema::create('enderecos', function (Blueprint $table) {
            $table->id();
            $table->string('cep', 8);
            $table->string('endereco', 50);
            $table->string('numero', 5);
            $table->string('complemento', 100)
                ->nullable();
            $table->string('bairro', 50);
            $table->string('cidade', 50);
            $table->string('uf', 2);
            $table->foreignId('id_cidadao')
                ->constrained('cidadaos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enderecos');
    }
};
