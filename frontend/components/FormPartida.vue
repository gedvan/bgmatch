<template>
  <b-modal id="modal-partida" title="Cadastrar Partida" @ok="handleSubmit">

    <b-form @submit="handleSubmit">

      <b-form-group label="Jogo" label-for="input-jogo">
        <v-select id="input-jogo" v-model="jogo" required :options="listaJogos" />
      </b-form-group>

      <b-row>
        <b-col>
          <b-form-group label="Data" label-for="input-data">
            <b-form-input id="input-data" v-model="data" type="date" required placeholder="DD/MM/AAAA" />
          </b-form-group>
        </b-col>
        <b-col>
          <b-form-group label="Local" label-for="input-local">
            <v-select id="input-local" v-model="local" required :options="listaLocais" taggable
                      :create-option="createOption" placeholder="Ex: Casa de Bruno" />
          </b-form-group>
        </b-col>
      </b-row>

      <table class="table table-sm">
        <thead>
        <th width="10%"><b-checkbox v-model="checkAll" @change="handleCheckAll" /></th>
        <th width="40%">Jogador</th>
        <th width="35%">Pontuação</th>
        <th width="15%">Vencedor</th>
        </thead>
        <tbody>
        <tr v-for="jogador in jogadores">
          <td><b-checkbox v-model="jogador.presente" /></td>
          <td>{{ jogador.nome }}</td>
          <td><b-input type="text" size="sm" style="width: 5em"
                       v-model="jogador.pontuacao" v-show="jogador.presente" /></td>
          <td class="text-center">
            <b-checkbox v-model="jogador.vencedor" />
          </td>
        </tr>
        </tbody>
      </table>

    </b-form>

  </b-modal>
</template>

<script>
  import BGMatch from "../BGMatch";

  export default {

    data() {
      return {
        listaJogos: [],
        listaLocais: [],

        data: '',
        jogo: '',
        local: '',
        jogadores: []
      }
    },

    computed: {
      checkAll: {
        get() {
          return this.jogadores.length &&
            this.jogadores.filter(j => j.presente).length === this.jogadores.length;
        },
        set(value) {
        }
      }
    },

    methods: {
      handleSubmit: function(evt) {
        evt.preventDefault();
      },

      handleCheckAll: function(checked) {
        this.jogadores.forEach(j => j.presente = checked);
      },

      createOption(opt) {
        console.log('create option', opt);
        this.listaLocais.push(opt);
        return opt;
      }
    },

    created() {
      window.fetch(BGMatch.apiUrl + '/jogos')
        .then(response => response.json())
        .then(jogos => this.listaJogos = jogos.map(jogo => ({code: jogo.id_ludo, label: jogo.nome})))
        .catch(error => console.error(error));

      window.fetch(BGMatch.apiUrl + '/jogadores')
        .then(response => response.json())
        .then(jogadores => jogadores.map(jogador => ({
          presente: true,
          id: jogador.id,
          nome: jogador.nome,
          pontuacao: 0,
          vencedor: false
        })))
        .then(jogadores => this.jogadores = jogadores)
        .catch(error => console.error(error));
    }
  }
</script>
