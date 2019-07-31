var BGMatch = {
  apiUrl: window.location.protocol + '//' + window.location.host + '/api',
  ludopediaUrl: 'https://www.ludopedia.com.br'
};

Vue.filter('plural', function(value, singular, plural) {
  return value === 1 ? singular : plural;
});

var listaJogos = new Vue({
  el: '#lista-jogos',

  data: {
    // Lista de jogos carregados
    jogos: [],

    // Opções dos selects de tipos de jogos
    tiposOptions: [
      { value: 'C', text: 'Cooperativo'},
      { value: 'E', text: 'Expert'},
      { value: 'I', text: 'Infantil'},
      { value: 'M', text: 'Médio'},
      { value: 'P', text: 'Party game'},
    ],

    // Filtro para os tipos de jogos exibidos
    tipos: ['C', 'E', 'I', 'M', 'P'],

    // Filtro pelo número de jogadores
    num: "",

    // Campo para ordenação
    sort: 'nome',

    // Flag para ordenação invertida
    sortInv: false,

    // Opções de ordenação
    sortOptions: [
      { text: 'Pelo nome', value: 'nome' },
      { text: 'Min. jogadores', value: 'min' },
      { text: 'Máx. jogadores', value: 'max' },
      { text: 'Última partida (não funcionando)', value: 'ult' },
      { text: 'Qtd. de partidas (não funcionando)', value: 'qtd' },
    ],

    // Jogo que está sendo exibido no modal
    jogoModal: null,

    // Flag que indica se o usuário está editando o tipo do jogo
    editandoTipo: false,

    // Tipo escolhido na edição
    tipoEdicao: '',

    // Flag que indica se os jogos estão sendo atualizados
    atualizando: false,
  },

  computed: {

    // Retorna o label para os tipos selecionados
    tiposSelecionados: function() {
      if (this.tipos.length === this.tiposOptions.length) {
        return "Todos";
      }
      if (this.tipos.length === 0) {
        return "Nenhum";
      }
      return this.tiposOptions.filter(option => this.tipos.indexOf(option.value) > -1).map(option => option.text).join(', ');
    },

    // Retorna a lista dos jogos depois de aplicados os filtros
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
        return jogo.min === 1 ? '1' : `${jogo.min}`;
      } else {
        return `${jogo.min} - ${jogo.max}`;
      }
    },

    urlJogoLudopedia: function(jogo) {
      return BGMatch.ludopediaUrl + '/jogo/' + jogo.slug;
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
        .then(jogos => this.jogos = jogos)
        .catch(error => console.error(error));
    },

    selecionaJogo: function(jogo) {
      this.jogoModal = jogo;
      this.tipoEdicao = jogo.tipo;
    },

    iniciaEdicaoTipo: function() {
      this.editandoTipo = true;
    },

    cancelaEdicaoTipo: function() {
      this.tipoEdicao = this.jogoModal.tipo;
      this.editandoTipo = false;
    },

    salvaEdicaoTipo: function() {

      const url = `${BGMatch.apiUrl}/jogos/${this.jogoModal.id_ludo}/tipo`;
      window.fetch(url, {
        method: "POST",
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({tipo: this.tipoEdicao})
      })
        .then(response => response.json())
        .then(data => {
          console.log('data', data);
        })
        .catch(error => console.error(error))
        .finally(() => {
          this.jogoModal.tipo = this.tipoEdicao;
          this.editandoTipo = false;
        });

    },

    atualizaJogos: function() {
      if (window.confirm("A atualização do acervo consultará todos os jogos do grupo na Ludopedia e pode demorar alguns minutos. Deseja continuar?")) {
        this.atualizando = true;
        window.fetch(BGMatch.apiUrl + '/jogos/ludopedia')
          .then(response => response.json())
          .then(jogos => {
            jogos.base.forEach(this.atualizaJogo);
            jogos.expansao.forEach(this.atualizaJogo);
          });
      }
    },

    atualizaJogo: function(slug, index, nomes) {
      window.fetch(BGMatch.apiUrl + '/jogos/atualiza/' + slug, {method: "POST"})
        .then(response => response.json())
        .then(jogo => {
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
