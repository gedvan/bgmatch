<?php

use App\Http\Controllers\JogosController;
use App\Services\JogosService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JoinCategoryPartyInfantil extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::table('jogos')
        ->where('categoria', 'F')
        ->orWhere('categoria', 'I')
        ->update(['categoria' => JogosService::CATEGORIA_PARTY_INFANTIL]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      DB::table('jogos')
        ->where('categoria', JogosService::CATEGORIA_PARTY_INFANTIL)
        ->update(['categoria' => 'F']);
    }
}
