<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DropColumnJogosLinkLudo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jogos', function (Blueprint $table) {
            $table->dropColumn('link_ludo');
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
            $table->string('link_ludo')->nullable();
        });

        DB::table('jogos')->update(['link_ludo' => DB::raw("concat('https://www.ludopedia.com.br/jogo/', slug)")]);

        Schema::table('jogos', function (Blueprint $table) {
            $table->string('link_ludo')->nullable(FALSE)->change();
        });
    }
}
