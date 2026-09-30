<script>
import BGMatch from "../BGMatch";
import RankingTrilha from '../components/RankingTrilha.vue';
import Ranking2024 from "../components/Ranking2024.vue";
export default {
  components: {
    RankingTrilha,
    Ranking2024
  },

  data() {
    const anoInicial = 2019;
    const anoAtual = new Date().getFullYear();
    const anosAnteriores = Array.from({length: anoAtual - anoInicial}, (v, k) => anoInicial + k).reverse();
    return {
      anoAtual: anoAtual,
      anosAnteriores: anosAnteriores,
      anoSelecionado: anoAtual,
      ranking: null,
      erro: '',
    }
  },

  created() {
    if (this.$route.params.hasOwnProperty('ano') && /^\d{4}$/.test(this.$route.params.ano)) {
      this.anoSelecionado = parseInt(this.$route.params.ano);
    }
    this.fetchRanking();
  },

  methods: {
    /**
     * Busca o ranking do ano, já calculado pelo backend com a regra da época.
     */
    fetchRanking() {
      BGMatch.fetch('/ranking/' + this.anoSelecionado)
        .then(response => response.json())
        .then(ranking => {
          this.ranking = ranking;
        })
        .catch(error => {
          this.erro = 'Não foi possível carregar o ranking.';
          if (process.env.NODE_ENV === 'development') {
            console.error(error);
          }
        });
    }
  }
}
</script>

<template>
  <div id="ranking">

    <header class="bg-alternate">
      <div class="container">
        <h2>Ranking {{ anoSelecionado }}</h2>
        <b-dropdown :text="anoSelecionado + (anoSelecionado === anoAtual ? ' - Atual' : '')" right size="sm" variant="light">
          <b-dropdown-item to="/ranking" :active="$route.path === '/ranking'">{{ anoAtual }} - Atual</b-dropdown-item>
          <b-dropdown-item v-for="anoAnterior in anosAnteriores" :key="anoAnterior" :to="'/ranking/' + anoAnterior"
                           :active="$route.path === '/ranking/' + anoAnterior">{{ anoAnterior }}</b-dropdown-item>
        </b-dropdown>
      </div>
    </header>

    <div class="container">
      <b-alert v-if="erro" show variant="danger">{{ erro }}</b-alert>
      <RankingTrilha v-if="ranking && (ranking.regra === '2019' || ranking.regra === '2022')" :ranking="ranking"></RankingTrilha>
      <Ranking2024 v-if="ranking && ranking.regra === '2024'" :ranking="ranking"></Ranking2024>
    </div>

  </div>
</template>

<style scoped lang="scss">

</style>
