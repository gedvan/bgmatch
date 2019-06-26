var BGMatch = {
  baseUrl: 'http://localhost:8000/api'
};

var listaJogos = new Vue({
  el: '#lista-jogos',
  data: {
    jogos: []
  },
  created: function () {
    window.fetch(BGMatch.baseUrl + '/jogos')
      .then(response => response.json())
      .then(jogos => this.jogos = jogos);
  }
});
