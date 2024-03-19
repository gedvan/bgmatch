<?php

namespace App\Console\Commands;

use App\Services\JogosService;
use Illuminate\Console\Command;

class UpdateBggInfoCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'update-bgg-info';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Atualiza as informações do BGG (id e peso) para todos os jogos.';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct(
    protected JogosService $jogosService,
  )
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return void
   */
  public function handle()
  {
    $games = $this->jogosService->getLista();
    $success = [];
    $failure = [];
    $already = [];

    foreach ($games as $game) {
      $this->line('Processando ' . $game->nome . '...');

      if ($game->bgg_id && $game->bgg_weight) {
        $this->line('  Já possui as informações do BGG.');
        $already[] = $game->nome;
        continue;
      }

      $bgg_id = $this->jogosService->fetchBggId($game);

      if (!$bgg_id) {
        $this->error('  Não foi determinar o ID do jogo no BGG.');
        $failure[] = $game->nome;
        continue;
      }

      $bgg_weight = $this->jogosService->fetchBggWeight($bgg_id);
      if (!$bgg_weight) {
        $this->error("  Não foi obter o peso do jogo no BGG (ID $bgg_id).");
        $failure[] = $game->nome;
        continue;
      }

      $upd = $this->jogosService->update($game->id, [
        'bgg_id' => $bgg_id,
        'bgg_weight' => $bgg_weight,
      ]);

      if ($upd) {
        $this->info('  Jogo atualizado com sucesso.');
        $success[] = $game->nome;
      } else {
        $this->error('  Ocorreu um erro ao salvar os dados no BD.');
        $failure[] = $game->nome;
      }
    }

    $this->newLine();
    $this->line('Resultado:');

    $this->newLine();
    $this->line(sprintf('%d jogos NÃO foram atualizados porque já possuem as informações do BGG.', count($already)));

    $this->newLine();
    $this->info('Os jogos abaixo foram atualizados com as informações do BGG:');
    foreach ($success as $name) {
      $this->line(' - ' . $name);
    }

    $this->newLine();
    $this->warn('Os jogos abaixo NÃO foram atualizados com as informações do BGG devido a erros na obtenção dos dados:');
    foreach ($failure as $name) {
      $this->line(' - ' . $name);
    }
  }
}
