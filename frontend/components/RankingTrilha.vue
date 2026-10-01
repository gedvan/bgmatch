<script>
import Marcador from "./Marcador.vue";
import GraficoRanking2019 from "./GraficoRanking2019.vue";

/**
 * Exibe os rankings de 2019 a 2023 (trilha de pontos). A pontuação vem pronta
 * do backend; este componente só desenha.
 */
export default {
  components: {
    Marcador,
    GraficoRanking2019
  },

  props: {
    ranking: Object
  },

  computed: {
    /**
     * Jogadores que pontuaram, do maior para o menor.
     */
    jogadores() {
      return this.ranking.jogadores;
    },

    /**
     * Trilha de 0 a 99, em dezenas e unidades, com os jogadores em cada casa.
     */
    trilha() {
      const trilha = [];
      for (let d = 0; d < 10; d++) {
        trilha[d] = [];
        for (let u = 0; u < 10; u++) {
          trilha[d][u] = {numero: d * 10 + u, jogadores: []};
        }
      }
      for (const jogador of this.jogadores) {
        const d = Math.floor((jogador.total % 100) / 10);
        const u = jogador.total % 10;
        trilha[d][u].jogadores.push(jogador);
      }
      return trilha;
    }
  }
}
</script>

<template>
  <div class="trilha-pontos trilha-100">
    <div v-for="(unidades, dezena) in trilha" :class="['dezena', 'dezena-' + dezena]">
      <div v-for="(casa, unidade) in unidades" :class="['casa', `casa-${casa.numero}`, `und-${unidade}`]">
        {{ casa.numero }}
        <Marcador v-for="(jogador, i) in casa.jogadores" :key="jogador.id" :class="['marcador', `sobre-${i}`]"
                  :color="jogador.cor" :numero="jogador.total"
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
              <th scope="col" v-for="categoria in ranking.tabela.categorias" class="text-center">{{ categoria.label }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="linha in ranking.tabela.posicoes">
              <td>{{ linha.posicao }}º lugar</td>
              <td v-for="categoria in ranking.tabela.categorias" class="text-center">{{ linha.pontos[categoria.key] }}</td>
            </tr>
            </tbody>
          </table>

        </div>
      </div>

      <grafico-ranking2019 :datas="ranking.datas" :jogadores="jogadores"></grafico-ranking2019>

    </div>
  </div>
</template>

<style scoped lang="scss">

</style>
