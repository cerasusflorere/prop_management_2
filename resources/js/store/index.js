import Vue from 'vue'
import Vuex from 'vuex'

import error from './error'
import message from './message'

Vue.use(Vuex)

const store = new Vuex.Store({
  modules: {
    error,
    message
  }
})

export default store