<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorPartidas extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::table('jogadores_partidas', function (Blueprint $table) {
      $table->unsignedTinyInteger('posicao')->nullable(true);
      $table->dropColumn('vencedor');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::table('jogadores_partidas', function (Blueprint $table) {
      $table->boolean('vencedor')->default(FALSE);
      $table->dropColumn('posicao');
    });
  }
}
