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
