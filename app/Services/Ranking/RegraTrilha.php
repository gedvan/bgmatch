<?php

namespace App\Services\Ranking;

/**
 * Base das regras de 2019 a 2023: cada partida dá pontos conforme a posição do
 * jogador e a categoria do jogo, e os jogadores avançam numa trilha de 0 a 99.
 */
abstract class RegraTrilha implements RegraRanking
{
  const NOMES_CATEGORIAS = [
    'P' => 'Pesado',
    'M' => 'Médio',
    'L' => 'Leve',
    'Y' => 'Party/Infantil',
  ];

  /**
   * Identificador da regra, devolvido na resposta.
   */
  abstract public function nome(): string;

  /**
   * Tabela de pontuação no formato [posição => [categoria => pontos]].
   */
  abstract protected function tabela(): array;

  /**
   * Pontos de uma posição numa categoria de jogo. Posição ou categoria fora da
   * tabela vale zero.
   */
  public function pontos($posicao, ?string $categoria): int
  {
    // Party e infantil foram unificadas na categoria Y.
    if ($categoria === 'F' || $categoria === 'I') {
      $categoria = 'Y';
    }
    return $this->tabela()[(int) $posicao][$categoria] ?? 0;
  }

  public function calcula(int $ano, array $jogadores, array $partidas, \DateTimeImmutable $hoje): array
  {
    $pontuacao = [];
    foreach ($jogadores as $jogador) {
      $pontuacao[$jogador['id']] = [
        'id' => $jogador['id'],
        'nome' => $jogador['nome'],
        'cor' => $jogador['cor'],
        'total' => 0,
        'mensal' => array_fill(0, 12, 0),
        // Pontos feitos em cada dia de partida, no formato "MM-DD".
        'semanal' => ['01-01' => 0],
      ];
    }

    // Dias com partida, em ordem, para o eixo do gráfico.
    $datas = ['01-01'];

    foreach ($partidas as $partida) {
      $data = substr($partida['data'], 5, 5);
      $mes = (int) substr($partida['data'], 5, 2) - 1;
      if (!in_array($data, $datas, true)) {
        $datas[] = $data;
      }

      foreach ($partida['jogadores'] as $jogadorPartida) {
        if (!isset($pontuacao[$jogadorPartida['id']])) {
          continue;
        }
        $pontos = $this->pontos($jogadorPartida['posicao'], $partida['jogo']['categoria']);
        $jogador = &$pontuacao[$jogadorPartida['id']];
        $jogador['total'] += $pontos;
        $jogador['mensal'][$mes] += $pontos;
        $jogador['semanal'][$data] = ($jogador['semanal'][$data] ?? 0) + $pontos;
        unset($jogador);
      }
    }

    // Só entra quem pontuou. A ordenação é estável: empates mantêm a ordem
    // alfabética.
    $classificacao = array_values(array_filter($pontuacao, fn($j) => $j['total'] > 0));
    usort($classificacao, fn($a, $b) => $b['total'] <=> $a['total']);

    return [
      'regra' => $this->nome(),
      'tabela' => $this->descreveTabela(),
      'datas' => $datas,
      'jogadores' => $classificacao,
    ];
  }

  /**
   * Tabela de pontuação no formato exibido na tela.
   */
  protected function descreveTabela(): array
  {
    $tabela = $this->tabela();
    $chaves = array_keys(reset($tabela));

    $categorias = array_map(fn($key) => [
      'key' => $key,
      'label' => self::NOMES_CATEGORIAS[$key],
    ], $chaves);

    $posicoes = [];
    foreach ($tabela as $posicao => $pontos) {
      $posicoes[] = ['posicao' => $posicao, 'pontos' => $pontos];
    }

    return ['categorias' => $categorias, 'posicoes' => $posicoes];
  }
}
