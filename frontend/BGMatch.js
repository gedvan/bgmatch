export default {

  apiUrl: window.location.protocol + '//' + window.location.host + '/api',

  ludopediaUrl: 'https://www.ludopedia.com.br',

  categoriasJogos: [
    {value: 'P', text: 'Pesado'},
    {value: 'M', text: 'Médio'},
    {value: 'L', text: 'Leve'},
    {value: 'F', text: 'Party game'},
    {value: 'I', text: 'Infantil'},
  ],

  urlJogoLudopedia: function (jogo) {
    return this.ludopediaUrl + '/jogo/' + jogo.slug;
  },

  nomeCategoria: function (key) {
    const find = this.categoriasJogos.findIndex(option => option.value === key);
    return find > -1 ? this.categoriasJogos[find].text : '';
  }

}
