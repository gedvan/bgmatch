var BGMatch = {
  apiUrl: 'http://localhost:8000/api'
};

var listaJogos = new Vue({
  el: '#lista-jogos',
  data: {
    jogos: [],
    atualizando: false
  },
  methods: {

    atualizaJogos: function() {
      this.atualizando = true;
      window.fetch(BGMatch.apiUrl + '/jogos/ludopedia')
        .then(response => response.json())
        .then(jogos => jogos.forEach(this.atualizaJogo));
    },

    atualizaJogo(nome, index, nomes) {
      window.fetch(BGMatch.apiUrl + '/jogos/atualiza/' + nome, {method: "POST"})
        .then(response => response.json())
        .then(jogo => console.log(jogo))
        .then(() => {
          if (index === nomes.length - 1) {
            this.atualizando = false;
          }
        });
    }

  },
  created: function () {
    window.fetch(BGMatch.apiUrl + '/jogos')
      .then(response => response.json())
      .then(jogos => this.jogos = jogos);
  }
});
