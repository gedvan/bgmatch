<template>
  <div id="view-lista-partidas">
    <b-button @click="abrirFormPartida">Cadastrar partida</b-button>

    <table v-if="partidas.length > 0" class="table table-striped table-sm">
      <thead>
      <tr>
        <th>Data</th><th>Jogo</th><th>Local</th><th>Jogadores</th><th>&nbsp;</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(partida, index) in partidas">
        <td>{{ partida.data }}</td>
        <td>{{ partida.nome_jogo }}</td>
        <td>{{ partida.local }}</td>
        <td>
          <span v-for="(jogador, i) in partida.jogadores" :class="['jogador', 'posicao-' + jogador.posicao]">
            <font-awesome-icon icon="medal" v-if="jogador.posicao <= 3" />
            {{ jogador.nome + (i < partida.jogadores.length - 1 ? ',' : '')}}
          </span>
        </td>
        <td class="text-right">
          <b-button variant="link" size="sm" @click="abrirFormPartida(index)">
            <font-awesome-icon icon="edit" />
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
  import { faMedal, faEdit } from '@fortawesome/free-solid-svg-icons';

  library.add(faMedal, faEdit);

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
        edicaoPartida: null
      }
    },

    methods: {
      /**
       * Abre o modal com o formulário de cadastro/edição de uma partida.
       *
       * @param i
       */
      abrirFormPartida: function (i) {
        if (typeof i == 'number') {
          this.edicaoPartida = this.partidas[i];
        } else {
          this.edicaoPartida = null;
        }
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
