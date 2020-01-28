<script>
  import { Pie } from 'vue-chartjs';

  export default {
    extends: Pie,
    props: ['resultados'],

    data() {
      return {
        chartdata: {
          labels: ['1º Lugar', '2º Lugar', '3º Lugar', '4º Lugar', '5º Lugar'],
          datasets: [{
            data: [],
            backgroundColor: [
              'rgb(54,162,235)',
              'rgb(54,192,123)',
              'rgba(255, 206, 86)',
              'rgba(255, 99, 132)',
              'rgba(153, 102, 255)',
            ],
          }]
        },

        options: {
          legend: {
            position: 'bottom'
          }
        }
      }
    },

    mounted() {
      this.chartdata.datasets[0].data = this.resultados.map(r => r.quantidade);
      this.renderChart(this.chartdata, this.options)
    }
  }
</script>
