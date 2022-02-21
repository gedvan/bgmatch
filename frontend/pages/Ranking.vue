<template>
  <div id="ranking">

    <header class="bg-alternate">
      <div class="container">
        <h3>Ranking {{ ano }}</h3>
        <b-dropdown text="Ano" right size="sm" variant="light">
          <b-dropdown-item to="/ranking" :active="$route.path === '/ranking'">Atual</b-dropdown-item>
          <b-dropdown-item v-for="anoAnterior in anteriores" :key="anoAnterior" :to="'/ranking/' + anoAnterior" :active="$route.path === '/ranking/' + anoAnterior">{{ anoAnterior }}</b-dropdown-item>
        </b-dropdown>
      </div>
    </header>

    <div class="container">
      <div class="trilha-pontos">
        <div v-for="(unidades, dezena) in this.trilha" :class="['dezena', 'dezena-' + dezena]">
          <div v-for="(casa, unidade) in unidades" :class="['casa', `casa-${casa.numero}`, `unidade-${unidade}`]">
            {{ casa.numero }}
            <Marcador v-for="(jogador, i) in casa.jogadores" :key="jogador.id" :class="['marcador', `sobre-${i}`]" :color="jogador.cor"
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
                  <tr v-for="jogador in jogadores" :key="jogador.id">
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
                    <th scope="col" v-for="peso in pesosPontuacao" class="text-center">{{ peso }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="pontosPosicao in pontuacao">
                    <td>{{ pontosPosicao.posicao }}º lugar</td>
                    <td v-for="pontos in Object.values(pontosPosicao.pontuacao)" class="text-center">
                      {{ pontos }}
                    </td>
                  </tr>
                </tbody>
              </table>

            </div>
          </div>

          <grafico-ranking class="grafico" :jogadores="jogadores" periodo="semanal"></grafico-ranking>

        </div>
      </div>
    </div>

  </div>
</template>

<script>
  import Marcador from '../components/Marcador';
  import BGMatch from "../BGMatch";
  import GraficoRanking from "../components/GraficoRanking";

  export default {
    components: {
      GraficoRanking,
      Marcador
    },

    data() {
      const anoInicial = 2018;
      const anoAtual = new Date().getFullYear();
      const anos = Array.from({length: anoAtual - anoInicial}, (v, k) => anoInicial + k).reverse();
      return {
        ano: anoAtual,
        anteriores: anos,
        pontuacao: [],
        trilha: [],
        jogadores: []
      }
    },

    computed: {
      pesosPontuacao() {
        return this.pontuacao.length > 0
          ? Object.keys(this.pontuacao[0].pontuacao).map(p => BGMatch.nomeCategoria(p))
          : [];
      }
    },

    methods: {
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

      fetchDados() {
        const ano = this.ano;
        BGMatch.fetch('/ranking/' + ano)
          .then(response => response.json())
          .then(jogadores => this.jogadores = jogadores.map(jogador => {
            let j = jogadores.find(j => j.id === jogador.id);
            jogador.total = j ? j.total : 0;
            return jogador;
          }))
          .then(jogadores => {
            this.jogadores.forEach(jogador => {
              const d = Math.floor((jogador.total % 100) / 10);
              const u = jogador.total % 10;
              this.trilha[d][u].jogadores.push(jogador);
            });
            this.$forceUpdate();
          })
          .catch(error => {
            alert('Ocorreu um erro ao obter os dados do ranking.');
            console.error(error);
          });
      },

      fetchTabelaPontuacao() {
        BGMatch.fetch('/ranking/' + this.ano + '/pontuacao')
          .then(response => response.json())
          .then(pontuacao => {
            console.log(pontuacao);
            this.pontuacao = pontuacao;
            /*
            this.pontuacao = [1, 2, 3, 4, 5, 6].map(p => ({
              pos: p,
              pontuacao: {P: 10, M: 7, L: 5},
            }));
            console.log(this.pontuacao);
             */
          })
          .catch(error => {
            alert('Ocorreu um erro ao obter os dados do ranking.');
            console.error(error);
          });
      },

      pontuacaoPosicao(pos) {
        this.pontuacao.reduce();
      }
    },

    mounted() {
      if (this.$route.params.hasOwnProperty('ano') && /^\d{4}$/.test(this.$route.params.ano)) {
        this.ano = this.$route.params.ano;
      } else {
        this.ano = new Date().getFullYear();
      }
      this.montaTrilha();
      this.fetchTabelaPontuacao();
      this.fetchDados();
    }
  }
</script>

<style lang="scss" scoped>
.col-jogador {
  width: 90%;
}
.col-posicao {
  width: 40%;
}
</style>
