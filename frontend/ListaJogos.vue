<template>
  <div id="lista-jogos">

    <div class="toolbar border rounded mb-3 px-4 pt-4 pb-2 bg-light">
      <div class="row">

        <div class="col-md-5 form-group">
          <label>Tipos</label>
          <b-dropdown id="dd-tipos" :text="tiposSelecionados" ref="dropdown" variant="outline-secondary" class="d-flex align-items-start">
            <b-dropdown-form>
              <b-form-checkbox-group id="check-tipos" v-model="tipos" :options="tiposOptions" name="tipos" stacked></b-form-checkbox-group>
            </b-dropdown-form>
          </b-dropdown>
        </div>

        <div class="col-md-2 form-group">
          <label for="num-jogadores">Nº de Jogadores</label>
          <b-form-input type="number" v-model="num" id="num-jogadores" min="1"></b-form-input>
        </div>

        <div class="col-md-5 form-group">
          <label>Ordenação</label>
          <div class="input-group">
            <b-form-select v-model="sort" :options="sortOptions"></b-form-select>
            <b-form-checkbox v-model="sortInv" switch id="sort-inv">Inverter</b-form-checkbox>
          </div>
        </div>

      </div>
    </div>

    <div class="text-center mb-4">
      {{ jogosFiltrados.length }} {{ jogosFiltrados.length | plural('jogo', 'jogos') }}.
    </div>

    <ul class="grid">
      <li v-for="jogo in jogosFiltrados">
        <a href="#" v-b-modal.modal-jogo @click.prevent="selecionaJogo(jogo)">
          <img :src="jogo.img_ludo">
        </a>
        <h3>{{ jogo.nome }}</h3>
        <div class="text-muted small">
          <i class="fas fa-user-friends"></i> {{ numJogadores(jogo) }}
          / {{ nomeTipo(jogo.tipo) }}
        </div>
      </li>
    </ul>

    <b-modal id="modal-jogo" size="lg" hide-footer>
      <template #modal-title>
        {{ jogoModal.nome }}
        <b-link :href="urlJogoLudopedia(jogoModal)" target="_blank" class="small" title="Abrir na Ludopedia">
          <i class="fas fa-external-link-alt"></i>
        </b-link>
      </template>
      <div v-if="jogoModal" class="media">
        <img :src="jogoModal.img_ludo" class="mr-3">
        <div class="media-body">
          <dl class="row">
            <dt class="col-sm-3">Nº de jogadores</dt>
            <dd class="col-sm-9">{{ numJogadores(jogoModal) }}</dd>
            <dt class="col-sm-3">Categoria</dt>
            <dd class="col-sm-9">
              <span v-if="!editandoTipo">{{ nomeTipo(jogoModal.tipo) }}</span>
              <b-form-select v-if="editandoTipo" v-model="tipoEdicao" :options="tiposOptions" size="sm" class="w-auto"></b-form-select>
              <b-button v-if="!editandoTipo" variant="link" size="sm" @click="iniciaEdicaoTipo"><i class="far fa-edit"></i></b-button>
              <b-button-group v-if="editandoTipo" size="sm">
                <b-button variant="outline-success" @click="salvaEdicaoTipo"><i class="far fa-check-circle"></i></b-button>
                <b-button variant="outline-danger" @click="cancelaEdicaoTipo"><i class="far fa-times-circle"></i></b-button>
              </b-button-group>
            </dd>
            <template v-if="jogoModal.expansoes.length">
              <dt class="col-sm-3">Expansões</dt>
              <dd class="col-sm-9" v-if="jogoModal.expansoes.length">
                <ul class="m-0 pl-3">
                  <li v-for="exp in jogoModal.expansoes">
                    {{ exp.nome }}
                    <b-link :href="urlJogoLudopedia(exp)" target="_blank" class="small" title="Abrir na Ludopedia">
                      <i class="fas fa-external-link-alt"></i>
                    </b-link>
                  </li>
                </ul>
              </dd>
            </template>
          </dl>
        </div>
      </div>
    </b-modal>

    <div class="text-center my-4">
      <button class="btn btn-primary" @click="atualizaJogos" v-bind:disabled="atualizando">Atualizar acervo</button>
    </div>

  </div>
</template>

<script>
  export default {
    data() {
      return {
        // Lista de jogos carregados
        jogos: [],

        // Opções dos selects de tipos de jogos
        tiposOptions: [
          {value: 'C', text: 'Cooperativo'},
          {value: 'E', text: 'Expert'},
          {value: 'I', text: 'Infantil'},
          {value: 'M', text: 'Médio'},
          {value: 'P', text: 'Party game'},
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
          {text: 'Pelo nome', value: 'nome'},
          {text: 'Min. jogadores', value: 'min'},
          {text: 'Máx. jogadores', value: 'max'},
          {text: 'Última partida (não funcionando)', value: 'ult'},
          {text: 'Qtd. de partidas (não funcionando)', value: 'qtd'},
        ],

        // Jogo que está sendo exibido no modal
        jogoModal: null,

        // Flag que indica se o usuário está editando o tipo do jogo
        editandoTipo: false,

        // Tipo escolhido na edição
        tipoEdicao: '',

        // Flag que indica se os jogos estão sendo atualizados
        atualizando: false,
      }
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

      nomeTipo: function (key) {
        const find = this.tiposOptions.findIndex(option => option.value === key);
        return find > -1 ? this.tiposOptions[find].text : '';
      },

      numJogadores: function (jogo) {
        if (jogo.min === jogo.max) {
          return jogo.min === 1 ? '1' : `${jogo.min}`;
        } else {
          return `${jogo.min} - ${jogo.max}`;
        }
      },

      urlJogoLudopedia: function (jogo) {
        return BGMatch.ludopediaUrl + '/jogo/' + jogo.slug;
      },

      sortFunction: function () {
        if (this.sort === 'nome') {
          return (jogoA, jogoB) => this.sortInv ?
            jogoB.nome.toLocaleLowerCase().localeCompare(jogoA.nome.toLocaleLowerCase()) :
            jogoA.nome.toLocaleLowerCase().localeCompare(jogoB.nome.toLocaleLowerCase());
        } else if (this.sort === 'min') {
          return (jogoA, jogoB) => this.sortInv ? jogoB.min - jogoA.min : jogoA.min - jogoB.min;
        } else if (this.sort === 'max') {
          return (jogoA, jogoB) => this.sortInv ? jogoB.max - jogoA.max : jogoA.max - jogoB.max;
        }
      },

      inicializaJogos: function () {
        window.fetch(BGMatch.apiUrl + '/jogos')
          .then(response => response.json())
          .then(jogos => this.jogos = jogos)
          .catch(error => console.error(error));
      },

      selecionaJogo: function (jogo) {
        this.jogoModal = jogo;
        this.tipoEdicao = jogo.tipo;
      },

      iniciaEdicaoTipo: function () {
        this.editandoTipo = true;
      },

      cancelaEdicaoTipo: function () {
        this.tipoEdicao = this.jogoModal.tipo;
        this.editandoTipo = false;
      },

      salvaEdicaoTipo: function () {

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

      atualizaJogos: function () {
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

      atualizaJogo: function (slug, index, nomes) {
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
  }
</script>
