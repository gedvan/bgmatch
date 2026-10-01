<?php

namespace App\Services\Ranking;

/**
 * Regra de 2024 em diante.
 *
 * Cada partida dá pontos conforme o peso do jogo no BGG (o da expansão, se
 * houver) e a posição do jogador. Ao fim de cada mês, os três jogadores com
 * mais pontos no mês ganham 3, 2 e 1 pontos de vitória (estrelas). Vence o ano
 * quem somar mais estrelas; o desempate é pelo número de meses vencidos.
 */
class Ranking2024 implements RegraRanking
{
  /**
   * Multiplicador de cada posição (1º ao 6º lugar).
   */
  const FATORES = [10, 7, 4, 1, 1, 1];

  /**
   * Pontos de vitória do 1º, 2º e 3º lugar de cada mês.
   */
  const PONTOS_VITORIA = [3, 2, 1];

  const MESES = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

  public function nome(): string
  {
    return '2024';
  }

  /**
   * Peso da partida: o da expansão, se ela tiver peso, senão o do jogo.
   */
  public function peso(array $partida): float
  {
    $expansao = $partida['expansao'] ?? null;
    $peso = $expansao && self::preenchido($expansao['peso'] ?? null)
      ? $expansao['peso']
      : ($partida['jogo']['peso'] ?? 0);
    return (float) $peso;
  }

  /**
   * Pontos de um jogador numa partida: peso normalizado (0 a 1) vezes o fator
   * da posição. Posição fora de 1 a 6 ou jogo sem peso vale zero.
   */
  public function pontos(float $peso, $posicao): float
  {
    $pos = (int) $posicao;
    if ($pos < 1 || $pos > count(self::FATORES) || !$peso) {
      return 0;
    }
    return $peso / 5 * self::FATORES[$pos - 1];
  }

  public function calcula(int $ano, array $jogadores, array $partidas, \DateTimeImmutable $hoje): array
  {
    $anoAtual = (int) $hoje->format('Y');
    $mesAtual = (int) $hoje->format('n') - 1;

    $meses = [];
    foreach (self::MESES as $i => $nome) {
      $meses[$i] = [
        'numero' => $i + 1,
        'nome' => $nome,
        'concluido' => $anoAtual > $ano || ($anoAtual === $ano && $mesAtual > $i),
        'partidas' => [],
      ];
    }

    // Mês exibido inicialmente: o da partida mais recente.
    $mesCorrente = 0;

    foreach ($partidas as $partida) {
      $peso = $this->peso($partida);
      $mes = (int) substr($partida['data'], 5, 2) - 1;
      $meses[$mes]['partidas'][] = [
        'id' => $partida['id'],
        'data' => $partida['data'],
        'jogo' => ['id' => $partida['jogo']['id'], 'nome' => $partida['jogo']['nome']],
        'expansao' => $partida['expansao']
          ? ['id' => $partida['expansao']['id'], 'nome' => $partida['expansao']['nome']]
          : null,
        'peso' => $peso,
        'jogadores' => array_map(fn($jp) => [
          'id' => $jp['id'],
          'nome' => $jp['nome'],
          'posicao' => $jp['posicao'],
          'pontos' => $this->pontos($peso, $jp['posicao']),
        ], $partida['jogadores']),
      ];
      $mesCorrente = $mes;
    }

    $anual = [];
    foreach ($jogadores as $jogador) {
      $anual[$jogador['id']] = [
        'id' => $jogador['id'],
        'nome' => $jogador['nome'],
        'cor' => $jogador['cor'],
        'pontos_total' => 0,
        'pontos_meses' => array_fill(0, 12, 0),
      ];
    }

    foreach ($meses as $i => &$mes) {
      $mes['classificacao'] = $this->classificacaoDoMes($mes['partidas'], $jogadores);
      if ($mes['concluido']) {
        foreach ($this->podioDoMes($mes['partidas']) as $posicao => $idJogador) {
          if (isset($anual[$idJogador])) {
            $anual[$idJogador]['pontos_total'] += self::PONTOS_VITORIA[$posicao];
            $anual[$idJogador]['pontos_meses'][$i] = self::PONTOS_VITORIA[$posicao];
          }
        }
      }
    }
    unset($mes);

    $anual = array_values($anual);
    usort($anual, function ($a, $b) {
      if ($a['pontos_total'] !== $b['pontos_total']) {
        return $b['pontos_total'] <=> $a['pontos_total'];
      }
      return self::mesesVencidos($b) <=> self::mesesVencidos($a);
    });

    return [
      'regra' => $this->nome(),
      'fatores' => self::FATORES,
      'pontos_vitoria' => self::PONTOS_VITORIA,
      'mes_atual' => $mesCorrente,
      'meses' => $meses,
      'jogadores' => $anual,
    ];
  }

  /**
   * Pontos de todos os jogadores no mês, do maior para o menor. Quem não jogou
   * aparece com zero. Empates mantêm a ordem alfabética.
   */
  protected function classificacaoDoMes(array $partidas, array $jogadores): array
  {
    $pontos = [];
    foreach ($jogadores as $jogador) {
      $pontos[$jogador['id']] = [
        'id' => $jogador['id'],
        'nome' => $jogador['nome'],
        'cor' => $jogador['cor'],
        'pontos' => 0,
      ];
    }
    foreach ($partidas as $partida) {
      foreach ($partida['jogadores'] as $jp) {
        if (isset($pontos[$jp['id']])) {
          $pontos[$jp['id']]['pontos'] += $jp['pontos'];
        }
      }
    }
    $lista = array_values($pontos);
    usort($lista, fn($a, $b) => $b['pontos'] <=> $a['pontos']);
    return $lista;
  }

  /**
   * Ids dos três primeiros do mês, entre os jogadores que jogaram nele. Empates
   * mantêm a ordem em que cada jogador apareceu pela primeira vez no mês.
   */
  protected function podioDoMes(array $partidas): array
  {
    $pontos = [];
    foreach ($partidas as $partida) {
      foreach ($partida['jogadores'] as $jp) {
        $pontos[$jp['id']] = ($pontos[$jp['id']] ?? 0) + $jp['pontos'];
      }
    }
    $lista = [];
    foreach ($pontos as $id => $total) {
      $lista[] = ['id' => $id, 'pontos' => $total];
    }
    usort($lista, fn($a, $b) => $b['pontos'] <=> $a['pontos']);
    return array_column(array_slice($lista, 0, count(self::PONTOS_VITORIA)), 'id');
  }

  protected static function mesesVencidos(array $jogador): int
  {
    return count(array_filter($jogador['pontos_meses'], fn($pv) => $pv === self::PONTOS_VITORIA[0]));
  }

  /**
   * Replica a checagem de "valor preenchido" usada antes no frontend: só
   * null, string vazia e zero numérico contam como vazio.
   */
  protected static function preenchido($valor): bool
  {
    return !($valor === null || $valor === '' || $valor === 0 || $valor === 0.0 || $valor === false);
  }
}
