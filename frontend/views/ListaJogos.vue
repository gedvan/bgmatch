<template>
  <div id="lista-jogos">

    <div class="filter-form bg-light">
      <div class="form-row">

        <div class="col-md-5 form-group form-group-tipo">
          <label>Tipos</label>
          <b-dropdown id="dd-tipos" :text="tiposSelecionados" ref="dropdown" variant="outline-secondary" class="d-flex align-items-start">
            <b-dropdown-form>
              <b-form-checkbox-group id="check-tipos" v-model="tipos" :options="BGMatch.tiposJogos" name="tipos" stacked></b-form-checkbox-group>
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
      <button class="btn btn-primary" @click="atualizaJogos" v-bind:disabled="atualizando">Atualizar acervo</button>
    </div>

  </div>
</template>

<script>
  import BGMatch from "../BGMatch";
  import ModalJogo from "../components/ModalJogo.vue";
  import ItemJogo from "../components/ItemJogo.vue";

  export default {
    components: {
      ItemJogo,
      ModalJogo
    },
    data() {
      return {
        BGMatch,

        // Lista de jogos carregados
        jogos: [],

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

        // Flag que indica se os jogos estão sendo atualizados
        atualizando: false,
      }
    },

    computed: {

      // Retorna o label para os tipos selecionados
      tiposSelecionados: function() {
        if (this.tipos.length === BGMatch.tiposJogos.length) {
          return "Todos";
        }
        if (this.tipos.length === 0) {
          return "Nenhum";
        }
        return BGMatch.tiposJogos.filter(option => this.tipos.indexOf(option.value) > -1).map(option => option.text).join(', ');
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
        this.$bvModal.show('modal-jogo');
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

<style lang="scss">
  @import "../scss/includes";

  #lista-jogos {

    .filter-form {
      padding: 17px 20px 7px;

      .form-group-tipo {
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
