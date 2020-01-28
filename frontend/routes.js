import ListaJogos from "./views/ListaJogos.vue";
import ListaPartidas from "./views/ListaPartidas.vue";
import Jogadores from "./views/Jogadores.vue";

const routes = [
  { path: '/', component: ListaJogos },
  { path: '/partidas', component: ListaPartidas },
  { path: '/jogadores', component: Jogadores }
];

export default routes;
