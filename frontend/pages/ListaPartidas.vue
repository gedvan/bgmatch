<template>
  <div id="lista-partidas">

    <div class="filter-form bg-alternate">
      <div class="container">
        <div class="form-row">

          <b-form-group label="Período" label-for="input-data" class="col-md-4">
            <b-input-group>
              <b-form-input id="input-data" v-model="filtros.data_inicial" type="date" required placeholder="DD/MM/AAAA" />
              <b-form-input id="input-data" v-model="filtros.data_final" type="date" required placeholder="DD/MM/AAAA" />
            </b-input-group>
            <b-button v-for="periodo in periodos" :key="periodo.key" size="sm" variant="link"
                      :class="{'periodo-ativo': (periodo.inicial == filtros.data_inicial && periodo.final == filtros.data_final)}"
                      @click="filtroPeriodo(periodo.inicial, periodo.final)">{{ periodo.label }}</b-button>
          </b-form-group>

          <b-form-group label="Jogo" label-for="input-jogo" class="col-md-4">
            <v-select id="input-jogo" v-model="filtros.jogo" :options="opcoes.jogos" />
          </b-form-group>

          <b-form-group label="Jogador" label-for="filtro-jogador" class="col-md-4">
            <b-form-select id="filtro-jogador" :options="opcoes.jogadores" v-model="filtros.jogador"></b-form-select>
          </b-form-group>

        </div>
      </div>
    </div>

    <div class="container">

      <div class="row my-3">
        <div class="col">
          Total de partidas no período: {{ partidas.length }}
          <template v-if="partidas.length !== partidasFiltradas.length">
            (exibindo {{ partidasFiltradas.length }})
          </template>
        </div>
        <div class="col text-right">
          <b-button @click="abrirFormPartida(null)" class="ml-auto" variant="primary">
            <font-awesome-icon icon="plus" />&nbsp;Cadastrar partida
          </b-button>
        </div>
      </div>

      <div class="table-responsive">
        <table v-if="partidasFiltradas.length > 0" class="table table-striped table-sm">
          <thead>
          <tr>
            <th>
              <a href="#" @click="ordenarPor('data')">
                Data <font-awesome-icon v-if="ordenacao.campo == 'data'" :icon="sortIcon" />
              </a>
            </th>
            <th>
              <a href="#" @click="ordenarPor('jogo')">
                Jogo <font-awesome-icon v-if="ordenacao.campo == 'jogo'" :icon="sortIcon" />
              </a>
            </th>
            <th>
              <a href="#" @click="ordenarPor('peso')">
                Peso <font-awesome-icon v-if="ordenacao.campo == 'peso'" :icon="sortIcon" />
              </a>
            </th>
            <th>
              <a href="#" @click="ordenarPor('local')">
                Local <font-awesome-icon v-if="ordenacao.campo == 'local'" :icon="sortIcon" />
              </a>
            </th>
            <th>Jogadores</th>
            <th class="text-center" width="5%">Ações</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(partida, index) in partidasFiltradas">
            <td>
              {{ partida.data | data_br }}
            </td>
            <td>
              {{ partida.expansao ? partida.expansao.nome : partida.jogo.nome }}
            </td>
            <td class="text-muted small align-middle">
              {{ getPesoPartida(partida) }}
            </td>
            <td>
              {{ partida.local }}
            </td>
            <td>
              <template v-for="(jogador, i) in partida.jogadores">
                <span :class="['jogador', 'posicao-' + jogador.posicao]">
                  <font-awesome-icon icon="medal" v-if="jogador.posicao <= 3" />
                  {{ jogador.nome + (i < partida.jogadores.length - 1 ? ',' : '')}}
                </span>{{ ' ' }}
              </template>
            </td>
            <td class="text-center text-nowrap">
              <b-button variant="link" size="sm" @click="abrirFormPartida(partida)">
                <font-awesome-icon icon="edit" />
              </b-button>
              <b-button variant="link" size="sm" @click="excluirPartida(partida)">
                <font-awesome-icon icon="trash" />
              </b-button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

    </div>

    <form-partida :partida="partidaEdicao" @updated="fetchPartidas" />
  </div>
</template>

<script>
  import BGMatch from "../BGMatch";
  import FormPartida from "../components/FormPartida.vue";
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

  export default {
    components: {
      FontAwesomeIcon,
      FormPartida
    },

    data() {
      return {
        // Lista de todas as partidas cadastradas
        partidas: [],

        // Lista de filtros para as partidas exibidas
        filtros: {
          data_inicial: '',
          data_final: '',
          jogo: null,
          jogador: 0
        },

        // Opções dos campos de filtro
        opcoes: {
          jogos: [],
          jogadores: [{value: 0, text: '-- Quaisquer --'}]
        },

        // Critérios de ordenação
        ordenacao: {
          campo: 'data',
          inverter: false,
        },

        // Lista de períodos pré-definidos (anos)
        periodos: [],

        // Partida sendo editada
        partidaEdicao: null,

        // Flag de controle para evitar requisição dupla ao clicar nos anos.
        preventFetchPartidas: false,
      }
    },

    computed: {
      partidasFiltradas() {
        return this.partidas
          .filter(partida => {
            if (this.filtros.jogo) {
              return partida.jogo.id === this.filtros.jogo.code
                || (partida.expansao && partida.expansao.id == this.filtros.jogo.code);
            }
            return true
          })
          .filter(partida => {
            if (this.filtros.jogador > 0) {
              return !!partida.jogadores.find(j => j.id === this.filtros.jogador);
            }
            return true;
          })
          .toSorted(this.sortFunction);
      },

      filtroDataInicial() {
        return this.filtros.data_inicial;
      },

      filtroDataFinal() {
        return this.filtros.data_final;
      },

      sortIcon() {
        return this.ordenacao.inverter ? 'sort-up' : 'sort-down'
      }
    },

    created() {
      this.initPeriodos();
      this.fetchJogos();
      this.fetchJogadores();
      this.fetchPartidas();
    },

    watch: {
      filtroDataInicial(val, old) {
        if (!this.preventFetchPartidas) {
          this.fetchPartidas();
        }
      },

      filtroDataFinal(val, old) {
        if (!this.preventFetchPartidas) {
          this.fetchPartidas();
        }
      }
    },

    methods: {
      /**
       * Inicializa lista de períodos pré-definidos (anos) e os valores
       * iniciais das datas.
       */
      initPeriodos() {
        const anoInicial = 2018;
        const anoAtual = new Date().getFullYear();
        for (let ano = anoAtual; ano >= anoInicial; ano--) {
          this.periodos.push({
            key: ano,
            label: ano,
            inicial: `${ano}-01-01`,
            final: `${ano}-12-31`
          })
        }
        this.periodos.push({
          key: 'all',
          label: 'Tudo',
          inicial: '',
          final: ''
        });

        this.filtros.data_inicial = `${anoAtual}-01-01`;
        this.filtros.data_final = `${anoAtual}-12-31`;
      },

      filtroPeriodo(inicial, final) {
        this.preventFetchPartidas = true;
        this.filtros.data_inicial = inicial;
        this.filtros.data_final = final;
        this.fetchPartidas().then(() => {
          this.preventFetchPartidas = false;
        });
      },

      /**
       * Abre o modal com o formulário de cadastro/edição de uma partida.
       *
       * @param partida
       */
      abrirFormPartida: function (partida) {
        this.partidaEdicao = partida;
        this.$bvModal.show('modal-partida');
      },

      /**
       * Método auxiliar para exibir os nomes dos jogadores da partida.
       *
       * @param jogadores
       * @returns {string}
       */
      nomesJogadores: function (jogadores) {
        return jogadores.map(j => j.vencedor ? `<u><font-awesome-icon icon="trophy" /> ${j.nome}</u>` : j.nome).join(', ');
      },

      ordenarPor: function(campo) {
        if (this.ordenacao.campo === campo) {
          this.ordenacao.inverter = !this.ordenacao.inverter;
        }
        else {
          this.ordenacao.campo = campo;
          this.ordenacao.inverter = false;
        }
      },

      getPesoPartida(partida) {
        return partida.expansao && partida.expansao.peso
          ? partida.expansao.peso
          : partida.jogo.peso;
      },

      sortFunction(p1, p2) {
        let fieldFn;
        let inv = this.ordenacao.inverter;
        let sortFn = (a, b) => inv ? b.localeCompare(a) : a.localeCompare(b);

        switch(this.ordenacao.campo) {
          case 'data':
            fieldFn = (p) => p.data;
            inv = !inv;
            break;

          case 'jogo':
            fieldFn = (p) => p.jogo.nome.toLocaleLowerCase();
            break;

          case 'peso':
            fieldFn = this.getPesoPartida;
            sortFn = (a, b) => inv ? a - b : b - a;
            break;

          case 'local':
            fieldFn = (p) => p.local;
            break;

          default:
            return null;
        }

        return sortFn(fieldFn(p1), fieldFn(p2));
      },

      excluirPartida: function (partida) {
        if (window.confirm(`Deseja realmente excluir a partida de ${partida.nome_jogo} de ${partida.data}?`)) {
          BGMatch.fetch(`/partidas/${partida.id}/delete`, {method: 'POST'})
            .then(response => response.json())
            .then(data => {
              this.fetchPartidas();
            })
            .catch(error => {
              window.alert('Ocorreu um erro ao excluir a partida.');
              console.error(error);
            });
        }
      },

      /**
       * Atualiza a lista de partidas com base nos dados do servidor.
       */
      fetchPartidas: function () {
        let path = '/partidas-periodo';
        if (this.filtros.data_inicial || this.filtros.data_final) {
          path += '/' + this.filtros.data_inicial + ':' + this.filtros.data_final;
        }
        return BGMatch.fetch(path)
          .then(response => response.json())
          .then(partidas => this.partidas = partidas)
          .catch(error => console.error(error));
      },

      fetchJogadores: function () {
        BGMatch.fetch('/jogadores')
          .then(response => response.json())
          .then(jogadores => jogadores.map(j => ({value: j.id, text: j.nome})))
          .then(jogadores => this.opcoes.jogadores = this.opcoes.jogadores.concat(jogadores))
          .catch(error => console.error(error));
      },

      /**
       * Consulta a lista de jogos para o select.
       */
      fetchJogos: function () {
        BGMatch.fetch('/jogos')
          .then(response => response.json())
          .then(jogos => this.opcoes.jogos = jogos.map(jogo => ({code: jogo.id, label: jogo.nome})))
          .catch(error => console.error(error));
      },
    },
  }
</script>
