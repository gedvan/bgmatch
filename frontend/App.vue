<template>
  <div id="app" class="container">

    <template v-if="$route.name == 'login'">

      <div id="login" class="row justify-content-center">
        <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5">

          <header id="header" class="mt-5 mb-2">
            <div class="branding text-center">
              <Logo class="logo" />
              <h1 class="app-name sr-only">BGMatch</h1>
            </div>
          </header>

          <main id="main">
            <router-view :key="$route.fullPath"></router-view>
          </main>

        </div>
      </div>

    </template>
    <template v-else>

      <header id="header">
        <div class="header-top">
          <div class="branding">
            <Logo class="logo" />
            <h1 class="app-name sr-only">BGMatch</h1>
          </div>
          <div class="user-info">
            Logado como: <strong>{{ usuario.usuario }}</strong>
            | <a href="#" @click.prevent="logout">sair</a>
          </div>
        </div>

        <nav class="main-nav">
          <b-nav>
            <b-nav-item to="/" :active="$route.path === '/'">
              Jogos
            </b-nav-item>
            <b-nav-item to="/partidas" :active="$route.path === '/partidas'">
              Partidas
            </b-nav-item>
            <b-nav-item to="/ranking" :active="$route.path === '/ranking'">
              Ranking
            </b-nav-item>
            <b-nav-item to="/jogadores" :active="$route.path === '/jogadores'">
              Jogadores
            </b-nav-item>
            <b-nav-item to="/estatisticas" :active="$route.path === '/estatisticas'">
              Estatísticas
            </b-nav-item>
          </b-nav>
        </nav>
      </header>

      <main id="main">
        <router-view :key="$route.fullPath"></router-view>
      </main>

    </template>

    <template v-if="isLocalhost()">
      <div class="responsive-indicator d-sm-none">xs</div>
      <div class="responsive-indicator d-none d-sm-flex d-md-none">sm</div>
      <div class="responsive-indicator d-none d-md-flex d-lg-none">md</div>
      <div class="responsive-indicator d-none d-lg-flex d-xl-none">lg</div>
      <div class="responsive-indicator d-none d-xl-flex">xl</div>
    </template>

  </div>
</template>

<script>
  import Logo from './assets/images/logo.svg';
  import router from "./router";
  import BGMatch from "./BGMatch";

  export default {
    components: {
      Logo
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
      }
    },

    methods: {
      isLocalhost() {
        return window.location.hostname === 'localhost';
      },

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

<style lang="scss">
  @import "scss/includes";

  #header {
    .header-top {
      display: flex;
    }
    .branding {
      padding: 30px 0 20px;
    }
    .user-info {
      margin-left: auto;
      padding: .5rem;
    }
    .main-nav {
      overflow-x: auto;
      .nav {
        border-bottom: 3px solid #ddd;
        flex-wrap: nowrap;
        .nav-item {
          margin-bottom: -3px;
          > a {
            border-bottom: 3px solid transparent;
            font-weight: $font-weight-bold;
            color: $body-color;
            &:hover, &:focus {
              border-bottom-color: #bbb;
            }
            &.active {
              border-bottom-color: $primary;
              color: $primary;
            }
          }
        }
      }
    }
  }

  #main {
    margin-bottom: 1rem;
  }

  .responsive-indicator {
    position: fixed;
    right: 15px;
    top: 15px;
    background: red;
    color: white;
    font-size: 150%;
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: .5;
  }
</style>
