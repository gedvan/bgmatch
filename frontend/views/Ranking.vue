<template>
  <div id="ranking">
    <header class="d-flex">
      <h3>Ranking {{ ano }}</h3>
      <b-dropdown text="Anteriores" right size="sm" class="ml-auto align-self-center">
        <b-dropdown-item to="/ranking" :active="$route.path === '/ranking'">Atual</b-dropdown-item>
        <b-dropdown-item v-for="anoAnterior in anteriores" :to="'/ranking/' + anoAnterior" :active="$route.path === '/ranking/' + anoAnterior">{{ anoAnterior }}</b-dropdown-item>
      </b-dropdown>
    </header>
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
              <thead>
              <tr>
                <th>Jogador</th><th class="text-right">Pontuação</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="jogador in jogadores" :key="jogador.id">
                <td>
                  <Marcador class="marcador" :color="jogador.cor"></Marcador>
                  {{ jogador.nome }}
                </td>
                <td class="text-right">{{ jogador.total }}</td>
              </tr>
              </tbody>
            </table>

          </div>
          <div class="col-lg">

          </div>
        </div>

        <grafico-ranking class="grafico" :jogadores="jogadores" periodo="semanal"></grafico-ranking>

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
        trilha: [],
        jogadores: []
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
        const cores = [
          '#36C07B',
          '#9966FF',
          '#36A2EB',
          '#FF6384',
          '#FFCE56',
        ];
        const ano = this.ano;
        BGMatch.fetch('/ranking/' + ano)
          .then(response => response.json())
          .then(jogadores => this.jogadores = jogadores.map(jogador => {
            let j = jogadores.find(j => j.id === jogador.id);
            jogador.total = j ? j.total : 0;
            jogador.cor = cores[jogador.id - 1];
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
      }
    },

    mounted() {
      if (this.$route.params.hasOwnProperty('ano') && /^\d{4}$/.test(this.$route.params.ano)) {
        this.ano = this.$route.params.ano;
      } else {
        this.ano = new Date().getFullYear();
      }
      this.montaTrilha();
      this.fetchDados();
    }
  }
</script>

<style lang="scss">
  @import "../scss/includes";

  #ranking {
    h3 {
      margin: .8em 0;
    }
    .trilha-pontos {
      display: grid;
      grid-template-columns: 29px auto 29px;
      grid-template-rows: 29px auto auto auto auto 29px;
      grid-template-areas: "d0 d0 d0" "d9 c d1" "d8 c d2" "d7 c d3" "d6 c d4" "d5 d5 d5";

      .casa {
        background: #f2ece1;
        color: #ddd4c2;
        font-size: 110%;
        font-weight: bold;
        border: 1px solid white;
        flex-grow: 1;
        min-width: 29px;
        min-height: 29px;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;

        &.unidade-0, &.unidade-5 {
          background: #e1d7c3;
          color: white;
          font-size: 120%;
        }
        .marcador {
          width: 30px;
          height: auto;
          position: absolute;
          left: 50%;
          top: 50%;
          margin-left: -15px;
          margin-top: -15px;
          z-index: 0;

          &.sobre-1 {
            margin-top: -22px;
            z-index: 1;
          }
          &.sobre-2 {
            margin-top: -29px;
            z-index: 2;
          }
          &.sobre-3 {
            margin-top: -36px;
            z-index: 3;
          }
          &.sobre-4 {
            margin-top: -43px;
            z-index: 4;
          }
        }
      }

      .dezena {
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-between;

        &.dezena-0 {
          grid-area: d0;
          flex-direction: row;
        }
        &.dezena-1 {
          grid-area: d1;
          flex-direction: column;
        }
        &.dezena-2 {
          grid-area: d2;
          flex-direction: column;
        }
        &.dezena-3 {
          grid-area: d3;
          flex-direction: column;
        }
        &.dezena-4 {
          grid-area: d4;
          flex-direction: column;
        }
        &.dezena-5 {
          grid-area: d5;
          flex-direction: row-reverse;
        }
        &.dezena-6 {
          grid-area: d6;
          flex-direction: column-reverse;
        }
        &.dezena-7 {
          grid-area: d7;
          flex-direction: column-reverse;
        }
        &.dezena-8 {
          grid-area: d8;
          flex-direction: column-reverse;
        }
        &.dezena-9 {
          grid-area: d9;
          flex-direction: column-reverse;
        }
      }

      @include media-breakpoint-up(sm) {
        grid-template-columns: 34px auto 34px;
        grid-template-rows: 34px auto auto auto auto 34px;

        .casa {
          min-width: 34px;
          min-height: 34px;
        }
      }

      @include media-breakpoint-up(md) {
        grid-template-columns: 34px auto auto 34px;
        grid-template-rows: 34px auto auto auto 34px;
        grid-template-areas: "d0 d0 d1 d1" "d9 c c d2" "d8 c c d3" "d7 c c d4" "d6 d6 d5 d5";

        .dezena.dezena-1 {
          flex-direction: row;
        }
        .dezena.dezena-6 {
          flex-direction: row-reverse;
        }
      }

      @include media-breakpoint-up(xl) {
        grid-template-columns: 37px auto auto auto 37px;
        grid-template-rows: 37px auto auto 37px;
        grid-template-areas: "d0 d0 d1 d2 d2" "d9 c c c d3" "d8 c c c d4" "d7 d7 d6 d5 d5";

        .casa {
          min-width: 37px;
          min-height: 37px;
        }

        .dezena.dezena-2 {
          flex-direction: row;
        }
        .dezena.dezena-7 {
          flex-direction: row-reverse;
        }
      }

      .info {
        grid-area: c;
        padding: 15px;

        .table-pontuacao {
          //width: 80%;
          //font-size: 120%;

          .marcador {
            width: 20px;
            height: auto;
            margin-right: .2em;
          }
        }

        .grafico {
          //margin-top: 30px;
          position: relative;
          overflow: auto;
        }

        @include media-breakpoint-up(sm) {
        }
        @include media-breakpoint-up(md) {
          padding: 30px;
        }
        @include media-breakpoint-up(lg) {
        }
        @include media-breakpoint-up(xl) {
        }
      }

    }
  }
</style>
