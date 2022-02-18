<template>
  <div id="lista-jogadores" class="container">

    <b-alert variant="danger" :show="erro.length" class="mt-3">{{ erro }}</b-alert>

    <template v-if="jogadores.length > 0">
      <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3">
        <div class="col mt-4" v-for="jogador in jogadores" :key="jogador.id">
          <b-card class="jogador h-100" :title="jogador.nome">
            <table class="table table-sm">
              <tbody>
              <tr>
                <th scope="row">Total de partidas</th>
                <td colspan="2"><strong>{{ jogador.num_partidas }}</strong></td>
              </tr>
              <tr v-for="resultado in jogador.resultados">
                <td>{{ resultado.posicao }}º Lugar</td>
                <td>{{ resultado.quantidade }}</td>
                <td>{{ (100 * resultado.quantidade / jogador.num_partidas).toFixed(1) }}%</td>
              </tr>
              </tbody>
            </table>
            <template v-if="jogador.vitorias.length > 0">
              <h5>Jogos com mais vitórias</h5>
              <ul class="mb-0">
                <li v-for="vitoria in jogador.vitorias">
                  {{ vitoria.nome_jogo }} ({{ vitoria.qtd }})
                </li>
              </ul>
            </template>
          </b-card>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-4 mt-4">
          <b-card title="% de vitórias">
            <grafico-vitorias :jogadores="jogadores" class="grafico-vitorias"></grafico-vitorias>
          </b-card>
        </div>
        <div class="col-lg-8 mt-4">
          <b-card title="% de posições por jogador">
            <grafico-jogadores :jogadores="jogadores" class="grafico-posicoes"></grafico-jogadores>
          </b-card>
        </div>
      </div>
    </template>

  </div>
</template>

<script>
  import BGMatch from "../BGMatch";
  import GraficoJogadores from "../components/GraficoJogadores";
  import GraficoVitorias from "../components/GraficoVitorias";

  export default {
    components: {
      GraficoVitorias,
      GraficoJogadores
    },

    data() {
      return {
        erro: '',
        jogadores: [],
      }
    },

    computed: {
      labelsVitorias() {
        return this.jogadores.map(j => j.nome);
        //return this.jogadores.sort((a, b) => a.num_vitorias - b.num_vitorias).map(j => j.nome);
      },
      valuesVitorias() {
        return this.jogadores.map(j => j.num_vitorias);
        //return this.jogadores.sort((a, b) => a.num_vitorias - b.num_vitorias).map(j => j.num_vitorias);
      }
    },

    methods: {
      fetchJogadores: function() {
        BGMatch.fetch('/jogadores/dados')
          .then(response =>  response.json())
          .then(jogadores => {
            this.jogadores = jogadores;
          })
          .catch(error => {
            this.erro = 'Ocorreu um erro ao obter a lista de jogadores.';
            console.error(error);
          });
      }
    },

    mounted() {
      this.fetchJogadores();
    }
  }
</script>
