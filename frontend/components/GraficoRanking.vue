<script>
  import { Line } from 'vue-chartjs';

  export default {
    extends: Line,

    props: ['jogadores', 'periodo'],

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

        if (this.periodo == 'semanal') {
          this.labels = Object.keys(this.jogadores[0].semanal)
            .map(d => d.split('-').reverse().join('/'));
        }
        else {
          this.labels = Object.keys(this.jogadores[0].mensal);
        }

        const datasetDefaults = {
          borderColor: '',
          label: '',
          data: [],
          lineTension: 0,
          backgroundColor: 'rgba(0, 0, 0, 0)',
          pointBackgroundColor: ''
        };

        this.datasets = this.jogadores.map(jogador => {
          let dataset = JSON.parse(JSON.stringify(datasetDefaults));
          dataset.label = jogador.nome;
          dataset.borderColor = jogador.cor;
          dataset.pointBackgroundColor = jogador.cor;
          dataset.data = Object.values(this.periodo === 'semanal' ? jogador.semanal : jogador.mensal);
          return dataset;
        });

        this.renderChart({labels: this.labels, datasets: this.datasets}, this.options)
      }
    },

    watch: {
      jogadores() {
        this.render();
      }
    },

    mounted () {
    }
  }
</script>
