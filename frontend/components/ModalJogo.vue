<template>
  <b-modal id="modal-jogo" size="lg" :title="jogo ? jogo.nome : ''">
    <b-media v-if="jogo">
      <template #aside>
        <img :src="jogo.imagem" class="imagem-jogo">
      </template>
      <div class="form-row">

        <div class="form-group col-lg-4">
          <label>Nº de jogadores</label>
          <div>{{ numJogadores }}</div>
        </div>

        <div class="form-group col-lg-4">
          <label>Cooperativo/Em grupo</label>
          <div v-if="!editando">
            {{ jogo.coop ? 'Sim' : 'Não' }}
          </div>
          <b-form-checkbox v-if="editando" v-model="jogoEdicao.coop" switch></b-form-checkbox>
        </div>

        <div class="form-group col-lg-4">
          <label>Excluído da coleção</label>
          <div v-if="!editando">
            {{ jogo.excluido ? 'Sim' : 'Não' }}
          </div>
          <b-form-checkbox v-if="editando" v-model="jogoEdicao.excluido" switch></b-form-checkbox>
        </div>

      </div>
      <div class="form-row">

        <div class="form-group col-lg-4">
          <label>Categoria</label>
          <div>
            <span v-if="!editando">{{ nomeCategoria(jogo.categoria) }}</span>
            <span v-if="!editando && jogo.jogo_base">
              (<b-link @click="mudaJogo(jogo.jogo_base)">{{ jogo.jogo_base.nome }}</b-link>)
            </span>
            <b-form-select v-if="editando" v-model="jogoEdicao.categoria" :options="categoriasJogos" size="sm"></b-form-select>
          </div>
        </div>

        <div class="form-group col-lg-4" v-if="editando">
          <label>ID BGG</label>
          <b-button variant="link" size="sm" @click="fetchBggUrl"><font-awesome-icon icon="search" /></b-button>
          <b-form-input v-model="jogoEdicao.bgg_id" size="sm" ref="input_bgg_id"></b-form-input>
        </div>

        <div class="form-group col-lg-4">
          <label>Peso BGG</label>
          <span class="bgg-weight-value" v-if="!editando">{{ jogo.bgg_weight ?? '-' }}</span>
          <b-form-input v-model="jogoEdicao.bgg_weight" size="sm" v-if="editando"></b-form-input>
        </div>

      </div>

      <div v-if="jogo.expansoes && jogo.expansoes.length">
        <label>Expansões</label>
        <ul v-if="jogo.expansoes.length">
          <li v-for="expansao in jogo.expansoes">
            <b-link @click="mudaJogo(expansao)">{{ expansao.nome }}</b-link>
          </li>
        </ul>
      </div>
    </b-media>
    <template #modal-footer>
      <b-link :href="urlLudopedia" target="_blank" title="Página do jogo na Ludopedia" class="small mr-3">
        <font-awesome-icon icon="external-link-alt" />&nbsp;Ludopedia
      </b-link>
      <b-link :href="urlBgg" target="_blank" title="Página do jogo no BoardGameGeek" class="small" :disabled="!jogo.bgg_id">
        <font-awesome-icon icon="external-link-alt" />&nbsp;BoardGameGeek
      </b-link>
      <b-button v-if="!editando" variant="outline-primary" @click="iniciaEdicao" class="ml-auto">
        <font-awesome-icon icon="edit" />&nbsp;Editar
      </b-button>
      <b-button v-if="editando" variant="primary" @click="salvaEdicao" class="ml-auto">
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
  import {faExternalLinkAlt, faEdit, faCheck, faTimes, faSearch} from '@fortawesome/free-solid-svg-icons';

  library.add(faExternalLinkAlt, faEdit, faCheck, faTimes, faSearch);

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
        jogoEdicao: null,
      }
    },

    computed: {

      categoriasJogos() {
        return BGMatch.categoriasJogos.map(c => ({value: c.key, text: c.label}));
      },

      numJogadores() {
        if (this.jogo.min === this.jogo.max) {
          return this.jogo.min === 1 ? '1 jogador' : this.jogo.min + ' jogadores';
        } else {
          return `${this.jogo.min} a ${this.jogo.max} jogadores`;
        }
      },

      urlLudopedia() {
        return BGMatch.urlJogoLudopedia(this.jogo);
      },

      urlBgg() {
        return this.jogo.bgg_id ? 'https://boardgamegeek.com/boardgame/' + this.jogo.bgg_id : '#';
      }

    },

    methods: {

      nomeCategoria: function(key) {
        return BGMatch.nomeCategoria(key)
      },

      mudaJogo(jogo) {
        this.jogo = jogo;
      },

      iniciaEdicao: function() {
        this.jogoEdicao = Object.assign({}, this.jogo);
        this.editando = true;
      },

      finalizaEdicao: function () {
        this.editando = false;
        this.jogoEdicao = null;
      },

      salvaEdicao: function () {
        const url = '/jogos/' + this.jogo.id + '/update';
        const postKeys = ['categoria', 'coop', 'excluido', 'bgg_id', 'bgg_weight'];
        const postData = Object.fromEntries(
          postKeys.map(key => [key, this.jogoEdicao[key]])
        );
        BGMatch.fetch(url, {
          method: "POST",
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify(postData)
        })
          .then(response => response.json())
          .then(data => {
            if (data.updated === 1) {
              for (const key in postData) {
                this.jogo[key] = postData[key];
              }
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

      fetchBggUrl: function() {
        const url = '/jogos/' + this.jogo.id + '/bgg';
        BGMatch.fetch(url, {
          method: "GET"
        })
          .then(response => response.json())
          .then(data => {
            if (data.bgg_id) {
              this.jogoEdicao.bgg_id = data.bgg_id;
              this.jogoEdicao.bgg_weight = data.bgg_weight;
            }
            else {
              window.alert('Não foi possível obter os dados do jogo no BoardGameGeek. Por favor, preencha os dados manualmente.')
            }
          })
          .catch(error => {
            window.alert('Ocorreu um erro ao consultar o BoardGameGeek. Por favor, preencha os dados manualmente.');
            console.error(error);
          })
      }
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
</style>
