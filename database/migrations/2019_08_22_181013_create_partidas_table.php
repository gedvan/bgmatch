<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartidasTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('partidas', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->bigInteger('id_jogo')->unsigned();
      $table->date('data')->useCurrent();
      $table->string('local', 255);

      $table->foreign('id_jogo')->references('id_ludo')->on('jogos')
        ->onUpdate('cascade')->onDelete('restrict');
    });

    Schema::create('jogadores', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('nome', 255);
      $table->string('user_ludo', 255)->unique();
    });

    Schema::create('jogadores_partidas', function (Blueprint $table) {
      $table->bigInteger('id_partida')->unsigned();
      $table->bigInteger('id_jogador')->unsigned();
      $table->integer('pontuacao')->default(0);
      $table->boolean('vencedor')->default(FALSE);

      $table->foreign('id_partida')->references('id')->on('partidas')
        ->onUpdate('cascade')->onDelete('cascade');
      $table->foreign('id_jogador')->references('id')->on('jogadores')
        ->onUpdate('cascade')->onDelete('cascade');
      $table->primary(['id_partida', 'id_jogador']);
    });

    DB::table('jogadores')->insert([
      ['nome' => 'Gedvan',  'user_ludo' => 'gedvan'],
      ['nome' => 'Fechine', 'user_ludo' => 'fechine'],
      ['nome' => 'Bruno',   'user_ludo' => 'brunobrujah'],
      ['nome' => 'Rodrigo', 'user_ludo' => 'rodrigor'],
      ['nome' => 'Matheus', 'user_ludo' => 'matheuslaureano'],
    ]);
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('jogadores_partidas');
    Schema::dropIfExists('jogadores');
    Schema::dropIfExists('partidas');
  }
}
