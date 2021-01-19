<template>
  <b-modal id="modal-jogo" size="lg" :title="jogo ? jogo.nome : ''">
    <div v-if="jogo" class="media">
      <img :src="jogo.imagem" class="mr-3">
      <div class="media-body">
        <dl class="row">
          <dt class="col-sm-3">Nº de jogadores</dt>
          <dd class="col-sm-9">{{ numJogadores }}</dd>
          <dt class="col-sm-3">Cooperativo/Em grupo</dt>
          <dd class="col-sm-9">
            <span v-if="!editando">{{ jogo.coop ? 'Sim' : 'Não' }}</span>
            <b-checkbox v-if="editando" v-model="jogoEdicao.coop" switch></b-checkbox>
          </dd>
          <dt class="col-sm-3">Categoria</dt>
          <dd class="col-sm-9">
            <span v-if="!editando">{{ BGMatch.nomeCategoria(jogo.categoria) }}</span>
            <b-form-select v-if="editando" v-model="jogoEdicao.categoria" :options="BGMatch.categoriasJogos" size="sm" class="w-auto"></b-form-select>
          </dd>
          <template v-if="jogo.expansoes.length">
            <dt class="col-sm-3">Expansões</dt>
            <dd class="col-sm-9" v-if="jogo.expansoes.length">
              <ul class="m-0 pl-3">
                <li v-for="exp in jogo.expansoes">
                  {{ exp.nome }}
                  <b-link :href="BGMatch.urlJogoLudopedia(exp)" target="_blank" class="small" title="Abrir na Ludopedia">
                    <font-awesome-icon icon="external-link-alt" />
                  </b-link>
                </li>
              </ul>
            </dd>
          </template>
        </dl>
        <b-link :href="BGMatch.urlJogoLudopedia(jogo)" target="_blank" title="Página do jogo na Ludopedia">
          <font-awesome-icon icon="external-link-alt" />&nbsp;Ludopedia
        </b-link>
      </div>
    </div>
    <template #modal-footer>
      <b-button v-if="!editando" variant="outline-primary" @click="iniciaEdicao">
        <font-awesome-icon icon="edit" />&nbsp;Editar
      </b-button>
      <b-button v-if="editando" variant="primary" @click="salvaEdicao">
        <font-awesome-icon icon="check" />&nbsp;Salvar
      </b-button>
      <b-button v-if="editando" variant="secondary" @click="finalizaEdicao">
        <font-awesome-icon icon="times" />&nbsp;Cancelar
      </b-button>
    </template>
  </b-modal>
</template>

<script>
  import BGMatch from "../BGMatch";
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { library } from '@fortawesome/fontawesome-svg-core';
  import { faExternalLinkAlt, faEdit, faCheck, faTimes } from '@fortawesome/free-solid-svg-icons';

  library.add(faExternalLinkAlt, faEdit, faCheck, faTimes);

  export default {
    components: {
      FontAwesomeIcon
    },

    props: {
      jogo: Object
    },

    data() {
      return {
        BGMatch,
        editando: false,
        jogoEdicao: {},
      }
    },

    computed: {

      numJogadores: function() {
        if (this.jogo.min === this.jogo.max) {
          return this.jogo.min === 1 ? '1 jogador' : this.jogo.min + ' jogadores';
        } else {
          return `${this.jogo.min} a ${this.jogo.max} jogadores`;
        }
      }

    },

    methods: {

      iniciaEdicao: function() {
        this.jogoEdicao = Object.assign({}, this.jogo);
        this.editando = true;
      },

      finalizaEdicao: function () {
        this.editando = false;
        this.jogoEdicao = {};
      },

      salvaEdicao: function () {
        const url = '/jogos/salva/' + this.jogo.id;
        BGMatch.fetch(url, {
          method: "POST",
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({
            coop: this.jogoEdicao.coop,
            categoria: this.jogoEdicao.categoria
          })
        })
          .then(response => response.json())
          .then(data => {
            if (data.updated === 1) {
              this.jogo.categoria = this.jogoEdicao.categoria;
              this.jogo.coop = this.jogoEdicao.coop;
            } else {
              throw new Error('Erro ao salvar o jogo.');
            }
          })
          .catch(error => {
            window.alert('Ocorreu um erro ao salvar o jogo.');
            console.error(error);
          })
          .finally(() => {
            this.finalizaEdicao();
          });

      },
    },

    watch: {
      jogo: function (newJogo, oldJogo) {
        this.jogoEdicao = newJogo.categoria;
      }
    }

  }
</script>
