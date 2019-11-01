<template>
  <div id="app" class="container">

    <header id="header">
      <div class="branding">
        <Logo class="logo" />
        <h1 class="app-name sr-only">BGMatch</h1>
      </div>

      <nav class="main-nav">
        <ul class="nav">
          <li v-for="menuItem in menuItems" class="nav-item">
            <router-link :to="menuItem.path" class="nav-link" :class="{active: $route.path == menuItem.path}">
              {{ menuItem.title }}
            </router-link>
          </li>
        </ul>
      </nav>
    </header>

    <main id="main">
      <router-view></router-view>
    </main>

  </div>
</template>

<script>
  import Logo from './assets/images/logo.svg';
  import ListaJogos from "./views/ListaJogos.vue";

  export default {
    components: {
      Logo,
      ListaJogos
    },

    data() {
      return {
        menuItems: [
          {
            path: '/',
            title: 'Jogos'
          }, {
            path: '/partidas',
            title: 'Partidas'
          }, {
            path: '/ranking',
            title: 'Ranking'
          }, {
            path: '/jogadores',
            title: 'Jogadores'
          }
        ]
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
          a {
            border-bottom: 3px solid transparent;
            font-weight: $font-weight-bold;
            color: $body-color;
          }
          a:hover, a:focus {
            border-bottom-color: #bbb;
          }
          a.active {
            border-bottom-color: $primary;
            color: $primary;
          }
        }
      }
    }
  }
</style>
