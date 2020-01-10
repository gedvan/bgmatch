<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorJogos extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('jogos', function (Blueprint $table) {
      $table->boolean('coop')->default(false);
      $table->renameColumn('id_ludo', 'id');
      $table->renameColumn('img_ludo', 'imagem');
      $table->renameColumn('tipo', 'categoria');
    });

    DB::table('jogos')->where('categoria', '<>', 'C')->update(['coop' => false]);
    DB::table('jogos')->where('categoria', 'C')->update(['coop' => true, 'categoria' => 'M']);
    DB::table('jogos')->where('categoria', 'P')->update(['categoria' => 'F']);
    DB::table('jogos')->where('categoria', 'E')->update(['categoria' => 'P']);
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    DB::table('jogos')->where('categoria', 'P')->update(['categoria' => 'E']);
    DB::table('jogos')->where('categoria', 'F')->update(['categoria' => 'P']);
    DB::table('jogos')->where('categoria', 'L')->update(['categoria' => 'M']);
    DB::table('jogos')->where('coop', TRUE)->update(['categoria' => 'C']);

    Schema::table('jogos', function (Blueprint $table) {
      $table->renameColumn('categoria', 'tipo');
      $table->renameColumn('imagem', 'img_ludo');
      $table->renameColumn('id', 'id_ludo');
      $table->dropColumn('coop');
    });
  }
}
