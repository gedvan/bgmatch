<script>
  import { Pie } from 'vue-chartjs';

  export default {
    extends: Pie,
    props: ['labels', 'values'],

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
        return {
          labels: this.labels,
          datasets: [{
            data: this.values,
            backgroundColor: this.colors
          }]
        }
      }
    },

    watch: {
      values() {
        this.renderChart(this.chartdata, this.options)
      }
    },

    mounted() {
      this.renderChart(this.chartdata, this.options)
    }
  }
</script>
