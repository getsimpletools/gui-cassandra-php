
/*eslint-disable*/
import ApiService from '../../services/ApiService'

export const namespaced = true;

export const state = {
    username: '',
    showLogin: false,
}

export const mutations = {

    SET_USERNAME(state, username) {
        state.username = username ? username : ''
    },

    SET_SHOW_LOGIN(state, showLogin) {
        state.showLogin = showLogin
    }
}


export const actions = {
    showLogin({commit, dispatch}) {

        commit('SET_SHOW_LOGIN', true)

    },


    setUsername({commit, dispatch}){
        commit('SET_USERNAME', ApiService.getCookie('username'))
    },
    logout({commit, dispatch}) {
        return new Promise((resolve, reject) => {
            ApiService.post('/api/logout')
                .then(res => {
                    location.reload();
                    resolve(res);
                })
                .catch(error => {
                    reject(error);
                })
                .finally(() => {
                })
        })
    },

    login({commit, dispatch},{username, password}) {
        return new Promise((resolve, reject) => {
            ApiService.post('/api/login', {username, password})
                .then(res => {
                    //commit('SET_KEYSPACES', res.data.body.keyspaces)
                    resolve(res);
                })
                .catch(error => {
                    // if(error.response.status == 401)
                    // {
                    //
                    // }

                    reject(error);

                })
                .finally(() => {

                })
        })
    },
}
