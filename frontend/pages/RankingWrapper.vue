<script>
import Ranking2019 from '../components/Ranking2019.vue';
import Ranking2022 from '../components/Ranking2022.vue';
import Ranking2024 from "../components/Ranking2024.vue";
export default {
  components: {
    'ranking-2019': Ranking2019,
    'ranking-2022': Ranking2022,
    'ranking-2024': Ranking2024
  },

  data() {
    const anoInicial = 2019;
    const anoAtual = new Date().getFullYear();
    const anosAnteriores = Array.from({length: anoAtual - anoInicial}, (v, k) => anoInicial + k).reverse();
    return {
      anoAtual: anoAtual,
      anosAnteriores: anosAnteriores,
      anoSelecionado: anoAtual,
    }
  },

  mounted() {
    if (this.$route.params.hasOwnProperty('ano') && /^\d{4}$/.test(this.$route.params.ano)) {
      this.anoSelecionado = parseInt(this.$route.params.ano);
    }
  }
}
</script>

<template>
  <div id="ranking">

    <header class="bg-alternate">
      <div class="container">
        <h3>Ranking {{ anoSelecionado }}</h3>
        <b-dropdown :text="anoSelecionado + (anoSelecionado === anoAtual ? ' - Atual' : '')" right size="sm" variant="light">
          <b-dropdown-item to="/ranking" :active="$route.path === '/ranking'">{{ anoAtual }} - Atual</b-dropdown-item>
          <b-dropdown-item v-for="anoAnterior in anosAnteriores" :key="anoAnterior" :to="'/ranking/' + anoAnterior" :active="$route.path === '/ranking/' + anoAnterior">{{ anoAnterior }}</b-dropdown-item>
        </b-dropdown>
      </div>
    </header>

    <div class="container">
      <ranking-2019 v-if="anoSelecionado >= 2019 && anoSelecionado <= 2021" :ano="anoSelecionado"></ranking-2019>
      <ranking-2022 v-if="anoSelecionado >= 2022 && anoSelecionado <= 2023" :ano="anoSelecionado"></ranking-2022>
      <ranking-2024 v-if="anoSelecionado >= 2024" :ano="anoSelecionado"></ranking-2024>
    </div>

  </div>
</template>

<style scoped lang="scss">

</style>
