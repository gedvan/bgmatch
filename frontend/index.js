import Vue from 'vue';
import VueRouter from "vue-router";
import BootstrapVue from 'bootstrap-vue';
import vSelect from 'vue-select';
import { library } from '@fortawesome/fontawesome-svg-core'
import {
  faEdit,
  faExternalLinkAlt,
  faMedal,
  faPlus,
  faSearch, faSortDown, faSortUp,
  faSpinner,
  faTrash
} from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import App from './App.vue';
import router from './router';
import './scss/main.scss';

library.add(faSearch, faSpinner, faExternalLinkAlt, faMedal, faEdit,
  faPlus, faTrash, faSortUp, faSortDown)

Vue.component('font-awesome-icon', FontAwesomeIcon)

Vue.config.productionTip = false;
Vue.use(BootstrapVue);
Vue.use(VueRouter);

Vue.component('v-select', vSelect);

Vue.filter('plural', function(value, singular, plural) {
  return value === 1 ? singular : plural;
});

Vue.filter('data_br', function (value) {
  var test = /^(\d\d\d\d)-(\d\d)-(\d\d)$/.exec(value);
  if (test) {
    return test[3] + '/' + test[2] + '/' + test[1];
  }
});

const app = new Vue({
  render: h => h(App),
  router
})
app.$mount('#app');
