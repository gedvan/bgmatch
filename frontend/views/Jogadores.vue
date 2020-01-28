<template>
  <div id="lista-jogadores">
    <b-card-group columns class="my-4">
      <b-card class="jogador" v-for="jogador in jogadores" v-bind:key="jogador.id" :title="jogador.nome">
        <dl>
          <div class="row">
            <div class="col">
              <dt>Número de partidas</dt>
              <dd>{{ jogador.num_partidas }}</dd>
            </div>
            <div class="col">
              <dt>Número de vitórias</dt>
              <dd>{{ jogador.num_vitorias }} ({{ percentualJogador(jogador) }}%)</dd>
            </div>
          </div>
          <dt>Jogos com mais vitórias</dt>
          <dd>
            <ul class="mb-0">
              <li v-for="vitoria in jogador.vitorias">
                {{ vitoria.nome_jogo }} ({{ vitoria.qtd }})
              </li>
            </ul>
          </dd>
          <dt>Gráfico de colocações</dt>
          <dd>
            <grafico-posicao :resultados="jogador.resultados"></grafico-posicao>
          </dd>
        </dl>
      </b-card>
    </b-card-group>
  </div>
</template>

<script>
  import BGMatch from "../BGMatch";
  import GraficoPosicao from "../components/GraficoPosicao.vue";

  export default {
    components: {
      GraficoPosicao
    },

    data() {
      return {
        jogadores: [],
      }
    },

    methods: {
      percentualJogador: function(jogador) {
        const perc = 100 * jogador.num_vitorias / jogador.num_partidas;
        return perc.toFixed(1);
      },

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
