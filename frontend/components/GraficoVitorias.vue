<script>
  import { Pie } from 'vue-chartjs';

  export default {
    extends: Pie,
    props: ['jogadores'],

    data() {
      return {
        colors: ['#36A2EB', '#36C07B', '#FFCE56', '#FF6384', '#9966FF'],
        options: {
          legend: {
            position: 'bottom'
          }
        }
      }
    },

    computed: {
      chartdata() {
        let jogadores = this.jogadores.sort((a, b) => b.num_vitorias - a.num_vitorias);
        return {
          labels: jogadores.map(j => j.nome),
          datasets: [{
            data: jogadores.map(j => j.num_vitorias),
            backgroundColor: this.colors
          }]
        }
      }
    },

    watch: {
      jogadores() {
        this.renderChart(this.chartdata, this.options)
      }
    },

    mounted() {
      this.renderChart(this.chartdata, this.options)
    }
  }
</script>
