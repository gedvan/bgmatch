<!doctype html>
<html lang="pt-br">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <base href="/">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

  <title>Hello, world!</title>
</head>
<body>

<div class="container">

  <header id="header">
    <h1 class="app-name text-center">{{ config('app.name') }}</h1>

    <nav class="main-nav">
      <ul class="nav nav-pills justify-content-center">
        <li class="nav-item">
          <a class="nav-link" href="jogos">Jogos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="jogos">Partidas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="jogos">Jogadores</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="jogos">Ranking</a>
        </li>
      </ul>
    </nav>
  </header>

  <main id="main">
    <div id="lista-jogos">
      <ul>
        <li v-for="jogo in jogos">
          <img v-bind:src="jogo.img_ludo">
          <h3>@{{ jogo.nome }}</h3>
        </li>
      </ul>
    </div>
  </main>

</div>


<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>

<!-- Vue.js -->
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="app.js"></script>

</body>
</html>
