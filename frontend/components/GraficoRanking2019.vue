<script>
  import { Line } from 'vue-chartjs';

  export default {
    extends: Line,

    props: ['partidas', 'jogadores', 'periodo'],

    data() {
      return {
        labels: [],
        datasets: [],
        options: {
          legend: {
            position: 'bottom'
          },
          responsive: true,
          maintainAspectRatio: false
        }
      }
    },

    methods: {
      render() {
        if (!this.jogadores) {
          return;
        }

        // if (this.periodo === 'semanal') {
        //   this.labels = this.partidas.map(partida => partida.data.substring(5))
        //     .sort().map(d => d.split('-').reverse().join('/'));
        // }
        // else {
        //   this.labels = Object.keys(this.jogadores[0].mensal);
        // }

        const datasetDefaults = {
          borderColor: '',
          label: '',
          data: [],
          lineTension: 0,
          backgroundColor: 'rgba(0, 0, 0, 0)',
          pointBackgroundColor: ''
        };

        this.labels = [];
        this.datasets = [];

        this.datasets = this.jogadores.map(jogador => {
          let dataset = JSON.parse(JSON.stringify(datasetDefaults));
          dataset.label = jogador.nome;
          dataset.borderColor = jogador.cor;
          dataset.pointBackgroundColor = jogador.cor;
          dataset.data = [];
          return dataset;
        });

        // Pega todas as datas em que houve partidas (sem duplicatas).
        const datas = ['01-01'];
        this.partidas.forEach(partida => {
          const data = partida.data.substring(5);
          if (datas.indexOf(data) === -1) {
            datas.push(data);
          }
        });

        datas.forEach((data, d) => {
          this.labels.push(data.split('-').reverse().join('/'));
          this.datasets.forEach((dataset, i) => {
            const pontosAnterior = d > 0 ? dataset.data[d - 1] : 0;
            const pontos = typeof this.jogadores[i].semanal[data] !== 'undefined'
              ? this.jogadores[i].semanal[data] : 0;
            dataset.data.push(pontosAnterior + pontos);
          });
        });

        this.renderChart({labels: this.labels, datasets: this.datasets}, this.options)
      }
    },

    mounted() {
      this.render();
    }
  }
</script>
