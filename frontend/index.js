import Vue from 'vue';
import BootstrapVue from 'bootstrap-vue'
import App from './App.vue';
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

var BGMatch = {
  apiUrl: window.location.protocol + '//' + window.location.host + '/api',
  ludopediaUrl: 'https://www.ludopedia.com.br'
};

Vue.use(BootstrapVue);

Vue.filter('plural', function(value, singular, plural) {
  return value === 1 ? singular : plural;
});

new Vue({
  render: h => h(App)
}).$mount('#app');
