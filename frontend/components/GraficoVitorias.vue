<script>
  import { Pie } from 'vue-chartjs';

  export default {
    extends: Pie,
    props: ['jogadores'],

    data() {
      return {
        options: {
          legend: {
            position: 'bottom'
          }
        }
      }
    },

    computed: {
      chartdata() {
        return {
          labels: this.jogadores.map(j => j.nome),
          datasets: [{
            data: this.jogadores.map(j => j.num_vitorias),
            backgroundColor: this.jogadores.map(j => j.cor)
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
