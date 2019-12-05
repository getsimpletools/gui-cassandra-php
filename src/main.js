// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
/* eslint-disable */
// Components
import './components'


import * as serverConfig  from './../_env/main.config.json';
import * as cassanraConfig  from './../_env/cassandra.config.json';

/* eslint-disable */
// Plugins
import './plugins'

// Sync router with store
import { sync } from 'vuex-router-sync'

// Application imports
import App from './App'
import i18n from '@/i18n'
import router from '@/router'
import store from '@/store'

// Sync store with router
sync(store, router)

delete cassanraConfig.default.username;
delete cassanraConfig.default.password;

Vue.config.productionTip = false;
Vue.prototype.$config = {
  serverConfig : serverConfig.default,
  cassanraConfig: cassanraConfig.default
};



/* eslint-disable no-new */
new Vue({
  i18n,
  router,
  store,
  render: h => h(App)
}).$mount('#app')
