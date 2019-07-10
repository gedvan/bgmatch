<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnJogoSlug extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jogos', function (Blueprint $table) {
            $table->string('slug')->nullable();
        });

        DB::table('jogos')->update(['slug' => DB::raw("split_part(link_ludo, '/', 5)")]);

        Schema::table('jogos', function (Blueprint $table) {
            $table->string('slug')->nullable(FALSE)->change();
            $table->unique('slug');
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
            $table->dropUnique('jogos_slug_unique');
            $table->dropColumn('slug');
        });
    }
}
