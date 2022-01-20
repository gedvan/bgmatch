<template>
  <div id="app" :class="'route-' + $route.name">

    <header id="header">
      <div class="container">

        <div class="branding">
          <Logo class="logo" />
          <h1 class="title">
            <span class="prefix">BG</span>Match
          </h1>
        </div>

        <div v-if="isLogged" class="user-info">
          <strong>{{ usuario.usuario }}</strong> | <a href="#" @click.prevent="logout">sair</a>
        </div>

      </div>
    </header>

    <main-nav v-if="isLogged" :route-path="$route.path" id="main-nav"></main-nav>

    <main id="main">
      <router-view :key="$route.fullPath"></router-view>
    </main>

    <template v-if="isLocalhost">
      <div class="responsive-indicator d-sm-none">xs</div>
      <div class="responsive-indicator d-none d-sm-flex d-md-none">sm</div>
      <div class="responsive-indicator d-none d-md-flex d-lg-none">md</div>
      <div class="responsive-indicator d-none d-lg-flex d-xl-none">lg</div>
      <div class="responsive-indicator d-none d-xl-flex">xl</div>
    </template>

  </div>
</template>

<script>
  import Logo from './assets/images/logo.svg?inline';
  import Titulo from './assets/images/titulo.svg?inline';
  import router from "./router";
  import BGMatch from "./BGMatch";
  import MainNav from "./components/MainNav";

  export default {
    components: {
      MainNav,
      Logo, Titulo
    },

    data() {
      return {
        usuario: {
          id: 0,
          usuario: ''
        }
      }
    },

    computed: {
      classes() {
        return 'teste';
      },

      isLocalhost() {
        return window.location.hostname === 'localhost';
      },

      isLogged() {
        return this.usuario.id !== 0;
      }
    },

    methods: {

      logout() {
        this.usuario = {
          id: 0,
          usuario: ''
        };
        localStorage.removeItem('token');
        this.$router.push({name: 'login'});
      },

      fetchUserInfo() {
        const token = window.localStorage.getItem('token');
        if (token) {
          BGMatch.fetch('/userinfo')
            .then(response => response.json())
            .then(usuario => this.usuario = usuario)
            .catch(error => console.error(error));
        }
      }
    },

    mounted() {
      this.$router.app.$on('check-user', user => {
        if (user) {
          this.usuario = user;
        }
        else {
          this.logout();
        }
      });
    }
  }
</script>
