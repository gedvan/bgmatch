<template>
  <div id="lista-jogos">

    <div class="filter-form bg-light">
      <div class="form-row">

        <div class="col-md-5 form-group form-group-categoria">
          <label>Categorias</label>
          <b-dropdown id="dd-categorias" :text="categoriasSelecionadas" ref="dropdown" variant="outline-secondary" class="d-flex align-items-start">
            <b-dropdown-form>
              <b-form-checkbox-group id="check-categorias" v-model="categorias" :options="opcoesCategorias" name="categorias" stacked></b-form-checkbox-group>
            </b-dropdown-form>
          </b-dropdown>
        </div>

        <div class="col-md-2 form-group">
          <label for="num-jogadores">Nº de Jogadores</label>
          <b-form-input type="number" v-model="num" id="num-jogadores" min="1"></b-form-input>
        </div>

        <div class="col-md-5 form-group form-group-order">
          <label>Ordenação</label>
          <b-form-checkbox v-model="sortInv" switch id="sort-inv">Inverter</b-form-checkbox>
          <b-form-select v-model="sort" :options="sortOptions"></b-form-select>
        </div>

      </div>
    </div>

    <div class="text-center mb-4">
      {{ jogosFiltrados.length }} {{ jogosFiltrados.length | plural('jogo', 'jogos') }}.
    </div>

    <ul class="grid">
      <li v-for="jogo in jogosFiltrados">
        <item-jogo :jogo="jogo" @on-click="selecionaJogo" />
      </li>
    </ul>

    <modal-jogo :jogo="jogoModal" />

    <div class="text-center my-4">
      <button class="btn btn-primary" @click="atualizaJogos" :disabled="atualizando">
        <font-awesome-icon icon="sync-alt" :class="{'fa-spin': atualizando}" />&nbsp;
        Atualizar acervo
      </button>
    </div>

  </div>
</template>

<script>
  import BGMatch from "../BGMatch";
  import ModalJogo from "../components/ModalJogo.vue";
  import ItemJogo from "../components/ItemJogo.vue";
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { library } from '@fortawesome/fontawesome-svg-core';
  import { faSyncAlt } from '@fortawesome/free-solid-svg-icons';

  library.add(faSyncAlt);

  export default {
    components: {
      ItemJogo,
      ModalJogo,
      FontAwesomeIcon
    },
    data() {
      return {
        BGMatch,

        // Lista de jogos carregados
        jogos: [],

        // Opções do filtro de categorias
        opcoesCategorias: [
          {value: 'P', text: 'Pesado'},
          {value: 'M', text: 'Médio'},
          {value: 'L', text: 'Leve'},
          {value: 'F', text: 'Party game'},
          {value: 'I', text: 'Infantil'},
          {value: 'C', text: 'Coop./Grupo'},
        ],

        // Filtro para os categorias de jogos exibidos
        categorias: ['P', 'M', 'L', 'F', 'I', 'C'],

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

        // Flag que indica se os jogos estão sendo atualizados
        atualizando: false,
      }
    },

    computed: {

      // Retorna o label para as categorias selecionadas
      categoriasSelecionadas: function() {
        if (this.categorias.length === this.opcoesCategorias.length) {
          return "Todas";
        }
        if (this.categorias.length === 0) {
          return "Nenhum";
        }
        return this.opcoesCategorias.filter(option => this.categorias.indexOf(option.value) > -1).map(option => option.text).join(', ');
      },

      // Retorna a lista dos jogos depois de aplicados os filtros
      jogosFiltrados: function() {
        return this.jogos
          .filter(jogo => this.categorias.indexOf(jogo.categoria) > -1)
          .filter(jogo => this.categorias.indexOf('C') > -1 || !jogo.coop)
          .filter(jogo => this.num === '' || (this.num >= jogo.min && this.num <= jogo.max))
          .sort(this.sortFunction());
      }

    },

    methods: {

      /**
       * Função para ordenar os jogos de acordo com o critério escolhido.
       */
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

      /**
       * Faz a requisição inicial ao backend para obter a lista de jogos.
       */
      inicializaJogos: function () {
        window.fetch(BGMatch.apiUrl + '/jogos')
          .then(response => response.json())
          .then(jogos => this.jogos = jogos)
          .catch(error => console.error(error));
      },

      /**
       * Abre um modal com os detalhes do jogo clicado.
       *
       * @param jogo
       */
      selecionaJogo: function (jogo) {
        this.jogoModal = jogo;
        this.$bvModal.show('modal-jogo');
      },

      /**
       * Atualiza a lista de jogos no banco de dados local, com base nos jogos do grupo na Ludopedia.
       */
      atualizaJogos: function () {
        if (window.confirm("A atualização do acervo consultará todos os jogos do grupo na Ludopedia e pode demorar alguns minutos. Deseja continuar?")) {
          this.atualizando = true;
          window.fetch(BGMatch.apiUrl + '/jogos/atualiza', {method: "POST"})
            .then(response => response.json())
            .then(jogos => this.jogos = jogos)
            .finally(() => this.atualizando = false)
            .catch(error => console.error(error));
        }
      }
    },

    created: function () {
      this.inicializaJogos();
    }
  }
</script>

<style lang="scss">
  @import "../scss/includes";

  #lista-jogos {

    .filter-form {
      padding: 17px 20px 7px;

      .form-group-categoria {
        .dropdown {
          .dropdown-toggle {
            text-align: left;
            background: white;
            border-color: $input-border-color;

            &::after {
              float: right;
              margin-top: .5rem;
            }
            &:hover, &:focus {
              color: $body-color;
            }
          }
          &.show > .dropdown-toggle {
            color: $body-color;
          }
        }
      }
      .form-group-order {
        position: relative;
        .custom-switch {
          float: right;
          font-size: 90%;
        }
        select[name=sort] {
          flex-grow: 2;
        }
      }
    }

    ul.grid {
      display: grid;
      margin: 0;
      padding: 0;
      grid-template-columns: repeat(2, 50%);
      grid-template-rows: auto;
      justify-content: space-between;

      @media (min-width: 576px) {
        grid-template-columns: repeat(3, 33.3%);
      }
      @media (min-width: 768px) {
        grid-template-columns: repeat(4, 25%);
      }
      @media (min-width: 992px) {
        grid-template-columns: repeat(5, 20%);
      }
      @media (min-width: 1200px) {
        grid-template-columns: repeat(6, 16.66%);
      }

      li {
        list-style: none;
        border: 1px solid transparent;
        border-radius: 3px;
        &:hover {
          border-color: $border-color;
        }
      }
    }

  }

</style>
