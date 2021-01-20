<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CorJogador extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('jogadores', function (Blueprint $table) {
        $table->string('cor', 20)->nullable();
      });

      DB::table('jogadores')->update(['cor' => '#36A2EB']);

      Schema::table('jogadores', function (Blueprint $table) {
        $table->string('cor')->nullable(FALSE)->change();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('jogos', function (Blueprint $table) {
        $table->dropColumn('cor');
      });
    }
}
