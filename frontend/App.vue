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
          <b-nav-item-dropdown text="Ranking">
            <b-dropdown-item to="/ranking/2020" :active="$route.path === '/ranking/2020'">2020</b-dropdown-item>
            <b-dropdown-item to="/ranking/2019" :active="$route.path === '/ranking/2019'">2019</b-dropdown-item>
            <b-dropdown-item to="/ranking/2018" :active="$route.path === '/ranking/2018'">2018</b-dropdown-item>
          </b-nav-item-dropdown>
          <b-nav-item to="/jogadores" :active="$route.path === '/jogadores'">
            Jogadores
          </b-nav-item>
        </b-nav>
      </nav>
    </header>

    <main id="main">
      <router-view></router-view>
    </main>

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
      .nav {
        border-bottom: 3px solid #ddd;
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
</style>
