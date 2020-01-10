<template>
  <div class="jogo">
    <a class="imagem" href="#" @click.prevent="$emit('on-click', jogo)">
      <img :src="jogo.imagem" />
    </a>
    <h3 class="titulo">
      <a href="#" @click.prevent="$emit('on-click', jogo)">{{ jogo.nome }}</a>
    </h3>
    <div class="info">
      {{ numJogadores }} | {{ nomeCategoria }} {{ jogo.coop ? '| Coop./Grupo' : '' }}
    </div>
  </div>
</template>

<script>
  import BGMatch from "../BGMatch";
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { library } from '@fortawesome/fontawesome-svg-core';
  import { faUserFriends } from '@fortawesome/free-solid-svg-icons';

  library.add(faUserFriends);

  export default {

    components: {
      FontAwesomeIcon
    },

    props: {
      jogo: Object
    },

    computed: {

      numJogadores: function() {
        return this.jogo.min === this.jogo.max
          ? this.jogo.min.toString()
          : this.jogo.min + '-' + this.jogo.max;
      },

      nomeCategoria: function() {
        return BGMatch.nomeCategoria(this.jogo.categoria);
      }

    }

  }
</script>

<style lang="scss" scoped>
  @import "../scss/includes";

  .jogo {
    text-align: center;
    padding: 15px 10px;
    line-height: 1.1;
    .imagem img {
      height: 100px;
      width: auto;
    }
    .titulo {
      font-weight: $font-weight-bold;
      font-size: 0.9375rem;
      margin: .5rem 0;
      line-height: 1.1;
      a {
        color: $text-color;
      }
      a:focus {
        color: $primary;
        text-decoration: none;
      }
    }
    .info {
      font-size: 90%;
      color: $text-muted;
    }

    &:hover {
      .titulo a {
        color: $primary;
        text-decoration: none;
      }
    }
  }
</style>
