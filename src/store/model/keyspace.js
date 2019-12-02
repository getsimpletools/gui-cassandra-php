
/*eslint-disable*/
import ApiService from '../../services/ApiService'

export const namespaced = true;

export const state = {
	keyspaces: ['test']
}

export const mutations = {

	SET_KEYSPACES(state, keyspaces) {
		state.keyspaces = keyspaces
	}
}


export const actions = {
	getKeyspaces({commit, dispatch}) {
		return new Promise((resolve, reject) => {
			ApiService.post(ApiService.apiUrl+'/api/get.keyspaces', {})
					.then(res => {
						commit('SET_KEYSPACES', res.data.body.keyspaces)
						resolve(res);
					})
					.catch(error => {
						reject(error);
					})
					.finally(() => {

					})
		})
	},
}
