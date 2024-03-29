/**
 * Vuex
 *
 * @library
 *
 * https://vuex.vuejs.org/en/
 */

// Lib imports
import Vue from 'vue'
import Vuex from 'vuex'

// Store functionality
import actions from './actions'
import getters from './getters'
import modules from './modules'
import mutations from './mutations'
import state from './state'
import * as keyspace from './model/keyspace.js'
import * as table from './model/table.js'
import * as user from './model/user.js'
modules.keyspace = keyspace;
modules.table = table;
modules.user = user;


Vue.use(Vuex)

// Create a new store
const store = new Vuex.Store({
  actions,
  getters,
  modules,
  mutations,
  state
})

export default store
