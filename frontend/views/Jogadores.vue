<template>
  <div id="lista-jogadores">
    <b-card-group columns class="my-4">
      <b-card class="jogador" v-for="jogador in jogadores" v-bind:key="jogador.id" :title="jogador.nome">
        <h5>Tabela de resultados</h5>
        <table class="table table-sm">
          <tbody>
          <tr v-for="resultado in jogador.resultados">
            <td>{{ resultado.posicao }}º Lugar</td>
            <td>{{ resultado.quantidade }}</td>
            <td>{{ (100 * resultado.quantidade / jogador.num_partidas).toFixed(1) }}%</td>
          </tr>
          <tr>
            <th scope="row">Total de partidas</th>
            <td colspan="2"><strong>{{ jogador.num_partidas }}</strong></td>
          </tr>
          </tbody>
        </table>
        <h5>Jogos com mais vitórias</h5>
        <ul class="mb-0">
          <li v-for="vitoria in jogador.vitorias">
            {{ vitoria.nome_jogo }} ({{ vitoria.qtd }})
          </li>
        </ul>
      </b-card>
    </b-card-group>
    <b-card>
      <grafico-jogadores :jogadores="jogadores" class="grafico-posicoes"></grafico-jogadores>
    </b-card>
  </div>
</template>

<script>
  import BGMatch from "../BGMatch";
  import GraficoJogadores from "../components/GraficoJogadores.vue";

  export default {
    components: {
      GraficoJogadores
    },

    data() {
      return {
        jogadores: [],
      }
    },

    methods: {
      fetchJogadores: function() {
        window.fetch(BGMatch.apiUrl + '/jogadores')
          .then(response => response.json())
          .then(jogadores => this.jogadores = jogadores)
          .catch(error => {
            alert('Ocorreu um erro ao obter a lista de jogadores.');
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

  #lista-jogadores {
    .card-columns {
      @include media-breakpoint-only(sm) {
        column-count: 2;
      }
      @include media-breakpoint-only(md) {
        column-count: 2;
      }
    }
    .grafico-posicoes {
      height: 500px;
    }
  }
</style>
