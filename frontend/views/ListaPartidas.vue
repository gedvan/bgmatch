<template>
  <div>
    <b-button @click="abrirFormPartida">Cadastrar partida</b-button>

    <form-partida />
  </div>
</template>

<script>
  import FormPartida from "../components/FormPartida.vue";

  export default {
    components: {
      FormPartida
    },

    data() {
      return {
      }
    },

    methods: {
      abrirFormPartida: function () {
        this.$bvModal.show('modal-partida');
      }
    }
  }
</script>
