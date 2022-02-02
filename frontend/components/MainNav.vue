<template>
  <nav>
    <div class="container">
      <b-nav>
        <b-nav-item to="/" :active="$route.path === '/'">
          Jogos
        </b-nav-item>
        <b-nav-item to="/partidas" :active="$route.path === '/partidas'">
          Partidas
        </b-nav-item>
        <b-nav-item to="/ranking" :active="$route.path.startsWith('/ranking')">
          Ranking
        </b-nav-item>
        <b-nav-item to="/jogadores" :active="$route.path === '/jogadores'">
          Jogadores
        </b-nav-item>
        <b-nav-item to="/estatisticas" :active="$route.path === '/estatisticas'">
          Estatísticas
        </b-nav-item>
      </b-nav>
    </div>
  </nav>
</template>

<script>
export default {
  props: {
    'route-path': String,
  }
}
</script>
