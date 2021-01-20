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
            backgroundColor: '#0073cc',
            data: []
          },{
            label: '2º Lugar',
            backgroundColor: '#0d8ff2',
            data: []
          },{
            label: '3º Lugar',
            backgroundColor: '#47a4eb',
            data: []
          },{
            label: '4º Lugar',
            backgroundColor: '#7dbae8',
            data: []
          },{
            label: '5º Lugar',
            backgroundColor: '#add2eb',
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

    methods: {
    },

    mounted() {
      this.chartdata.labels = this.jogadores.map(j => j.nome);
      this.chartdata.datasets.forEach((dataset, i) => {
        dataset.data = this.jogadores.map(jogador => {
          const resultado = jogador.resultados.find(r => r.posicao === (i+1));
          if (resultado && jogador.num_partidas > 0) {
            return (100 * resultado.quantidade / jogador.num_partidas).toFixed(2)
          }
          return 0;
        })
      });
      this.renderChart(this.chartdata, this.options);
    }
  }
</script>
