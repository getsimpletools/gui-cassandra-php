
/*eslint-disable*/
import ApiService from '../../services/ApiService'

export const namespaced = true;

export const state = {
	keyspaces: ['no keyspaces']
}

export const mutations = {

	SET_KEYSPACES(state, keyspaces) {
		state.keyspaces = keyspaces
	}
}


export const actions = {
	getKeyspaces({commit, dispatch}) {
		return new Promise((resolve, reject) => {
			ApiService.post('/api/get.keyspaces', {})
					.then(res => {
						commit('SET_KEYSPACES', res.body.keyspaces)
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
