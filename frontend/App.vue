<template>
  <div id="app" class="container">

    <header id="header" class="mb-4">
      <h1 class="app-name text-center m-4">BG Match</h1>

      <nav class="main-nav">
        <ul class="nav nav-tabs justify-content-center">
          <li class="nav-item">
            <a class="nav-link active" href="/">Jogos</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/">Partidas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/">Ranking</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/">Jogadores</a>
          </li>
        </ul>
      </nav>
    </header>

    <main id="main">
      <lista-jogos></lista-jogos>
    </main>

  </div>
</template>

<script>
  import ListaJogos from "./ListaJogos.vue";

  export default {
    components: {
      ListaJogos
    }
  }
</script>
