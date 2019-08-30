export default {

  apiUrl: window.location.protocol + '//' + window.location.host + '/api',

  ludopediaUrl: 'https://www.ludopedia.com.br',

  tiposJogos: [
    {value: 'C', text: 'Cooperativo'},
    {value: 'E', text: 'Expert'},
    {value: 'I', text: 'Infantil'},
    {value: 'M', text: 'Médio'},
    {value: 'P', text: 'Party game'},
  ],

  urlJogoLudopedia: function (jogo) {
    return this.ludopediaUrl + '/jogo/' + jogo.slug;
  },

  nomeTipo: function (key) {
    const find = this.tiposJogos.findIndex(option => option.value === key);
    return find > -1 ? this.tiposJogos[find].text : '';
  }

}
