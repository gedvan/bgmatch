<script>
  import { Bar } from 'vue-chartjs';

  export default {
    extends: Bar,
    props: ['jogadores'],

    data() {
      return {
        chartdata: {
          labels: [],
          datasets: [{
            label: '1º Lugar',
            backgroundColor: '#36A2EB',
            data: []
          },{
            label: '2º Lugar',
            backgroundColor: '#36C07B',
            data: []
          },{
            label: '3º Lugar',
            backgroundColor: '#FFCE56',
            data: []
          },{
            label: '4º Lugar',
            backgroundColor: '#FF6384',
            data: []
          },{
            label: '5º Lugar',
            backgroundColor: '#9966FF',
            data: []
          }],
        },
        options: {
          legend: {
            position: 'bottom'
          },
          tooltips: {
            mode: 'index',
            intersect: false
          },
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            xAxes: [{
              stacked: true,
            }],
            yAxes: [{
              stacked: true,
              ticks: {
                max: 100
              }
            }]
          }
        }
      }
    },

    watch: {
      jogadores(jogadores, old) {
        for (let jogador of jogadores) {
          this.chartdata.labels.push(jogador.nome);
          jogador.resultados.forEach(resultado => {
            this.chartdata.datasets[resultado.posicao - 1].data.push((100 * resultado.quantidade / jogador.num_partidas).toFixed(2));
          });
        }
        this.renderChart(this.chartdata, this.options);
      }
    },

    mounted() {
      this.renderChart(this.chartdata, this.options);
    }
  }
</script>
