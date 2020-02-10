<template>
  <div id="lista-jogos">

    <div class="filter-form bg-light">
      <div class="form-row">

        <div class="col-md-5 form-group form-group-categoria">
          <label>Categorias</label>
          <b-dropdown id="dd-categorias" :text="categoriasSelecionadas" ref="dropdown" variant="outline-secondary" class="d-flex align-items-start">
            <b-dropdown-form>
              <b-form-checkbox-group id="filtro-categorias" v-model="filtros.categorias" :options="opcoes.categorias" name="categorias" stacked></b-form-checkbox-group>
            </b-dropdown-form>
          </b-dropdown>
        </div>

        <div class="col-md-2 form-group">
          <label for="filtro-num-jogadores">Nº de Jogadores</label>
          <b-form-input type="number" v-model="filtros.num" id="filtro-num-jogadores" min="1" placeholder="Qualquer"></b-form-input>
        </div>

        <div class="col-md-5 form-group form-group-order">
          <label>Ordenação</label>
          <b-form-checkbox v-model="ordenacao.inverter" switch id="sort-inv">Inverter</b-form-checkbox>
          <b-form-select v-model="ordenacao.campo" :options="opcoes.ordenacao"></b-form-select>
        </div>

      </div>
      <div class="form-row">
        <div class="col-md-4 form-group">
          <b-form-checkbox v-model="filtros.coop_grupo" switch id="filtro-coop-grupo">Exibir jogos cooperativos ou em grupo</b-form-checkbox>
        </div>
        <div class="col-md-4 form-group">
          <b-form-checkbox v-model="filtros.excluidos" switch id="filtro-excluidos">Exibir jogos excluídos da coleção</b-form-checkbox>
        </div>
      </div>
    </div>

    <div class="row my-3">
      <div class="col">
        Total de jogos na coleção: {{ qtdJogosColecao }}
      </div>
      <div class="col text-center">
        Exibindo {{ jogosFiltrados.length }} {{ jogosFiltrados.length | plural('jogo', 'jogos') }}
      </div>
      <div class="col text-right">
        <button class="btn btn-primary" @click="atualizaJogos" :disabled="atualizando">
          <font-awesome-icon icon="sync-alt" :class="{'fa-spin': atualizando}" />&nbsp;
          Atualizar acervo
        </button>
      </div>
    </div>

    <ul class="grid">
      <li v-for="jogo in jogosFiltrados">
        <item-jogo :jogo="jogo" @on-click="abreModalJogo" />
      </li>
    </ul>

    <modal-jogo :jogo="jogoModal" />

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

        // Lista de jogos carregados
        jogos: [],

        opcoes: {
          // Lista de opções do filtro de categorias
          categorias: [
            {value: 'P', text: 'Pesado'},
            {value: 'M', text: 'Médio'},
            {value: 'L', text: 'Leve'},
            {value: 'F', text: 'Party game'},
            {value: 'I', text: 'Infantil'}
          ],
          // Lista de opções do critério de ordenação
          ordenacao: [
            {text: 'Pelo nome', value: 'nome'},
            {text: 'Última partida', value: 'ult'},
            {text: 'Qtd. de partidas', value: 'qtd'},
            {text: 'Min. jogadores', value: 'min'},
            {text: 'Máx. jogadores', value: 'max'},
          ]
        },

        // Conjunto de filtros da listagem de jogos
        filtros: {

          // Categorias de jogos exibidos
          categorias: ['P', 'M', 'L', 'F', 'I'],

          // Filtro pelo número de jogadores
          num: "",

          // Exibir ou não os jogos cooperativos ou em grupo
          coop_grupo: true,

          // Exibir ou não os jogos excluídos da coleção
          excluidos: false,
        },

        // Critérios de ordenação
        ordenacao: {

          // Campo para ordenação
          campo: 'nome',

          // Flag para ordenação invertida
          inverter: false,
        },

        // Jogo que está sendo exibido no modal
        jogoModal: null,

        // Flag que indica se os jogos estão sendo atualizados
        atualizando: false,
      }
    },

    computed: {

      // Retorna o label para as categorias selecionadas
      categoriasSelecionadas: function() {
        if (this.filtros.categorias.length === this.opcoes.categorias.length) {
          return "Todas";
        }
        if (this.filtros.categorias.length === 0) {
          return "Nenhum";
        }
        return this.opcoes.categorias.filter(option => this.filtros.categorias.indexOf(option.value) > -1).map(option => option.text).join(', ');
      },

      // Retorna a lista dos jogos depois de aplicados os filtros
      jogosFiltrados: function() {
        return this.jogos
          .filter(jogo => this.filtros.categorias.indexOf(jogo.categoria) > -1)
          .filter(jogo => this.filtros.coop_grupo || !jogo.coop)
          .filter(jogo => this.filtros.excluidos || !jogo.excluido)
          .filter(jogo => this.filtros.num === '' || (this.filtros.num >= jogo.min && this.filtros.num <= jogo.max))
          .sort(this.sortFunction())
          .map(jogo => {
            if (jogo.expansoes.length > 0) {
              const m = [jogo.max].concat(jogo.expansoes.map(e => e.max));
              jogo.max = Math.max.apply(null, m);
            }
            return jogo;
          });
      },

      qtdJogosColecao: function () {
        return this.jogos.filter(jogo => !jogo.excluido).length;
      }

    },

    methods: {

      /**
       * Função para ordenar os jogos de acordo com o critério escolhido.
       */
      sortFunction: function () {
        if (this.ordenacao.campo === 'nome') {
          return (jogoA, jogoB) => this.ordenacao.inverter ?
            jogoB.nome.toLocaleLowerCase().localeCompare(jogoA.nome.toLocaleLowerCase()) :
            jogoA.nome.toLocaleLowerCase().localeCompare(jogoB.nome.toLocaleLowerCase());
        }
        else if (this.ordenacao.campo === 'min') {
          return (jogoA, jogoB) => this.ordenacao.inverter ? jogoB.min - jogoA.min : jogoA.min - jogoB.min;
        }
        else if (this.ordenacao.campo === 'max') {
          return (jogoA, jogoB) => this.ordenacao.inverter ? jogoB.max - jogoA.max : jogoA.max - jogoB.max;
        }
        else if (this.ordenacao.campo === 'ult') {
          return (jogoA, jogoB) => {
            let dataA = jogoA.ultima_partida ? jogoA.ultima_partida : '';
            let dataB = jogoB.ultima_partida ? jogoB.ultima_partida : '';
            return this.ordenacao.inverter ? dataA.localeCompare(dataB) : dataB.localeCompare(dataA);
          };
        }
        else if (this.ordenacao.campo === 'qtd') {
          return (jogoA, jogoB) => this.ordenacao.inverter ? jogoA.num_partidas - jogoB.num_partidas : jogoB.num_partidas - jogoA.num_partidas;
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
      abreModalJogo: function (jogo) {
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
            .catch(error => {
              window.alert('Ocorreu um erro durante a atualização dos jogos.');
              console.error(error);
            });
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
