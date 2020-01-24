<template>
  <div id="view-lista-partidas">

    <div class="bg-light p-3">
      <div class="form-row">

        <b-form-group label="Período" label-for="input-data" class="col-md-4 mb-0">
          <b-input-group>
            <b-form-input id="input-data" v-model="filtros.data_inicial" type="date" required placeholder="DD/MM/AAAA" />
            <b-form-input id="input-data" v-model="filtros.data_final" type="date" required placeholder="DD/MM/AAAA" />
          </b-input-group>
        </b-form-group>

      </div>
    </div>

    <div class="py-3 d-flex">
      <b-button @click="abrirFormPartida(null)" class="ml-auto">
        <font-awesome-icon icon="plus" />&nbsp;Cadastrar partida
      </b-button>
    </div>

    <table v-if="partidasFiltradas.length > 0" class="table table-striped table-sm">
      <thead>
      <tr>
        <th>Data</th><th>Jogo</th><th>Local</th><th>Jogadores</th><th>&nbsp;</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(partida, index) in partidasFiltradas">
        <td>{{ partida.data | data_br }}</td>
        <td>{{ partida.nome_jogo }}</td>
        <td>{{ partida.local }}</td>
        <td>
          <span v-for="(jogador, i) in partida.jogadores" :class="['jogador', 'posicao-' + jogador.posicao]">
            <font-awesome-icon icon="medal" v-if="jogador.posicao <= 3" />
            {{ jogador.nome + (i < partida.jogadores.length - 1 ? ',' : '')}}
          </span>
        </td>
        <td class="text-right">
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

    <form-partida :partida="edicaoPartida" @updated="atualizaPartidas" />
  </div>
</template>

<script>
  import BGMatch from "../BGMatch";
  import FormPartida from "../components/FormPartida.vue";
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { library } from '@fortawesome/fontawesome-svg-core';
  import { faMedal, faEdit, faPlus, faTrash } from '@fortawesome/free-solid-svg-icons';

  library.add(faMedal, faEdit, faPlus, faTrash);

  export default {
    components: {
      FontAwesomeIcon,
      FormPartida
    },

    data() {
      return {
        // Lista de partidas exibidas na tabela
        partidas: [],

        // Partida sendo editada
        edicaoPartida: null,

        filtros: {
          //data_inicial: '2020-01-01',
          data_inicial: '',
          data_final: '',
        }
      }
    },

    computed: {

      partidasFiltradas: function() {
        return this.partidas
          .filter(partida => this.filtros.data_inicial ? partida.data >= this.filtros.data_inicial : true);
      }

    },

    methods: {
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
       * @param array jogadores
       * @returns {string | *}
       */
      nomesJogadores: function (jogadores) {
        return jogadores.map(j => j.vencedor ? `<u><font-awesome-icon icon="trophy" /> ${j.nome}</u>` : j.nome).join(', ');
      },

      excluirPartida: function (partida) {
        if (window.confirm(`Deseja realmente excluir a partida de ${partida.nome_jogo} de ${partida.data}?`)) {
          window.fetch(BGMatch.apiUrl + '/partida/' + partida.id + '/delete', {method: 'POST'})
            .then(response => response.json())
            .then(data => {
              console.log(data);
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
        window.fetch(BGMatch.apiUrl + '/partidas')
          .then(response => response.json())
          .then(partidas => this.partidas = partidas)
          .catch(error => console.error(error));
      }
    },

    created() {
      this.atualizaPartidas();
    }
  }
</script>

<style lang="scss">
  @import "../scss/includes";

  #view-lista-partidas {
    .jogador {
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
  }
</style>
