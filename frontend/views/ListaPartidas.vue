<template>
  <div>
    <b-button @click="abrirFormPartida">Cadastrar partida</b-button>

    <table v-if="partidas.length > 0" class="table table-striped">
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
          <span v-for="(jogador, i) in partida.jogadores" :class="{ 'font-weight-bold': jogador.vencedor }">
            <font-awesome-icon icon="trophy" v-if="jogador.vencedor" />
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
  import { faTrophy, faEdit } from '@fortawesome/free-solid-svg-icons';

  library.add(faTrophy, faEdit);

  export default {
    components: {
      FontAwesomeIcon,
      FormPartida
    },

    data() {
      return {
        partidas: [],
        edicaoPartida: null
      }
    },

    methods: {
      abrirFormPartida: function (i) {
        if (typeof i == 'number') {
          this.edicaoPartida = this.partidas[i];
        } else {
          this.edicaoPartida = null;
        }
        this.$bvModal.show('modal-partida');
      },

      nomesJogadores: function (jogadores) {
        return jogadores.map(j => j.vencedor ? `<u><font-awesome-icon icon="trophy" /> ${j.nome}</u>` : j.nome).join(', ');
      },

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
