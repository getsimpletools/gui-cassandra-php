
/*eslint-disable*/
import ApiService from '../../services/ApiService'

export const namespaced = true;

export const state = {
	tables: null,
	table: null,
	tableData: null,
	loading: false
}

export const mutations = {

	SET_TABLES(state, tables) {
		state.tables = tables
	},
	SET_TABLE(state, table) {
		state.table = table
	},
	SET_TABLE_DATA(state, tableData) {
		state.tableData = tableData
	},
	REMOVE_TABLE_DATA_ITEM(state, index) {
		state.tableData.splice(index, 1);
	},
	SET_LOADING(state, isLoading) {
		state.loading = isLoading;
	},
}


export const actions = {
	getTables({commit, dispatch}, keyspace) {
		commit('SET_LOADING', true);
		return new Promise((resolve, reject) => {
			ApiService.post('/api/get.tables', {
				keyspace: keyspace
			})
					.then(res => {
						commit('SET_TABLES', res.body.tables)
						resolve(res);
					})
					.catch(error => {
						reject(error);
					})
					.finally(() => {
						commit('SET_LOADING', false);
					})
		})
	},
	getTable({commit, dispatch}, {keyspace,table}) {
		commit('SET_LOADING', true);
		return new Promise((resolve, reject) => {
			ApiService.post('/api/get.table', {keyspace,table})
					.then(res => {
						commit('SET_TABLE', res.body.table)
						dispatch('getTableData',{keyspace,table,keys:null,limit:500})
						resolve(res);
					})
					.catch(error => {
						reject(error);
					})
					.finally(() => {
						commit('SET_LOADING', false);
					})
		})
	},
	getTableData({commit, dispatch}, {keyspace,table,keys,limit}) {
		commit('SET_LOADING', true);
		return new Promise((resolve, reject) => {
			ApiService.post('/api/get.table.data', {keyspace,table,keys,limit})
					.then(res => {
						commit('SET_TABLE_DATA', res.body.tableData)
						resolve(res);
					})
					.catch(error => {
						reject(error);
					})
					.finally(() => {
						commit('SET_LOADING', false);
					})
		})
	},
	setTableItem({commit, dispatch}, {keyspace,table,keys,item}) {
		commit('SET_LOADING', true);
		return new Promise((resolve, reject) => {
			ApiService.post('/api/set.table.item', {keyspace,table,keys,item})
					.then(res => {
						//commit('SET_TABLE_DATA', res.data.body.tableData)
						resolve(res);
					})
					.catch(error => {
						reject(error);
					})
					.finally(() => {
						commit('SET_LOADING', false);
					})
		})
	},
	removeTableItem({commit, dispatch}, {keyspace,table,keys, index}) {
		commit('SET_LOADING', true);
		return new Promise((resolve, reject) => {
			ApiService.post('/api/remove.table.item', {keyspace,table,keys})
					.then(res => {
						commit('REMOVE_TABLE_DATA_ITEM', index);
						resolve(res);
					})
					.catch(error => {
						reject(error);
					})
					.finally(() => {
						commit('SET_LOADING', false);
					})
		})
	},

}
