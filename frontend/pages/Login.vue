<template>
  <b-card title="Login" class="login-card">
    <b-form @submit.prevent="login">
      <b-alert variant="danger" :show="alertText.length > 0" dismissible fade
        @dismissed="alertText=''">
        {{ alertText }}
      </b-alert>
      <b-form-group label="Usuário">
        <b-form-input type="text" required v-model="credentials.user" ref="inputUser" @change="inputChange"></b-form-input>
      </b-form-group>
      <b-form-group label="Senha">
        <b-form-input type="password" required v-model="credentials.pass" @change="inputChange"></b-form-input>
      </b-form-group>
      <b-button type="submit" variant="primary" size="lg" block>Entrar</b-button>
    </b-form>
  </b-card>
</template>

<script>
  import BGMatch from "../BGMatch";

  export default {

    data() {
      return {
        credentials: {
          user: '',
          pass: '',
        },
        alertText: ''
      }
    },

    methods: {
      inputChange() {
        this.alertText = '';
      },

      login() {
        const formdata = new FormData();
        formdata.append("usuario", this.credentials.user);
        formdata.append("senha", this.credentials.pass);
        const requestOptions = {
          method: 'POST',
          body: formdata,
          redirect: 'follow'
        };
        window.fetch(BGMatch.apiUrl + '/login', requestOptions)
          .then(response =>  response.json())
          .then(response => {
            if (typeof response.error !== "undefined") {
              this.alertText = response.error;
              this.credentials.pass = '';
              this.$refs.inputUser.focus();
            } else {
              const token = response.token;
              window.localStorage.setItem('token', token);
              this.$router.push({name: 'index'});
              this.$router.app.$emit('login');
            }
          })
          .catch(error => {
            this.alertText = "Ocorreu um erro tentar efetuar o login.";
            console.error(error);
          });
      }
    },

    beforeCreate() {
      const token = localStorage.getItem('token');
      if (token) {
        this.$router.push({name: 'index'});
      }
    },

    mounted() {
      this.$refs.inputUser.focus();
    }
  }
</script>

<style lang="scss">
  .login-card {
    .card-title {
      text-align: center;
      text-transform: uppercase;
    }
  }
</style>
