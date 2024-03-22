<template>
  <b-modal id="modal-partida" title="Cadastrar Partida" @hidden="resetForm()" @shown="atualizaForm()">

    <b-form @submit="handleSubmit">

      <b-form-group label="Jogo" label-for="input-jogo">
        <v-select id="input-jogo" v-model="form.jogo" required :options="jogosSelect" />
      </b-form-group>

      <b-form-group label="Expansão" label-for="input-expansao" :class="{'d-none_': expansoesSelect.length === 0}">
        <v-select id="input-expansao" v-model="form.expansao" required :options="expansoesSelect" />
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
        <tr>
          <th width="20%">Posição</th>
          <th width="40%">Jogador</th>
          <th width="30%">Pontuação</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(jogador, j) in form.jogadores" :key="j">
          <td>
            <b-form-select :options="listaPosicoes" v-model="jogador.posicao"></b-form-select>
          </td>
          <td>
            <b-form-select :options="listaJogadores" v-model="jogador.id"></b-form-select>
          </td>
          <td>
            <b-form-input type="number" v-model="jogador.pontuacao"></b-form-input>
          </td>
        </tr>
        </tbody>
      </table>

    </b-form>

    <template #modal-footer="{ ok, cancel }">
      <b-form-checkbox class="mr-auto" v-model="form.ranking">Ranking</b-form-checkbox>
      <b-button @click="cancel()">Cancelar</b-button>
      <b-button variant="primary" @click="handleSubmit" :disabled="!isFormValid">Ok</b-button>
    </template>

  </b-modal>
</template>

<script>
  import BGMatch from "../BGMatch";

  export default {
    props: ['partida'],

    data() {
      return {
        // Lista geral de jogos
        jogos: [],

        // Lista de jogos para o select
        listaJogos: [],

        // Lista de locais para o input
        listaLocais: [],

        // Lista de posições para os selects
        listaPosicoes: [
          {text: '1º lugar', value: 1},
          {text: '2º lugar', value: 2},
          {text: '3º lugar', value: 3},
          {text: '4º lugar', value: 4},
          {text: '5º lugar', value: 5},
          {text: '6º lugar', value: 6}
        ],

        // Lista de jogadores para os selects
        listaJogadores: [
          {value: null, text: '--'}
        ],

        // Dados do formulário
        form: {
          data: '',
          jogo: '',
          expansao: '',
          local: '',
          ranking: true,
          jogadores: []
        },

        partidaObj: null,
      }
    },

    created() {
      this.initjogadores();
      this.fetchJogos();
      this.fetchLocais();
      this.fetchJogadores();
    },

    computed: {
      jogosSelect() {
        return this.jogos
          .filter(jogo => !jogo.id_base)
          .map(jogo => ({
            code: jogo.id,
            label: jogo.nome
          }))
      },

      expansoesSelect() {
        const id_base = this.form.jogo?.code;
        return id_base ? this.jogos
          .filter(jogo => jogo.id_base === id_base)
          .map(jogo => ({
            code: jogo.id,
            label: jogo.nome
          })) : []
      },

      jogoSelecionado() {
        return this.form.jogo;
      },

      isFormValid() {
        return this.form.jogo
          && this.form.data
          && this.form.local
          && this.form.jogadores.filter(j => j.id).length > 1;
      }
    },

    watch: {
      jogoSelecionado(val, old) {
        if (this.form.expansao) {
          if (!val) {
            this.form.expansao = '';
            return
          }
          const exp = this.jogos.find(j => j.id === this.form.expansao.code);
          if (exp && exp.id_base !== val.code) {
            this.form.expansao = '';
          }
        }
      }
    },

    methods: {
      initjogadores() {
        const numJogadores = 6;
        for (let i = 0; i < numJogadores; i++) {
          this.form.jogadores.push({
            id: null,
            posicao: i + 1,
            pontuacao: null,
          });
        }
      },

      /**
       * Adiciona uma opção no input de locais.
       */
      createOption(opt) {
        this.listaLocais.push(opt);
        return opt;
      },

      /**
       * Consulta a lista de jogos para o select.
       */
      fetchJogos: function () {
        BGMatch.fetch('/jogos')
          .then(response => response.json())
          .then(jogos => {
            this.jogos = jogos;
            this.listaJogos = jogos
              .filter(jogo => !jogo.id_base)
              .map(jogo => ({
                code: jogo.id,
                label: jogo.nome
              }))
          })
          .catch(error => console.error(error));
      },

      /**
       * Consulta a lista de locais existentes para o input.
       */
      fetchLocais: function () {
        BGMatch.fetch('/partidas/locais')
          .then(response => response.json())
          .then(locais => this.listaLocais = locais)
          .catch(error => console.error(error));
      },

      /**
       * Consulta a lista de jogadores.
       */
      fetchJogadores: function () {
        BGMatch.fetch('/jogadores')
          .then(response => response.json())
          .then(jogadores => {
            this.listaJogadores.push(...jogadores.map(jogador => ({
              value: jogador.id,
              text: jogador.nome,
            })));
          })
          .catch(error => console.error(error));
      },

      /**
       * Atualiza os campos do formulário com os dados da partida selecionada.
       */
      atualizaForm() {
        if (this.partida) {
          this.form.data = this.partida.data;
          this.form.jogo = this.jogosSelect.find(item => item.code == this.partida.jogo.id);
          this.form.expansao = this.partida.expansao
            ? this.expansoesSelect.find(item => item.code == this.partida.expansao.id)
            : '';
          this.form.local = this.partida.local;
          this.form.ranking = this.partida.ranking;
          this.partida.jogadores.forEach((jogador, i) => {
            this.form.jogadores[i].id = jogador.id;
            this.form.jogadores[i].posicao = jogador.posicao;
            this.form.jogadores[i].pontuacao = jogador.pontuacao;
          });
        }
        else {
          this.resetForm();
        }
      },

      /**
       * Limpa os campos do formulário.
       */
      resetForm: function() {
        this.form.data = '';
        this.form.jogo = '';
        this.form.expansao = '';
        this.form.local = '';
        this.form.ranking = true;
        this.form.jogadores.forEach((jogador, i) => {
          jogador.id = null;
          jogador.posicao = i + 1;
          jogador.pontuacao = null;
        });
      },

      /**
       * Responde ao envio do formulário.
       */
      handleSubmit: function(evt) {
        evt.preventDefault();

        const data = {
          id_jogo: this.form.jogo.code,
          id_expansao: this.form.expansao?.code ?? null,
          data: this.form.data,
          local: this.form.local,
          ranking: this.form.ranking,
          jogadores: this.form.jogadores.filter(j => j.id)
        };

        const path = this.partida ? `/partidas/${this.partida.id}/update` : '/partidas/new';
        BGMatch.fetch(path, {
          method: 'POST',
          headers: new Headers({"Content-Type": "application/json"}),
          body: JSON.stringify(data)
        }).then(response => response.json())
          .then(response => {
            this.resetForm();
            this.$bvModal.hide('modal-partida');
            this.$emit('updated', true);
            // TODO: Show toast
          })
          .catch(error => {
            window.alert('Ocorreu um erro ao salvar a partida.');
            console.error(error);
          });
      },
    }
  }
</script>
