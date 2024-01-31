import ListaJogos from "./pages/ListaJogos";
import ListaPartidas from "./pages/ListaPartidas";
import Jogadores from "./pages/Jogadores";
import RankingWrapper from "./pages/RankingWrapper";
import Login from "./pages/Login";
import VueRouter from "vue-router";
import BGMatch from "./BGMatch";

// Define todas as rotas da aplicação
const router = new VueRouter({
  routes: [
    { name: 'index', path: '/', component: ListaJogos },
    { name: 'login', path: '/login', component: Login },
    { name: 'partidas', path: '/partidas', component: ListaPartidas },
    { name: 'jogadores', path: '/jogadores', component: Jogadores },
    { name: 'ranking', path: '/ranking', component: RankingWrapper },
    { name: 'ranking-ano', path: '/ranking/:ano', component: RankingWrapper }
  ]
});

// Ao navegar para qualquer rota (exceto login), verifica se possui o token JWT
// e consulta o backend para ver se o mesmo ainda está ativo.
router.beforeEach((to, from, next) => {
  if (to.name !== 'login') {
    const token = window.localStorage.getItem('token');
    if (token) {
      BGMatch.fetch('/userinfo')
        .then(response => response.json())
        .then(user => router.app.$emit('check-user', user))
        .catch(error => router.app.$emit('check-user', null));
    }
    else {
      next({name: 'login'});
      return;
    }
  }
  next();
});

export default router;
