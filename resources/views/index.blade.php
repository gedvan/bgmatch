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

  <link rel="stylesheet" href="app.css">

  <title>{{ config('app.name') }}</title>
</head>
<body>

<div class="container">

  <header id="header" class="mb-4">
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

      <div class="toolbar border rounded mb-3 px-4 pt-4 pb-2 bg-light">
        <div class="row">

          <div class="col-md-4 form-group">
            <label>Tipos</label>
            <div class="dropdown">
              <button class="btn btn-outline-secondary dropdown-toggle w-100 text-left" type="button" data-toggle="dropdown">
                @{{ tiposSelecionados }}
              </button>
              <div class="dropdown-menu">
                <div class="form-check dropdown-item" v-for="tipo of tipos">
                  <label><input type="checkbox" name="tipos[]" :value="tipo.key" v-model="tipo.checked"> @{{ tipo.nome }}</label>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4 form-group">
            <label>Nº de Jogadores</label>
            <input type="number" name="num" class="form-control" v-model="num" min="1">
          </div>

          <div class="col-md-4 form-group">
            <label>Ordenação</label>
            <div class="input-group">
              <select name="sort" class="form-control" v-model="sort">
                <option value="alfa">Ordem alfabética</option>
                <option value="min">Min. jogadores</option>
                <option value="max">Max. jogadores</option>
                <option value="last">Última partida</option>
                <option value="qtd">Qtd. partidas</option>
              </select>
              <select name="sort_dir" class="form-control" v-model="sort_dir">
                <option value="asc">ASC</option>
                <option value="desc">DESC</option>
              </select>
            </div>
          </div>

        </div>
      </div>

      <div class="text-center mb-4">
        @{{ jogosFiltrados.length }} jogo(s).
      </div>

      <ul class="grid">
        <li v-for="jogo in jogosFiltrados">
          <img v-bind:src="jogo.img_ludo">
          <h3>@{{ jogo.nome }}</h3>
          <div class="text-muted small">
            @{{ nomeTipo(jogo.tipo) }}
          </div>
          <div class="text-muted small">
            @{{ numJogadores(jogo) }}
          </div>
        </li>
      </ul>

      <div class="text-center my-4">
        <button class="btn btn-primary" @click="atualizaJogos" v-bind:disabled="atualizando">Atualizar acervo</button>
      </div>

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
