import ListaJogos from "./views/ListaJogos.vue";
import ListaPartidas from "./views/ListaPartidas.vue";

const routes = [
  { path: '/', component: ListaJogos },
  { path: '/partidas', component: ListaPartidas }
];

export default routes;
