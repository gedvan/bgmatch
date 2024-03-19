<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SupportBggWeight extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('jogos', function (Blueprint $table) {
      $table->bigInteger('bgg_id')->nullable();
      $table->decimal('bgg_weight', total: 3, places: 2)->nullable();
    });

    Schema::table('partidas', function (Blueprint $table) {
      $table->bigInteger('id_expansao')->nullable();
      $table->boolean('ranking')->nullable()->default(TRUE);
    });

    DB::table('partidas')->update(['ranking' => TRUE]);
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('jogos', function (Blueprint $table) {
      $table->dropColumn(['bgg_id', 'bgg_weight']);
    });

    Schema::table('partidas', function (Blueprint $table) {
      $table->dropColumn(['id_expansao', 'ranking']);
    });
  }
}
