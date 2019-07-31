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

<div id="bgmatch-app" class="container">

  <header id="header" class="mb-4">
    <h1 class="app-name text-center m-4">{{ config('app.name') }}</h1>

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

    <div id="lista-jogos">

      <div class="toolbar border rounded mb-3 px-4 pt-4 pb-2 bg-light">
        <div class="row">

          <div class="col-md-5 form-group">
            <label>Tipos</label>
            <b-dropdown id="dd-tipos" :text="tiposSelecionados" ref="dropdown" variant="outline-secondary" class="d-flex align-items-start">
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
        @{{ jogosFiltrados.length }} @{{ jogosFiltrados.length | plural('jogo', 'jogos') }}.
      </div>

      <ul class="grid">
        <li v-for="jogo in jogosFiltrados">
          <a href="#" v-b-modal.modal-jogo @click.prevent="selecionaJogo(jogo)">
            <img :src="jogo.img_ludo">
          </a>
          <h3>@{{ jogo.nome }}</h3>
          <div class="text-muted small">
              <i class="fas fa-user-friends"></i> @{{ numJogadores(jogo) }}
              / @{{ nomeTipo(jogo.tipo) }}
          </div>
        </li>
      </ul>

      <b-modal id="modal-jogo" size="lg" hide-footer>
        <template #modal-title>
          @{{ jogoModal.nome }}
          <b-link :href="urlJogoLudopedia(jogoModal)" target="_blank" class="small" title="Abrir na Ludopedia">
            <i class="fas fa-external-link-alt"></i>
          </b-link>
        </template>
        <div v-if="jogoModal" class="media">
          <img :src="jogoModal.img_ludo" class="mr-3">
          <div class="media-body">
            <dl class="row">
              <dt class="col-sm-3">Nº de jogadores</dt>
              <dd class="col-sm-9">@{{ numJogadores(jogoModal) }}</dd>
              <dt class="col-sm-3">Categoria</dt>
              <dd class="col-sm-9">
                <span v-if="!editandoTipo">@{{ nomeTipo(jogoModal.tipo) }}</span>
                <b-form-select v-if="editandoTipo" v-model="tipoEdicao" :options="tiposOptions" size="sm" class="w-auto"></b-form-select>
                <b-button v-if="!editandoTipo" variant="link" size="sm" @click="iniciaEdicaoTipo"><i class="far fa-edit"></i></b-button>
                <b-button-group v-if="editandoTipo" size="sm">
                  <b-button variant="outline-success" @click="salvaEdicaoTipo"><i class="far fa-check-circle"></i></b-button>
                  <b-button variant="outline-danger" @click="cancelaEdicaoTipo"><i class="far fa-times-circle"></i></b-button>
                </b-button-group>
              </dd>
              <template v-if="jogoModal.expansoes.length">
                <dt class="col-sm-3">Expansões</dt>
                <dd class="col-sm-9" v-if="jogoModal.expansoes.length">
                  <ul class="m-0 pl-3">
                    <li v-for="exp in jogoModal.expansoes">
                      @{{ exp.nome }}
                      <b-link :href="urlJogoLudopedia(exp)" target="_blank" class="small" title="Abrir na Ludopedia">
                        <i class="fas fa-external-link-alt"></i>
                      </b-link>
                    </li>
                  </ul>
                </dd>
              </template>
            </dl>
          </div>
        </div>
      </b-modal>

      <div class="text-center my-4">
        <button class="btn btn-primary" @click="atualizaJogos" v-bind:disabled="atualizando">Atualizar acervo</button>
      </div>

    </div>

  </main>

</div>


<!-- Vue.js -->
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.js"></script>

<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/b55aa8c7d6.js"></script>

<script src="app.js"></script>

</body>
</html>
