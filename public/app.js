var BGMatch = {
  apiUrl: 'http://localhost:8000/api'
};

var listaJogos = new Vue({
  el: '#lista-jogos',

  data: {
    jogos: [],

    atualizando: false,

    tipos: [{
        key: "I",
        nome: "Infantil",
        checked: true
      }, {
        key: "C",
        nome: "Cooperativo",
        checked: true
      }, {
        key: "P",
        nome: "Party game",
        checked: true
      }, {
        key: "M",
        nome: "Médio",
        checked: true
      }, {
        key: "E",
        nome: "Expert",
        checked: true
      }
    ],

    num: 4,

    sort: 'alfa',
    sort_dir: 'asc'
  },

  computed: {

    tiposSelecionados: function() {
      const checked = this.tipos.filter(tipo => tipo.checked);
      if (checked.length === this.tipos.length) {
        return "Todos";
      }
      if (checked.length === 0) {
        return "Nenhum";
      }
      return checked.map(tipo => tipo.nome).join(", ");
    },

    jogosFiltrados: function() {
      const tipos = this.tipos.filter(tipo => tipo.checked).map(tipo => tipo.key);
      return this.jogos
        .filter(jogo => tipos.indexOf(jogo.tipo) > -1)
        .filter(jogo => this.num >= jogo.min && this.num <= jogo.max);
    }
  },

  methods: {

    nomeTipo: function(key) {
      let tipo = this.tipos.filter(tipo => tipo.key === key);
      return tipo.length ? tipo[0].nome : '';
    },

    numJogadores: function(jogo) {
      if (jogo.min === jogo.max) {
        return jogo.min > 1 ? `${jogo.min} jogadores` : '1 jogador';
      } else {
        return `${jogo.min} a ${jogo.max} jogadores`;
      }
    },

    inicializaJogos: function() {
      window.fetch(BGMatch.apiUrl + '/jogos')
        .then(response => response.json())
        .then(jogos => this.jogos = jogos);
    },

    atualizaLista: function() {

    },

    atualizaJogos: function() {
      if (window.confirm("A atualização do acervo consultará todos os jogos do grupo na Ludopedia e pode demorar alguns minutos. Deseja continuar?")) {
        this.atualizando = true;
        window.fetch(BGMatch.apiUrl + '/jogos/ludopedia')
          .then(response => response.json())
          .then(jogos => jogos.forEach(this.atualizaJogo));
      }
    },

    atualizaJogo: function(nome, index, nomes) {
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
    this.inicializaJogos();
  }

});
