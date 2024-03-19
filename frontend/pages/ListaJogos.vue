<template>
  <div id="lista-jogos">

    <div class="filter-form bg-alternate">
      <div class="container">

        <div class="form-row">

          <div class="col-sm-8 col-lg-4 form-group form-group-nome">
            <label for="filtro-nome">Nome</label>
            <b-form-input id="filtro-nome" v-model="filtros.nome" ref="nameFilter" />
            <b-button class="clear" variant="link" size="sm" v-if="filtros.nome !== ''"
                      @click="clearNameFilter"><font-awesome-icon icon="times" /></b-button>
          </div>

          <div class="col-sm-4 col-lg-2 form-group">
            <label for="filtro-num-jogadores">Nº de Jogadores</label>
            <b-form-input type="number" v-model="filtros.num" id="filtro-num-jogadores" min="1" placeholder="Qualquer" />
          </div>

          <div class="col-sm-6 col-lg-3 form-group form-group-categoria">
            <label>Categorias</label>
            <b-dropdown id="dd-categorias" :text="categoriasSelecionadas" ref="dropdown" variant="outline-secondary" class="d-flex align-items-start">
              <b-dropdown-form>
                <b-form-checkbox-group id="filtro-categorias" v-model="filtros.categorias" :options="opcoes.categorias" name="categorias" stacked></b-form-checkbox-group>
              </b-dropdown-form>
            </b-dropdown>
          </div>

          <div class="col-sm-6 col-lg-3 form-group form-group-order">
            <label>Ordenação</label>
            <b-form-select v-model="ordenacao.campo" :options="opcoes.ordenacao"></b-form-select>
          </div>

        </div>
        <div class="form-row">
          <div class="col-md-6 col-lg-4 form-group">
            <b-form-checkbox v-model="filtros.coop_grupo" switch id="filtro-coop-grupo">Exibir jogos cooperativos ou em grupo</b-form-checkbox>
          </div>
          <div class="col-md-6 col-lg-5 form-group">
            <b-form-checkbox v-model="filtros.excluidos" switch id="filtro-excluidos">Exibir jogos excluídos da coleção</b-form-checkbox>
          </div>
          <div class="col-md-6 col-lg-3 form-group">
            <b-form-checkbox v-model="ordenacao.inverter" switch id="sort-inv">Inverter ordenação</b-form-checkbox>
          </div>
        </div>

      </div>
    </div>

    <div class="container">

      <div class="info-listagem row my-3">
        <div class="col">
          Total de jogos cadastrados: {{ jogos.length }}
        </div>
        <div class="col text-center">
          Exibindo {{ jogosFiltrados.length }} {{ jogosFiltrados.length | plural('jogo', 'jogos') }}
        </div>
        <div class="col text-right">
          <b-button variant="primary" @click="abrirModalCadastro">
            <font-awesome-icon icon="plus" />&nbsp;Cadastrar jogo
          </b-button>
        </div>
      </div>

      <ul class="grid-jogos">
        <li v-for="jogo in jogosFiltrados">
          <item-jogo :jogo="jogo" @on-click="abreModalJogo" />
        </li>
      </ul>

    </div>

    <modal-jogo :jogo="jogoModal" />
    <modal-cadastrar-jogo @jogo-cadastrado="jogoCadastrado" />

  </div>
</template>

<script>
  import BGMatch from "../BGMatch";
  import ModalJogo from "../components/ModalJogo.vue";
  import ItemJogo from "../components/ItemJogo.vue";
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { library } from '@fortawesome/fontawesome-svg-core';
  import {faPlus, faSyncAlt, faTimes} from '@fortawesome/free-solid-svg-icons';
  import ModalCadastrarJogo from "../components/ModalCadastrarJogo";

  library.add(faSyncAlt);
  library.add(faPlus);
  library.add(faTimes);

  export default {
    components: {
      ModalCadastrarJogo,
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
          categorias: BGMatch.categoriasJogos.map(c => ({value: c.key, text: c.label})),

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
          nome: '',
          categorias: BGMatch.categoriasJogos.map(c => c.key),
          num: "",
          coop_grupo: true,
          excluidos: false,
        },

        // Critérios de ordenação
        ordenacao: {
          campo: 'nome',
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
          .filter(jogo => this.filtros.nome === '' || jogo.nome.toLocaleLowerCase().indexOf(this.filtros.nome.toLocaleLowerCase()) >= 0)
          .filter(jogo => this.filtros.categorias.indexOf(jogo.categoria) > -1)
          .filter(jogo => this.filtros.coop_grupo || !jogo.coop)
          .filter(jogo => this.filtros.excluidos || !jogo.excluido)
          .filter(jogo => this.filtros.num === '' || (this.filtros.num >= jogo.min && this.filtros.num <= jogo.max))
          .toSorted(this.sortFunction())
      },

      qtdJogosColecao: function () {
        return this.jogos.filter(jogo => !jogo.excluido).length;
      }

    },

    methods: {
      abrirModalCadastro() {
        this.$bvModal.show('modal-cadastrar-jogo')
      },

      clearNameFilter() {
        this.filtros.nome = '';
        this.$refs['nameFilter'].focus();
      },

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
        BGMatch.fetch('/jogos')
          .then(response => response.json())
          .then(jogos => this.jogos = jogos)
          .then(() => {
            // Processa expansões.
            this.jogos.forEach(jogo => {
              if (jogo.id_base) {
                const jogoBase = this.jogos.find(j => j.id === jogo.id_base);
                if (jogoBase) {
                  if (typeof jogoBase.expansoes === 'undefined') {
                    jogoBase.expansoes = [];
                  }
                  jogoBase.expansoes.push(jogo);
                  jogo.jogo_base = jogoBase;
                }
              }
            })
          })
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
          BGMatch.fetch('/jogos/atualiza', {method: "POST"})
            .then(response => response.json())
            .then(jogos => this.jogos = jogos)
            .finally(() => this.atualizando = false)
            .catch(error => {
              window.alert('Ocorreu um erro durante a atualização dos jogos.');
              console.error(error);
            });
        }
      },

      jogoCadastrado(event) {
        this.inicializaJogos()
      }
    },

    mounted() {
      this.inicializaJogos();
    }
  }
</script>
