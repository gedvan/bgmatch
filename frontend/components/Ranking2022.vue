<script>
import Marcador from "./Marcador.vue";
import BGMatch from "../BGMatch";
import GraficoRanking2019 from "./GraficoRanking2019.vue";

export default {
  components: {
    Marcador,
    GraficoRanking2019
  },

  props: {
    ano: Number
  },

  data() {
    return {
      tabelaPontuacao: [
        {"P": 10, "M": 7, "L": 4},  // 0: Posição 1
        {"P": 7,  "M": 4, "L": 2},  // 1: Posição 2
        {"P": 4,  "M": 2, "L": 1},  // 2: Posição 3
        {"P": 2,  "M": 1, "L": 1},  // 2: Posição 4
        {"P": 1,  "M": 1, "L": 1},  // 2: Posição 5
        {"P": 1,  "M": 1, "L": 1}   // 2: Posição 6
      ],
      trilha: [],
      partidas: [],
      jogadores: [],
      dataLoaded: false
    }
  },

  computed: {
    categorias() {
      return BGMatch.categoriasJogos.filter(c => c.key !== 'Y');
    },

    jogadoresOrdenadorPorPontos() {
      const jogadores = this.jogadores.map(j => ({
        id: j.id,
        nome: j.nome,
        cor: j.cor,
        total: j.total
      }))
      return jogadores.sort((a, b) => b.total - a.total);
    }
  },

  methods: {
    /**
     * Retorna a pontuação de uma determinada posição e categoria de jogos, de
     * acordo com a tabela de pontuação do ranking.
     *
     * @param posicao
     * @param categoria
     * @returns {number}
     */
    getPontuacao(posicao, categoria) {
      if (categoria === 'F' || categoria === 'I') {
        categoria = 'Y';
      }
      const p = posicao - 1;
      if (typeof this.tabelaPontuacao[p] === 'undefined' || typeof this.tabelaPontuacao[p][categoria] === 'undefined') {
        return 0;
      }
      return this.tabelaPontuacao[p][categoria];
    },

    /**
     * Inicializa o array da trilha de pontos.
     */
    montaTrilha() {
      this.trilha = new Array(10);
      for (let d = 0; d < 10; d++) {
        this.trilha[d] = new Array(10);
        for (let u = 0; u < 10; u++) {
          const n = d * 10 + u;
          this.trilha[d][u] = {
            numero: n,
            jogadores: [],
          };
        }
      }
    },

    /**
     * Consulta os dados necessários no back-end para montar o componente.
     */
    fetchDados() {
      this.fetchJogadores()
        .then(() => this.fetchPartidas())
        .then(() => {
          this.partidas.forEach(partida => this.processaPartida(partida));
          this.jogadores = this.jogadores.filter(jogador => jogador.total > 0);
          this.atualizaJogadoresTrilha();
          this.dataLoaded = true;
        })
    },

    /**
     * Consulta os jogadores cadastrados no BD.
     */
    fetchJogadores() {
      return BGMatch.fetch('/jogadores')
        .then(response => response.json())
        .then(jogadores => {
          // Pega os dados básicos dos jogadores e inicializa os totais (geral,
          // mensal e semanal) de sua pontuação no ranking.
          this.jogadores = jogadores.map(jogador => ({
            ...jogador,
            total: 0,
            mensal: new Array(12).fill(0),
            semanal: {'01-01': 0}
          }));
        });
    },

    /**
     * Consulta a lista de partida no ano.
     */
    fetchPartidas() {
      return BGMatch.fetch('/partidas-periodo/' + this.ano + '?sort=asc')
        .then(response => response.json())
        .then(partidas => {
          this.partidas = partidas;
        })
        .catch(error => {
          alert('Ocorreu um erro ao obter os dados do ranking.');
          if (process.env.NODE_ENV === 'development') {
            console.error(error);
          }
        });
    },

    /**
     * Processa as informações de uma partida, adicionando a pontuação
     * conquistada por cada jogador aos seus totais geral, semanal e mensal.
     *
     * @param partida
     */
    processaPartida(partida) {
      // Data da partida no formato "MM-DD".
      const data = partida.data.substring(5);
      console.log(data);

      // Mes da partida com inteiro (0 => Jan..11 => Dez).
      const mes = parseInt(partida.data.substring(5, 7)) - 1;

      partida.jogadores.forEach(jogadorPartida => {
        // Pontos que o jogador fez naquela partida.
        const pontos = this.getPontuacao(jogadorPartida.posicao, partida.jogo.categoria);

        const jogador = this.jogadores.find(j => j.id == jogadorPartida.id);
        if (!jogador) {
          return;
        }

        // Atualiza as pontuações do jogador (total, mensal e semanal).
        jogador.total += pontos;
        jogador.mensal[mes] += pontos;
        if (typeof jogador.semanal[data] === 'undefined') {
          jogador.semanal[data] = pontos;
        } else {
          jogador.semanal[data] += pontos;
        }
      })
    },

    /**
     * Atualiza a posição do jogador na trilha de pontos.
     */
    atualizaJogadoresTrilha() {
      this.jogadores.forEach(jogador => {
        const d = Math.floor((jogador.total % 100) / 10);
        const u = jogador.total % 10;
        this.trilha[d][u].jogadores.push(jogador);
      });
      this.$forceUpdate();
    }
  },

  mounted() {
    this.montaTrilha();
    this.fetchDados();
  }
}
</script>

<template>
  <div class="trilha-pontos">
    <div v-for="(unidades, dezena) in this.trilha" :class="['dezena', 'dezena-' + dezena]">
      <div v-for="(casa, unidade) in unidades" :class="['casa', `casa-${casa.numero}`, `unidade-${unidade}`]">
        {{ casa.numero }}
        <Marcador v-for="(jogador, i) in casa.jogadores" :key="jogador.id" :class="['marcador', `sobre-${i}`]"
                  :color="jogador.cor" :numero="Math.floor(jogador.total / 100) * 100"
                  v-b-popover.hover.click.top="`${jogador.nome} (${jogador.total})`"></Marcador>
      </div>
    </div>
    <div class="info">
      <div class="row">
        <div class="col-lg">

          <table v-if="jogadores.length > 0" class="table table-pontuacao">
            <colgroup>
              <col class="col-jogador">
            </colgroup>
            <thead>
            <tr>
              <th>Jogador</th><th class="text-center">Pontos</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="jogador in jogadoresOrdenadorPorPontos" :key="jogador.id">
              <td>
                <Marcador class="marcador" :color="jogador.cor"></Marcador>
                {{ jogador.nome }}
              </td>
              <td class="text-center">{{ jogador.total }}</td>
            </tr>
            </tbody>
          </table>

        </div>
        <div class="col-lg">

          <table class="table">
            <colgroup>
              <col class="col-posicao">
            </colgroup>
            <thead>
            <tr>
              <th scope="col">Posição / Pontuação</th>
              <th scope="col" v-for="categoria in categorias" class="text-center">{{ categoria.label }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(pontuacao, index) in tabelaPontuacao">
              <td>{{ index + 1 }}º lugar</td>
              <td v-for="categoria in categorias" class="text-center">{{ pontuacao[categoria.key] }}</td>
            </tr>
            </tbody>
          </table>

        </div>
      </div>

      <grafico-ranking2019 v-if="dataLoaded" :partidas="partidas" :jogadores="jogadores" periodo="semanal"></grafico-ranking2019>

    </div>
  </div>
</template>

<style scoped lang="scss">

</style>
