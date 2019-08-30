<template>
  <div class="jogo">
    <a href="#" @click.prevent="$emit('on-click', jogo)">
      <img :src="jogo.img_ludo" />
    </a>
    <h3>{{ jogo.nome }}</h3>
    <div class="text-muted small">
      <font-awesome-icon icon="user-friends"/>
      {{ numJogadores }} / {{ nomeTipo }}
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
          : this.jogo.min + ' a ' + this.jogo.max;
      },

      nomeTipo: function() {
        return BGMatch.nomeTipo(this.jogo.tipo);
      }

    }

  }
</script>
