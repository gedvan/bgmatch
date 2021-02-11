<template>
  <b-modal id="modal-cadastrar-jogo" ref="modal-cadastrar-jogo" title="Cadastrar Jogo" size="lg" scrollable @hidden="resetModal">
    <b-form @submit.prevent="search">
      <b-input-group>
        <b-form-input size="lg" placeholder="Pesquisar na Ludopedia..." v-model="term" ref="input-search" />
        <b-input-group-append>
          <b-button type="submit" :disabled="term.length < 3">
            <font-awesome-icon :icon="loading ? 'spinner' : 'search'" :class="{'fa-spin': loading}" />
          </b-button>
        </b-input-group-append>
      </b-input-group>
      <small class="text-muted" v-show="showLengthTip()">Digite pelo menos 3 caracteres</small>
    </b-form>
    <div class="resultados mt-4" v-if="jogos.length > 0">
      <h4>Jogos encontrados na Ludopedia</h4>
      <table class="table table-jogos">
        <tbody>
        <tr v-for="jogo in jogos" :key="jogo.id">
          <td class="col-image"><img :src="jogo.image" :alt="'Imagem de ' + jogo.title" /></td>
          <td class="col-title">{{ jogo.title }}</td>
          <td class="col-link">
            <a :href="jogo.link" target="_blank" title="Link para o jogo na Ludopedia">
              <font-awesome-icon icon="external-link-alt" />
            </a>
          </td>
          <td class="col-import">
            <span v-if="jogo.cadastrado" class="cadastrado" :class="{novo: jogo.novo}">Cadastrado</span>
            <b-button v-else variant="primary" size="sm" @click="importaJogo(jogo)">
              <template v-if="jogo.loading">
                <font-awesome-icon icon="spinner" class="fa-spin" />
              </template>
              <template v-else>
                Importar
              </template>
            </b-button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
    <template #modal-footer>
      <b-button variant="secondary" @click="closeModal()">Cancelar</b-button>
    </template>
  </b-modal>
</template>

<script>
import BGMatch from "../BGMatch";

export default {
  data() {
    return {
      term: '',
      touched: false,
      loading: false,
      jogos: []
    }
  },
  methods: {
    search(event) {
      this.touched = true;
      if (this.term.length > 2) {
        this.loading = true;
        BGMatch.fetch('/jogos/pesquisa/' + encodeURIComponent(this.term))
          .then(response => response.json())
          .then(jogos => {
            this.jogos = jogos;
          })
          .catch(error => console.error(error))
          .finally(() => {
            this.loading = false;
          });
      }
    },

    importaJogo(jogo) {
      this.$set(jogo, 'loading', true);
      BGMatch.fetch('/jogos/importa/' + jogo.slug, {method: "POST"})
        .then(response => response.json())
        .then(response => {
          const jogoLista = this.jogos.find(j => j.id === jogo.id)
          jogoLista.cadastrado = true
          this.$set(jogoLista, 'novo', true);
          this.$emit('jogo-cadastrado', response.jogo)
        })
        .catch(error => {
          alert('Erro na importação')
          console.error(error)
        })
        .finally(() => {
          jogo.loading = false;
        });
    },

    closeModal() {
      this.$refs['modal-cadastrar-jogo'].hide()
    },

    resetModal() {
      this.term = ''
      this.touched = false
      this.loading = false
      this.jogos = []
    },

    showLengthTip() {
      return this.touched && this.term.length > 0 && this.term.length < 3;
    }
  },

  mounted() {
  }
}
</script>

<style lang="scss" scoped>
.back-button {
  margin-right: auto;
}
.table-jogos {
  td {
    vertical-align: middle;
  }
  td.col-image {
    text-align: center;
  }
  td.col-import {
    white-space: nowrap;

    .cadastrado {
      color: #6c757d;
      font-style: italic;
      padding: 2px 6px;
      border-radius: 2px;
      &.novo {
        background: #c3ecc3;
      }
    }
  }
}
</style>
