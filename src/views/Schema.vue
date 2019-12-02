<template>
	<v-container
			fill-height
			fluid
			grid-list-xl
	>


		<v-layout
				justify-center
				wrap
				align-content-space-around="true"
		>
			<v-flex xs12>
				<v-select label="Keyspace" v-model="keyspace" @change="loadTables" :items="keyspaces"></v-select>
			</v-flex>

			<v-flex
					xs12
					sm4
					md3
					lg2
					v-if="keyspace && tables"
					v-for="table in tables"
					:key="table.name"
			>

				<v-card>
						<v-card-title><h4>{{ table.name }}</h4></v-card-title>
						<v-divider></v-divider>
						<v-list dense>
							<v-list-tile :key="table.name+column" v-for="(type, column) in table.columns">
								<v-list-tile-content>
									<span>
										{{column}}
										<v-icon small v-if="table.partitionKey.hasOwnProperty(column)">mdi-dns</v-icon>
										<v-icon small v-if="table.primaryKey.hasOwnProperty(column)">mdi-key</v-icon>
										<v-icon small v-if="table.indexes.hasOwnProperty(column)">mdi-key</v-icon>
									</span>

								</v-list-tile-content>
								<v-list-tile-content class="align-end">{{type}}</v-list-tile-content>
							</v-list-tile>
						</v-list>
					</v-card>
			</v-flex>
		</v-layout>
	</v-container>
</template>

<script>
	/*eslint-disable*/
	import { mapState, mapActions } from 'vuex'

	export default {
		//components: {VSelect},
		data: () => ({
			keyspace: null,
		}),
		methods: {
			...mapActions('table', ['getTables']),
			...mapActions('keyspace', ['getKeyspaces']),
			loadTables () {
				this.getTables(this.keyspace)
			},
		},
		computed: {
			...mapState({
				'tables': state => state.table.tables,
				'keyspaces': state => state.keyspace.keyspaces,
			}),
		},
		created(){
			var self = this;
			this.getKeyspaces();
		},
	}
</script>
<style lang="scss">
	.v-list--dense .v-list__tile:not(.v-list__tile--avatar) {
		height: 22px;
	}
	.v-card__title h4{
		margin: 0!important;
	}
</style>
