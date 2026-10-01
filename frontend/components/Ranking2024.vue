<script>
import Meeple from "./Meeple.vue";

/**
 * Exibe o ranking de 2024 em diante. A pontuação (por partida, por mês e as
 * estrelas do ano) vem pronta do backend; este componente só desenha.
 */
export default {
  components: {
    Meeple
  },

  props: {
    ranking: Object
  },

  data() {
    return {
      /**
       * Índice do mês visualizado atualmente. 0 = Janeiro, 11 = Dezembro.
       */
      mes: this.ranking.mes_atual,
    }
  },

  computed: {
    meses() {
      return this.ranking.meses;
    },

    partidasMes() {
      return this.meses[this.mes]?.partidas ?? [];
    },

    /**
     * Jogadores com seus pontos no mês visualizado, do maior para o menor.
     */
    jogadoresMesSorted() {
      return this.meses[this.mes]?.classificacao ?? [];
    },

    /**
     * Jogadores com as estrelas do ano, já na ordem final.
     */
    jogadoresAnoSorted() {
      return this.ranking.jogadores;
    },
  },

  methods: {
    /**
     * Muda o mês sendo visualizado.
     *
     * @param mes
     */
    mudaMes(mes) {
      this.mes = mes;
    },

    /**
     * Retorna a pontuação de ranking de um jogador em uma partida.
     *
     * @param idJogador
     * @param partida
     * @returns {number}
     */
    getPontuacaoJogador(idJogador, partida) {
      return partida.jogadores.find(j => j.id === idJogador)?.pontos ?? 0;
    },

    /**
     * Método auxiliar para retornar a posição de um jogador em uma partida.
     *
     * @param idJogador
     * @param partida
     * @returns {number|undefined}
     */
    getPosicaoJogador(idJogador, partida) {
      return partida.jogadores.find(j => j.id === idJogador)?.posicao;
    },

    /**
     * Formata o peso da partida com duas casas decimais.
     *
     * @param peso
     * @returns {string|number}
     */
    formataPeso(peso) {
      return peso ? peso.toFixed(2) : 0;
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
            {{ formataPeso(partida.peso) }}
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
            <img v-for="(n, i) in Array(jogador.pontos_meses[i])" :key="i" src="/images/estrela.svg" class="estrela" alt="Estrela"/>
          </td>
          <td class="col-total">
            {{jogador.pontos_total}}
          </td>
        </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<style scoped lang="scss">

</style>
