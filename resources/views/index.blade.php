<!doctype html>
<html lang="pt-br">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <base href="/">

  <!-- Bootstrap and BootstrapVue CSS -->
  <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
  <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

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
          <a class="nav-link active" href="jogos">Jogos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="jogos">Partidas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="jogos">Ranking</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="jogos">Jogadores</a>
        </li>
      </ul>
    </nav>
  </header>

  <main id="main">

    <div id="lista-jogos">

      <div class="toolbar border rounded mb-3 px-4 pt-4 pb-2 bg-light">
        <div class="row">

          <div class="col-md-5 form-group">
            <label>Tipos</label>
            <b-dropdown id="dd-tipos" :text="tiposSelecionados" ref="dropdown" variant="outline-secondary" class="d-flex">
              <b-dropdown-form>
                <b-form-checkbox-group id="check-tipos" v-model="tipos" :options="tiposOptions" name="tipos" stacked></b-form-checkbox-group>
              </b-dropdown-form>
            </b-dropdown>
          </div>

          <div class="col-md-2 form-group">
            <label for="num-jogadores">Nº de Jogadores</label>
            <b-form-input type="number" v-model="num" id="num-jogadores" min="1"></b-form-input>
          </div>

          <div class="col-md-5 form-group">
            <label>Ordenação</label>
            <div class="input-group">
              <b-form-select v-model="sort" :options="sortOptions"></b-form-select>
              <b-form-checkbox v-model="sortInv" switch id="sort-inv">Inverter</b-form-checkbox>
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


<!-- Vue.js -->
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.js"></script>
<script src="app.js"></script>

</body>
</html>
