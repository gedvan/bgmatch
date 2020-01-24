<template>
  <b-modal id="modal-partida" title="Cadastrar Partida" @ok="handleSubmit">

    <b-form @submit="handleSubmit">

      <b-form-group label="Jogo" label-for="input-jogo">
        <v-select id="input-jogo" v-model="form.jogo" required :options="listaJogos" />
      </b-form-group>

      <b-row>
        <b-col>
          <b-form-group label="Data" label-for="input-data">
            <b-form-input id="input-data" v-model="form.data" type="date" required placeholder="DD/MM/AAAA" />
          </b-form-group>
        </b-col>
        <b-col>
          <b-form-group label="Local" label-for="input-local">
            <b-form-input id="input-local" list="lista-locais" v-model="form.local" required />
            <b-form-datalist id="lista-locais" :options="listaLocais"></b-form-datalist>
          </b-form-group>
        </b-col>
      </b-row>

      <table class="table table-sm">
        <thead>
        <th width="10%"><b-checkbox v-model="checkAll" @change="handleCheckAll" /></th>
        <th width="40%">Jogador</th>
        <th width="20%">Pontuação</th>
        <th width="30%">Posição</th>
        </thead>
        <tbody>
        <tr v-for="jogador in form.jogadores">
          <td><b-checkbox v-model="jogador.presente" /></td>
          <td>{{ jogador.nome }}</td>
          <td><b-input type="text" size="sm" style="width: 5em"
                       v-model="jogador.pontuacao" v-show="jogador.presente" /></td>
          <td class="text-center">
            <b-form-select size="sm" v-model="jogador.posicao" :options="posicoes" v-show="jogador.presente"></b-form-select>
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
    props: ['partida'],

    data() {
      return {
        // Lista de jogos para o select
        listaJogos: [],

        // Lista de locais para o input
        listaLocais: [],

        // Dados do formulário
        form: {
          data: '',
          jogo: '',
          local: '',
          jogadores: []
        },

        posicoes: [
          {text: '1º lugar', value: 1},
          {text: '2º lugar', value: 2},
          {text: '3º lugar', value: 3},
          {text: '4º lugar', value: 4},
          {text: '5º lugar', value: 5}
        ],

        partidaObj: null,
      }
    },

    computed: {
      // Indicador de quando todos os jogadores estão selecionados
      checkAll: {
        get() {
          return this.form.jogadores.length &&
            this.form.jogadores.filter(j => j.presente).length === this.form.jogadores.length;
        },
        set(value) {
        }
      }
    },

    watch: {
      partida: function (newVal, oldVal) {
        console.log(newVal);
        this.resetPartida();
        if (newVal) {
          this.fetchPartida(newVal.id);
        }
      }
    },

    methods: {
      /**
       * Responde ao envio do formulário.
       */
      handleSubmit: function(evt) {
        evt.preventDefault();

        const data = {
          id_jogo:   this.form.jogo.code,
          data:      this.form.data,
          local:     this.form.local,
          jogadores: []
        };
        data.jogadores = this.form.jogadores
          .filter(j => j.presente)
          .map(j => ({id: j.id, pontuacao: j.pontuacao, posicao: j.posicao}));

        window.fetch(BGMatch.apiUrl + '/partidas/nova', {
          method: 'POST',
          headers: new Headers({"Content-Type": "application/json"}),
          body: JSON.stringify(data)
        }).then(response => response.json())
          .then(response => {
            this.resetPartida();
            this.$bvModal.hide('modal-partida');
            this.$emit('updated', true);
            // TODO: Show toast
          })
          .catch(error => {
            window.alert('Ocorreu um erro ao cadastrar a partida.');
            console.error(error);
          });
      },

      /**
       * Responde ao click no checkbox select all
       */
      handleCheckAll: function(checked) {
        this.form.jogadores.forEach(j => j.presente = checked);
      },

      /**
       * Adiciona uma opção no input de locais.
       */
      createOption(opt) {
        this.listaLocais.push(opt);
        return opt;
      },

      /**
       * Zera as informações da partida.
       */
      resetPartida: function() {
        this.form.data = '';
        this.form.jogo = '';
        this.form.local = '';
        this.form.jogadores.forEach((j, i) => {
          this.form.jogadores[i].presente = false;
          this.form.jogadores[i].pontuacao = 0;
          this.form.jogadores[i].posicao = null;
        });
      },

      /**
       * Consulta a lista de jogos para o select.
       */
      fetchGames: function () {
        window.fetch(BGMatch.apiUrl + '/jogos')
          .then(response => response.json())
          .then(jogos => this.listaJogos = jogos.map(jogo => ({code: jogo.id, label: jogo.nome})))
          .catch(error => console.error(error));
      },

      /**
       * Consulta a lista de locais existentes para o input.
       */
      fetchPlaces: function () {
        window.fetch(BGMatch.apiUrl + '/partidas/locais')
          .then(response => response.json())
          .then(locais => this.listaLocais = locais)
          .catch(error => console.error(error));
      },

      /**
       * Consulta a lista de jogadores.
       */
      fetchPlayers: function () {
        window.fetch(BGMatch.apiUrl + '/jogadores')
          .then(response => response.json())
          .then(jogadores => jogadores.map(jogador => ({
            presente: false,
            id: jogador.id,
            nome: jogador.nome,
            pontuacao: 0,
            posicao: '',
          })))
          .then(jogadores => this.form.jogadores = jogadores)
          .catch(error => console.error(error));
      },

      fetchPartida: function(id) {
        window.fetch(BGMatch.apiUrl + '/partida/' + id)
          .then(response => response.json())
          .then(partida => {
            this.form.data = partida.data;
            this.form.jogo = this.listaJogos.find(j => j.code == partida.id_jogo);
            this.form.local = partida.local;
            //console.log(partida.jogadores);
            //console.log(this.form.jogadores);
            partida.jogadores.forEach(jogador => {
              var jog = this.form.jogadores.find(j => j.id == jogador.id);
              jog.presente = true;
              jog.pontuacao = jogador.pontuacao;
              jog.posicao = jogador.posicao;
            });
          })
          .catch(error => console.error(error));
      }
    },

    created() {
      // Inicializa a lista de jogos, locais e jogadores
      this.fetchGames();
      this.fetchPlaces();
      this.fetchPlayers();
    }
  }
</script>
