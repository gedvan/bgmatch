var BGMatch = {
  apiUrl: 'http://localhost:8000/api'
};

var listaJogos = new Vue({
  el: '#lista-jogos',

  data: {
    jogos: [],

    atualizando: false,

    tipos: ['C', 'E', 'I', 'M', 'P'],
    tiposOptions: [
      { value: 'C', text: 'Cooperativo'},
      { value: 'E', text: 'Expert'},
      { value: 'I', text: 'Infantil'},
      { value: 'M', text: 'Médio'},
      { value: 'P', text: 'Party game'},
    ],

    num: "",

    sort: 'nome',
    sortOptions: [
      { text: 'Pelo nome', value: 'nome' },
      { text: 'Min. jogadores', value: 'min' },
      { text: 'Máx. jogadores', value: 'max' },
      { text: 'Última partida (não funcionando)', value: 'ult' },
      { text: 'Qtd. de partidas (não funcionando)', value: 'qtd' },
    ],

    sortInv: false
  },

  computed: {

    tiposSelecionados: function() {
      if (this.tipos.length === this.tiposOptions.length) {
        return "Todos";
      }
      if (this.tipos.length === 0) {
        return "Nenhum";
      }
      return this.tiposOptions.filter(option => this.tipos.indexOf(option.value) > -1).map(option => option.text).join(', ');
    },

    jogosFiltrados: function() {
      return this.jogos
        .filter(jogo => this.tipos.indexOf(jogo.tipo) > -1)
        .filter(jogo => this.num === '' || (this.num >= jogo.min && this.num <= jogo.max))
        .sort(this.sortFunction());
    }
  },

  methods: {

    nomeTipo: function(key) {
      const find = this.tiposOptions.findIndex(option => option.value === key);
      return find > -1 ? this.tiposOptions[find].text : '';
    },

    numJogadores: function(jogo) {
      if (jogo.min === jogo.max) {
        return jogo.min === 1 ? '1 jogador' : `${jogo.min} jogadores`;
      } else {
        return `${jogo.min} a ${jogo.max} jogadores`;
      }
    },

    sortFunction: function() {
      if (this.sort === 'nome') {
        return (jogoA, jogoB) => this.sortInv ?
          jogoB.nome.toLocaleLowerCase().localeCompare(jogoA.nome.toLocaleLowerCase()) :
          jogoA.nome.toLocaleLowerCase().localeCompare(jogoB.nome.toLocaleLowerCase());
      }
      else if (this.sort === 'min') {
        return (jogoA, jogoB) => this.sortInv ? jogoB.min - jogoA.min : jogoA.min - jogoB.min;
      }
      else if (this.sort === 'max') {
        return (jogoA, jogoB) => this.sortInv ? jogoB.max - jogoA.max : jogoA.max - jogoB.max;
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
