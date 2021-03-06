<template>
  <div id="view-lista-partidas">

    <div class="bg-light pt-3 px-3 pb-1 filter-form">
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

    <div class="row my-3">
      <div class="col">
        Total de partidas cadastradas: {{ partidas.length }}
      </div>
      <div class="col text-center">
        Exibindo {{ partidasFiltradas.length }} partida(s)
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
              Data <font-awesome-icon v-if="ordenacao.campo == 'data'" :icon="ordenacao.inverter ? 'sort-up' : 'sort-down'" />
            </a>
          </th>
          <th>
            <a href="#" @click="ordenarPor('nome_jogo')">
              Jogo <font-awesome-icon v-if="ordenacao.campo == 'nome_jogo'" :icon="ordenacao.inverter ? 'sort-up' : 'sort-down'" />
            </a>
          </th>
          <th>
            <a href="#" @click="ordenarPor('local')">
              Local <font-awesome-icon v-if="ordenacao.campo == 'local'" :icon="ordenacao.inverter ? 'sort-up' : 'sort-down'" />
            </a>
          </th>
          <th>Jogadores</th>
          <th class="text-center" width="5%">Ações</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(partida, index) in partidasFiltradas">
          <td>{{ partida.data | data_br }}</td>
          <td>{{ partida.nome_jogo }}</td>
          <td>{{ partida.local }}</td>
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

    <form-partida :partida="edicaoPartida" @updated="atualizaPartidas" />
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
        edicaoPartida: null
      }
    },

    computed: {
      partidasFiltradas: function() {
        return this.partidas
          .filter(partida => this.filtros.data_inicial ? partida.data >= this.filtros.data_inicial : true)
          .filter(partida => this.filtros.data_final ? partida.data <= this.filtros.data_final : true)
          .filter(partida => this.filtros.jogo ? partida.id_jogo === this.filtros.jogo.code : true)
          .filter(partida => {
            if (this.filtros.jogador > 0) {
              return partida.jogadores.find(j => j.id === this.filtros.jogador) ? true : false;
            }
            return true;
          })
          .sort(this.sortFunction());
      }
    },

    methods: {
      filtroPeriodo(inicial, final) {
        this.filtros.data_inicial = inicial
        this.filtros.data_final = final
      },

      /**
       * Abre o modal com o formulário de cadastro/edição de uma partida.
       *
       * @param partida
       */
      abrirFormPartida: function (partida) {
        this.edicaoPartida = partida;
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

      sortFunction: function () {
        const campo = this.ordenacao.campo;
        const inverter = campo === "data" ? !this.ordenacao.inverter : this.ordenacao.inverter;
        return (a, b) => inverter ?
          b[campo].toLocaleLowerCase().localeCompare(a[campo].toLocaleLowerCase()) :
          a[campo].toLocaleLowerCase().localeCompare(b[campo].toLocaleLowerCase());
      },

      excluirPartida: function (partida) {
        if (window.confirm(`Deseja realmente excluir a partida de ${partida.nome_jogo} de ${partida.data}?`)) {
          BGMatch.fetch(`/partida/${partida.id}/delete`, {method: 'POST'})
            .then(response => response.json())
            .then(data => {
              this.atualizaPartidas();
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
      atualizaPartidas: function () {
        BGMatch.fetch('/partidas')
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

    created() {
      this.periodos.push({
        key: 'all',
        label: 'Tudo',
        inicial: '',
        final: ''
      });
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

      this.filtros.data_inicial = `${anoAtual}-01-01`;
      this.filtros.data_final = `${anoAtual}-12-31`;
    },

    mounted() {
      this.fetchJogos();
      this.fetchJogadores();
      this.atualizaPartidas();
    }
  }
</script>

<style lang="scss" scoped>
  @import "../scss/includes";

  .filter-form {
    .form-group-sorting {
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

  .jogador {
    white-space: nowrap;
    &.posicao-1 {
      font-weight: bold;
      .fa-medal {
        color: goldenrod;
      }
    }
    &.posicao-2 .fa-medal {
      color: silver;
    }
    &.posicao-3 .fa-medal {
      color: chocolate;
    }
  }

  .periodo-ativo {
    font-weight: bold;
    text-decoration: underline;
  }
</style>
