<template>
  <div id="app" class="container">

    <header id="header" class="mb-4">
      <h1 class="app-name text-center m-4">BG Match</h1>

      <nav class="main-nav">
        <ul class="nav nav-tabs justify-content-center">
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
  import ListaJogos from "./views/ListaJogos.vue";

  export default {
    components: {
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
