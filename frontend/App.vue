<template>
  <div id="app" class="container">

    <header id="header">
      <div class="branding">
        <Logo class="logo" />
        <h1 class="app-name sr-only">BGMatch</h1>
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

  export default {
    components: {
      Logo
    },

    data() {
      return {
      }
    },

    methods: {
      isLocalhost() {
        return window.location.hostname === 'localhost';
      }
    }
  }
</script>

<style lang="scss">
  @import "scss/includes";

  #header {
    .branding {
      padding: 30px 0 20px;
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
