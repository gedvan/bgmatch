<template>
  <div id="ranking">
    <h3>Ranking {{ $route.params.ano }}</h3>
    <div class="trilha-pontos">
      <div v-for="(x, d) in 10" :class="['dezena', 'dezena-' + d]">
        <div v-for="(y, u) in 10" :class="['casa', 'casa-' + u]">
          {{ (d * 10) + u }}
          <template v-for="jogador in jogadores">
            <Marcador v-if="((d * 10) + u) === jogador.pontuacao" class="marcador" :color="jogador.cor"></Marcador>
          </template>
        </div>
      </div>
      <div class="info">
        <div class="row">
          <div class="col-lg col-pontuacao">

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
                <td class="text-right">{{ jogador.pontuacao }}</td>
              </tr>
              </tbody>
            </table>

          </div>
          <div class="col-lg">

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import Marcador from '../components/Marcador.vue';
  import BGMatch from "../BGMatch";

  export default {
    components: {
      Marcador,
    },

    data() {
      return {
        jogadores: [],

        cores: [
          '#36A2EB',
          '#9966FF',
          '#FF6384',
          '#36C07B',
          '#FFCE56',
        ],
      }
    },

    methods: {
      fetchJogadores() {
        window.fetch(BGMatch.apiUrl + '/jogadores')
          .then(response => response.json())
          .then(jogadores => this.jogadores = jogadores)
          .then(() => this.fetchDados())
          .catch(error => {
            alert('Ocorreu um erro ao obter os dados dos jgoadores.');
            console.error(error);
          });
      },

      fetchDados() {
        const ano = this.$route.params.ano;
        window.fetch(BGMatch.apiUrl + '/ranking/' + ano)
          .then(response => response.json())
          .then(pontuacao => {
            this.jogadores.forEach(jogador => {
              let p = pontuacao.find(p => p.id === jogador.id);
              jogador.pontuacao = p ? p.pontuacao : 0;
              jogador.cor = this.cores[jogador.id - 1];
            });
            this.jogadores = this.jogadores.sort((a, b) => b.pontuacao - a.pontuacao);
            this.$forceUpdate();
          })
          .catch(error => {
            alert('Ocorreu um erro ao obter os dados do ranking.');
            console.error(error);
          });
      }
    },

    mounted() {
      this.fetchJogadores();
    }
  }
</script>

<style lang="scss">
  @import "../scss/includes";

  #ranking {
    h3 {
      margin: .5em 0;
    }
    .trilha-pontos {
      display: grid;
      grid-template-columns: 29px auto 29px;
      grid-template-rows: 29px auto auto auto auto 29px;
      grid-template-areas: "d0 d0 d0" "d9 c d1" "d8 c d2" "d7 c d3" "d6 c d4" "d5 d5 d5";

      .casa {
        background: #f7f6ee;
        color: #dddabe;
        border: 1px solid white;
        flex-grow: 1;
        min-width: 29px;
        min-height: 29px;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;

        &.casa-0, &.casa-5 {
          font-weight: bold;
          background: #f2ece1;
          color: #ccbe86;
        }
        .marcador {
          width: 30px;
          height: auto;
          position: absolute;
          left: 50%;
          top: 50%;
          margin-left: -15px;
          margin-top: -15px;

          &.sobre-1 {
            margin-top: -22px;
          }
          &.sobre-2 {
            margin-top: -29px;
          }
          &.sobre-3 {
            margin-top: -36px;
          }
          &.sobre-4 {
            margin-top: -43px;
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

        .table-pontuacao {
          margin: 50px auto;
          width: 80%;
          font-size: 120%;

          .marcador {
            width: 30px;
            height: auto;
            margin-right: .5em;
          }
        }
      }

    }
  }
</style>
