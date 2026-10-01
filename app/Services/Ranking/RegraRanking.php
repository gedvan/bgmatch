<?php

namespace App\Services\Ranking;

/**
 * Regra de pontuação do ranking de uma época.
 *
 * As regras mudam de tempos em tempos. Cada época tem sua própria classe, e
 * uma mudança de regra entra como classe nova, sem alterar as anteriores, para
 * não reescrever rankings passados.
 */
interface RegraRanking
{
  /**
   * Calcula o ranking de um ano.
   *
   * @param int $ano
   * @param array $jogadores Lista de ['id', 'nome', 'cor'], em ordem alfabética.
   * @param array $partidas Partidas do ano que contam para o ranking, no formato
   *   de PartidasService::getPartidasPorPeriodo(), em ordem cronológica.
   * @param \DateTimeImmutable $hoje Data de referência (define os meses concluídos).
   *
   * @return array
   */
  public function calcula(int $ano, array $jogadores, array $partidas, \DateTimeImmutable $hoje): array;
}
