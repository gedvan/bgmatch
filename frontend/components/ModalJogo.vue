<template>
  <b-modal id="modal-jogo" size="lg" :title="jogo ? jogo.nome : ''">
    <b-media v-if="jogo">
      <template #aside>
        <img :src="jogo.imagem" class="imagem-jogo">
      </template>
      <dl class="row info-jogo">
        <dt class="col-sm-3">Nº de jogadores</dt>
        <dd class="col-sm-9">{{ numJogadores }}</dd>
        <dt class="col-sm-3">Cooperativo/Em grupo</dt>
        <dd class="col-sm-9">
          <span v-if="!editando">{{ jogo.coop ? 'Sim' : 'Não' }}</span>
          <b-checkbox v-if="editando" v-model="jogoEdicao.coop" switch></b-checkbox>
        </dd>
        <dt class="col-sm-3">Categoria</dt>
        <dd class="col-sm-9">
          <span v-if="!editando">{{ nomeCategoria(jogo.categoria) }}</span>
          <b-form-select v-if="editando" v-model="jogoEdicao.categoria" :options="categoriasJogos" size="sm" class="w-auto"></b-form-select>
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
    </b-media>
    <template #modal-footer>
      <b-link :href="urlJogoLudopedia(jogo)" target="_blank" title="Página do jogo na Ludopedia" class="small mr-auto">
        <font-awesome-icon icon="external-link-alt" />&nbsp;Ludopedia
      </b-link>
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
        editando: false,
        jogoEdicao: {},
      }
    },

    computed: {

      categoriasJogos() {
        return BGMatch.categoriasJogos.map(c => ({value: c.key, text: c.label}));
      },

      numJogadores: function() {
        if (this.jogo.min === this.jogo.max) {
          return this.jogo.min === 1 ? '1 jogador' : this.jogo.min + ' jogadores';
        } else {
          return `${this.jogo.min} a ${this.jogo.max} jogadores`;
        }
      }

    },

    methods: {

      nomeCategoria: function(key) {
        return BGMatch.nomeCategoria(key)
      },

      urlJogoLudopedia: function(jogo) {
        return BGMatch.urlJogoLudopedia(jogo);
      },

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
      }
    }

  }
</script>

<style lang="scss" scoped>
.media-aside {
  flex-direction: column;
  align-items: center;
}
.imagem-jogo {
  margin-bottom: .3em;
}
.info-jogo dt {
  line-height: 1.1;
  margin-bottom: 0.5rem;
}
</style>
