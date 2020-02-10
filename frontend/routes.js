import ListaJogos from "./views/ListaJogos.vue";
import ListaPartidas from "./views/ListaPartidas.vue";
import Jogadores from "./views/Jogadores.vue";
import Ranking from "./views/Ranking.vue";

const routes = [
  { path: '/', component: ListaJogos },
  { path: '/partidas', component: ListaPartidas },
  { path: '/jogadores', component: Jogadores },
  { path: '/ranking', component: Ranking },
  { path: '/ranking/:ano', component: Ranking }
];

export default routes;
