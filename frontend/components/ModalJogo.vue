<template>
  <b-modal id="modal-jogo" size="lg" hide-footer>
    <template #modal-title>
      {{ jogo.nome }}
      <b-link :href="BGMatch.urlJogoLudopedia(jogo)" target="_blank" class="small" title="Abrir na Ludopedia">
        <font-awesome-icon icon="external-link-alt" />
      </b-link>
    </template>
    <div v-if="jogo" class="media">
      <img :src="jogo.img_ludo" class="mr-3">
      <div class="media-body">
        <dl class="row">
          <dt class="col-sm-3">Nº de jogadores</dt>
          <dd class="col-sm-9">{{ numJogadores }}</dd>
          <dt class="col-sm-3">Categoria</dt>
          <dd class="col-sm-9">
            <span v-if="!editandoTipo">{{ BGMatch.nomeCategoria(jogo.tipo) }}</span>
            <b-form-select v-if="editandoTipo" v-model="tipoEdicao" :options="BGMatch.categoriasJogos" size="sm" class="w-auto"></b-form-select>
            <b-button v-if="!editandoTipo" variant="link" size="sm" @click="iniciaEdicaoTipo"><font-awesome-icon icon="edit" /></b-button>
            <b-button-group v-if="editandoTipo" size="sm">
              <b-button variant="link" @click="salvaEdicaoTipo"><font-awesome-icon icon="check-circle" /></b-button>
              <b-button variant="link" @click="cancelaEdicaoTipo"><font-awesome-icon icon="times-circle" /></b-button>
            </b-button-group>
          </dd>
          <template v-if="jogo.expansoes.length">
            <dt class="col-sm-3">Expansões</dt>
            <dd class="col-sm-9" v-if="jogo.expansoes.length">
              <ul class="m-0 pl-3">
                <li v-for="exp in jogo.expansoes">
                  {{ exp.nome }}
                  <b-link :href="urlJogoLudopedia(exp)" target="_blank" class="small" title="Abrir na Ludopedia">
                    <font-awesome-icon icon="external-link-alt" />
                  </b-link>
                </li>
              </ul>
            </dd>
          </template>
        </dl>
      </div>
    </div>
  </b-modal>
</template>

<script>
  import BGMatch from "../BGMatch";
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { library } from '@fortawesome/fontawesome-svg-core';
  import { faExternalLinkAlt, faEdit, faCheckCircle, faTimesCircle } from '@fortawesome/free-solid-svg-icons';

  library.add(faExternalLinkAlt, faEdit, faCheckCircle, faTimesCircle);

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
        editandoTipo: false,
        tipoEdicao: '',
      }
    },

    computed: {

      numJogadores: function() {
        if (this.jogo.min === this.jogo.max) {
          return this.jogo.min === 1 ? '1 jogador' : this.jogo.min + ' jogadores';
        } else {
          return `De ${this.jogo.min} a ${this.jogo.max} jogadores`;
        }
      }

    },

    methods: {

      urlJogoLudopedia: function (jogo) {
        return BGMatch.ludopediaUrl + '/jogo/' + jogo.slug;
      },

      iniciaEdicaoTipo: function () {
        this.editandoTipo = true;
      },

      cancelaEdicaoTipo: function () {
        this.tipoEdicao = this.jogo.tipo;
        this.editandoTipo = false;
      },

      salvaEdicaoTipo: function () {
        const url = `${BGMatch.apiUrl}/jogos/${this.jogo.id_ludo}/tipo`;
        window.fetch(url, {
          method: "POST",
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({tipo: this.tipoEdicao})
        })
          .then(response => response.json())
          .then(data => {
            if (data.updated === 1) {
              this.jogo.tipo = this.tipoEdicao;
            } else {
              throw new Error('Tipo de jogo não atualizado.');
            }
          })
          .catch(error => {
            this.tipoEdicao = this.jogo.tipo;
            window.alert('Ocorreu um erro ao atualizar o tipo do jogo.');
            console.error(error)
          })
          .finally(() => {
            this.editandoTipo = false;
          });

      },
    },

    watch: {
      jogo: function (newJogo, oldJogo) {
        this.tipoEdicao = newJogo.tipo;
      }
    }

  }
</script>
