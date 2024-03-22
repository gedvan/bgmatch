<script>
import BGMatch from "../BGMatch";
import Meeple from "./Meeple.vue";

export default {
  components: {
    Meeple
  },

  props: {
    ano: Number
  },

  data() {
    return {
      /**
       * Índice do mês visualizado atualmente. 0 = Janeiro, 11 = Dezembro.
       */
      mes: 0,

      /**
       * Lista de meses contendo seu nome, partidas e outras informações.
       */
      meses: [],

      /**
       * Lista de jogadores (e sua pontuação) exibidos conforme o mês atual.
       */
      jogadoresMes: [],

      /**
       * Lista de jogadores e sua pontuação no ano.
       */
      jogadoresAno: [],
    }
  },

  mounted() {
    this.inicializaMeses();
    this.fetchDados();
  },

  computed: {
    categorias() {
      return BGMatch.categoriasJogos.filter(c => c.key !== 'Y');
    },

    partidasMes() {
      return this.meses[this.mes]?.partidas ?? [];
    },

    jogadoresMesSorted() {
      return this.jogadoresMes.toSorted((a, b) => b.pontos - a.pontos);
    },

    jogadoresAnoSorted() {
      return this.jogadoresAno.toSorted((a, b) => {
        if (b.pontosTotal !== a.pontosTotal) {
          return b.pontosTotal - a.pontosTotal
        }
        const vitoriasA = a.pontosMeses.filter(m => m === 3).length;
        const vitoriasB = b.pontosMeses.filter(m => m === 3).length;
        return vitoriasB - vitoriasA;
      });
    },
  },

  methods: {
    /**
     * Inicializa a lista de meses.
     *
     * Cada mês, além do nome, deve conter a lista de partidas daquele período,
     * além de uma flag indicando se o mesmo já está concluído ou não.
     */
    inicializaMeses() {
      const hoje = new Date();
      const anoAtual = hoje.getUTCFullYear();
      const mesAtual = hoje.getUTCMonth();
      const meses = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
        "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];

      this.meses = meses.map((mes, i) => {
        let concluido = false;
        if (anoAtual > this.ano) {
          concluido = true;
        } else if (anoAtual === this.ano) {
          concluido = mesAtual > i;
        }
        return {
          nome: mes,
          partidas: [],
          concluido,
        }
      });
    },

    /**
     * Consulta os dados necessários no back-end para montar o componente.
     */
    fetchDados() {
      this.fetchJogadores()
        .then(() => this.fetchPartidas())
        .then(() => {
          this.atualizaPontosDoMes();
          this.calculaPontuacaoFinal();
        })
    },

    /**
     * Consulta os dados referentes aos jogadores.
     */
    fetchJogadores() {
      return BGMatch.fetch('/jogadores')
        .then(response => response.json())
        .then(jogadores => {
          for (const jogador of jogadores) {
            this.jogadoresMes = jogadores.map(jogador => ({
              id: jogador.id,
              nome: jogador.nome,
              cor: jogador.cor,
              pontos: 0,
            }));
            this.jogadoresAno = jogadores.map(jogador => ({
              id: jogador.id,
              nome: jogador.nome,
              cor: jogador.cor,
              pontosTotal: 0,
              pontosMeses: new Array(12).fill(0),
            }));
          }
        })
        .catch(error => {
          alert('Ocorreu um erro ao obter os dados dos jogadores.');
          if (process.env.NODE_ENV === 'development') {
            console.error(error);
          }
        });
    },

    /**
     * Consulta a lista de partida no ano.
     *
     * As partidas têm sua pontuação processada, conforme as regras do ranking,
     * a são separadas nos meses aos quais pertencem.
     */
    fetchPartidas() {
      return BGMatch.fetch('/partidas/lista/' + this.ano + '?sort=asc&ranking=1')
        .then(response => response.json())
        .then(partidas => {
          let mes = 0;
          for (const partida of partidas) {
            // Processa a pontuação da partida.
            partida.jogadores.forEach(jp => {
              jp.pontos_ranking = this.getPontosRankingPartida(partida, jp.posicao);
            });

            // Pôe a partida na lista de partidas do seu mês.
            mes = parseInt(partida.data.substring(5, 7)) - 1;
            this.meses[mes].partidas.push(partida);
          }
          // O mês atual é o último mês processado.
          this.mes = mes;
        })
        .catch(error => {
          alert('Ocorreu um erro ao obter os dados do ranking.');
          if (process.env.NODE_ENV === 'development') {
            console.error(error);
          }
        });
    },

    /**
     * Muda o mês sendo visualizado.
     *
     * @param mes
     */
    mudaMes(mes) {
      this.mes = mes;
      this.atualizaPontosDoMes();
    },

    atualizaPontosDoMes() {
      for (const jogador of this.jogadoresMes) {
        jogador.pontos = 0;
      }
      for (const partida of this.meses[this.mes].partidas) {
        for (const jp of partida.jogadores) {
          this.jogadoresMes.find(j => j.id === jp.id).pontos += jp.pontos_ranking;
        }
      }
    },

    /**
     * Faz o cálculo dos pontos de vitória dos jogadores no ranking ao longo do
     * ano, conforme sua colocação em cada mês.
     */
    calculaPontuacaoFinal() {
      this.meses.filter(m => m.concluido).forEach((mes, m) => {
        let jogadores = new Map();

        // Percorre as partidas do mês e soma os pontos que cada jogador fez.
        for (const partida of mes.partidas) {
          for (const jogadorPartida of partida.jogadores) {
            const pontos = jogadorPartida.pontos_ranking;
            const jogador = jogadores.get(jogadorPartida.id);
            if (jogador) {
              jogador.pontos += pontos;
            }
            else {
              jogadores.set(jogadorPartida.id, {
                id: jogadorPartida.id,
                pontos,
                pv: 0
              });
            }
          }
        }

        // Ordena a lista dos jogadores do mês pela quantidade de pontos.
        jogadores = jogadores.values().toArray().toSorted((a, b) => {
          return b.pontos - a.pontos;
        });

        // Os três primeiros de cada mês ganham 3, 2 e 1 PVs, respectivamente.
        for (let i = 0; i < 3; i++) {
          if (jogadores[i]) {
            const pontosVitoria = 3 - i;
            const jogador = this.jogadoresAno.find(j => j.id === jogadores[i].id);
            jogador.pontosTotal += pontosVitoria;
            jogador.pontosMeses[m] = pontosVitoria;
          }
        }

      });
    },

    /**
     * Retorna o valor do peso de uma partida, conforme o peso do jogo e
     * possível expansão utilizada.
     *
     * @param partida
     * @returns {number}
     */
    getPesoPartida(partida) {
      return partida.expansao && partida.expansao.peso
        ? partida.expansao.peso
        : (partida.jogo.peso ?? 0);
    },

    /**
     * Calcula a pontuação de ranking de uma partida, de acordo com o peso do
     * jogo e a posição do jogador.
     *
     * @param partida
     * @param posicao
     * @returns {number}
     */
    getPontosRankingPartida(partida, posicao) {
      // Posição do jogador na partida.
      const pos = parseInt(posicao);
      if (pos < 1 || pos > 6) {
        return 0;
      }

      // Peso do jogo/expansão da partida.
      const peso = this.getPesoPartida(partida);
      if (!peso) {
        return 0;
      }
      // Peso equalizado (0 a 1).
      const pesoEq = peso / 5;

      // Fator de multiplicação da posição (1º lugar, 2º lugar, 3º lugar, ...).
      const fator = [10, 7, 4, 1, 1, 1];

      return pesoEq * fator[pos - 1];
    },

    /**
     * Retorna a pontuação de ranking de um jogador em uma partida. Esse é um
     * valor já calculado anteriormente no método processaPontosPartida().
     *
     * @param idJogador
     * @param partida
     * @returns {number}
     */
    getPontuacaoJogador(idJogador, partida) {
      const jogadorPartida = partida.jogadores.find(j => j.id === idJogador);
      if (!jogadorPartida) {
        return 0;
      }
      return jogadorPartida.pontos_ranking;
    },

    /**
     * Método auxiliar para retornar os dados de um jogador em uma partida.
     *
     * @param idJogador
     * @param partida
     * @returns {object|null}
     */
    getPosicaoJogador(idJogador, partida) {
      return partida.jogadores.find(j => j.id === idJogador)?.posicao;
    },

    /**
     * Método auxiliar para arredondar os valores de pontuação para uma casa
     * decimal.
     *
     * @param points
     * @returns {number}
     */
    roundPoints(points) {
      return Math.round((points + Number.EPSILON) * 10) / 10;
    }
  }
}
</script>

<template>
  <div class="ranking ranking-2024">

    <h3>Pontuação Mensal</h3>

    <b-nav pills class="mb-3 nav-meses">
      <b-nav-item v-for="(m, i) in meses" :key="i" :active="i === mes" :disabled="m.partidas.length === 0 && m.concluido === false" v-on:click="mudaMes(i)">
        {{m.nome.substring(0, 1)}}<span class="three">{{m.nome.substring(1, 3)}}</span><span class="end">{{m.nome.substring(3)}}</span>
      </b-nav-item>
    </b-nav>

    <div class="table-responsive mb-5">
      <table class="table table-sm table-striped table-borderless table-pontos-mes">
        <thead>
        <tr>
          <th class="text-center">Dia</th>
          <th>Jogo</th>
          <th class="text-center">Peso</th>
          <th class="text-center col-jogador" v-for="jogador in jogadoresMesSorted" :key="jogador.id">
            <Meeple class="meeple" :cor="jogador.cor"></Meeple><br>
            <span class="nome-jogador d-none d-lg-inline">{{jogador.nome}}</span>
          </th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="partida in partidasMes">
          <td class="text-center">
            {{ partida.data.substring(8, 10) }}
          </td>
          <td>
            {{ partida.expansao ? partida.expansao.nome : partida.jogo.nome }}
          </td>
          <td class="text-center text-muted">
            {{ getPesoPartida(partida) }}
          </td>
          <td class="text-center col-pontos-partida" v-for="jogador in jogadoresMesSorted" :key="jogador.id">
            <span :class="getPosicaoJogador(jogador.id, partida) ? 'posicao-' + getPosicaoJogador(jogador.id, partida) : 'ausente'">
              {{ roundPoints(getPontuacaoJogador(jogador.id, partida)) }}
            </span>
          </td>
        </tr>
        </tbody>
        <tfoot>
        <tr>
          <th colspan="3" class="text-center">
            {{ partidasMes?.length }} partidas
          </th>
          <th class="text-center" v-for="jogador in jogadoresMesSorted" :key="jogador.id">
            {{ roundPoints(jogador.pontos) }}
          </th>
        </tr>
        </tfoot>
      </table>
    </div>

    <h3>Pontuação Final</h3>

    <div class="table-responsive">
      <table v-if="jogadoresAnoSorted.length > 0" class="table table-geral">
        <colgroup>
          <col class="col-jogador">
        </colgroup>
        <thead>
        <tr>
          <th scope="col" class="col-jogador">
            <span class="nome-jogador">Jogador</span>
          </th>
          <th scope="col" v-for="(m, i) in meses" :key="i" class="col-estrelas">
            {{m.nome.substring(0, 1)}}<span class="three">{{m.nome.substring(1, 3)}}</span><span class="end">{{m.nome.substring(3)}}</span>
          </th>
          <th scope="col" class="col-total">
            T<span class="three">otal</span>
          </th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="jogador in jogadoresAnoSorted" :key="jogador.id">
          <td class="col-jogador">
            <Meeple class="meeple" :cor="jogador.cor"></Meeple>
            <span class="nome-jogador">{{ jogador.nome }}</span>
          </td>
          <td v-for="(m, i) in meses" :key="i" class="col-estrelas">
            <img v-for="(n, i) in Array(jogador.pontosMeses[i])" :key="i" src="/images/estrela.svg" class="estrela" alt="Estrela"/>
          </td>
          <td class="col-total">
            {{jogador.pontosTotal}}
          </td>
        </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<style scoped lang="scss">

</style>
