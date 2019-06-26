<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CriarTabelaJogos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jogos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_ludo')->primary();
            $table->string('nome');
            $table->char('tipo', 1);
            $table->unsignedTinyInteger('min')->nullable();
            $table->unsignedTinyInteger('max')->nullable();
            $table->string('link_ludo');
            $table->string('img_ludo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jogos');
    }
}
