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

    <footer id="footer" role="contentinfo">
      <div class="container">
        Desenvolvido por <a href="mailto:gedvan@gmail.com">Gedvan Dias</a>.
      </div>
    </footer>

    <div class="responsive-indicator" v-if="isLocalhost">
      <span class="d-sm-none">xs</span>
      <span class="d-none d-sm-flex d-md-none">sm</span>
      <span class="d-none d-md-flex d-lg-none">md</span>
      <span class="d-none d-lg-flex d-xl-none">lg</span>
      <span class="d-none d-xl-flex">xl</span>
    </div>

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
        return window.location.hostname === 'localhost' || window.location.port === '8080';
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
      }
    },

    mounted() {
      this.$router.app.$on('check-user', data => {
        if (data) {
          this.usuario = data.user;
          if (data.token) {
            window.localStorage.setItem('token', data.token);
          }
        }
        else {
          this.logout();
        }
      });
    }
  }
</script>
