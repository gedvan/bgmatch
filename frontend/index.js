import Vue from 'vue';
import VueRouter from "vue-router";
import BootstrapVue from 'bootstrap-vue';
import vSelect from 'vue-select';
import App from './App.vue';
import routes from './routes';
import './scss/main.scss';


Vue.config.productionTip = false;
Vue.use(BootstrapVue);
Vue.use(VueRouter);

Vue.component('v-select', vSelect);

Vue.filter('plural', function(value, singular, plural) {
  return value === 1 ? singular : plural;
});

new Vue({
  render: h => h(App),
  router: new VueRouter({routes})
}).$mount('#app');
