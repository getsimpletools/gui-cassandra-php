<template>
  <v-container
    fill-height
    fluid
    grid-list-xl
  >
    <v-layout
      justify-center
      wrap
    >
      <v-flex
        md12
      >
				<v-select style="max-width: 500px" box label="Keyspace" v-model="keyspace" @change="loadTables" :items="keyspaces"></v-select>
				<v-select  style="max-width: 500px" box v-model="currentTable" @change="loadTable" label='table' v-if="keyspace && tables" :items="tables" item-text="name" item-value="name"></v-select>

        <cassandra-table
						v-if="keyspace && currentTable && table"
          color="green"
          title="Simple Table"
          text=""
						:keyspace="keyspace"
        >
        </cassandra-table>
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
		currentTable: null,

    headers: [
      {
        sortable: false,
        text: 'Name',
        value: 'name'
      },
      {
        sortable: false,
        text: 'Country',
        value: 'country'
      },
      {
        sortable: false,
        text: 'City',
        value: 'city'
      },
      {
        sortable: false,
        text: 'Salary',
        value: 'salary',
        align: 'right'
      }
    ],
    items: [
      {
        name: 'Dakota Rice',
        country: 'Niger',
        city: 'Oud-Tunrhout',
        salary: '$35,738'
      },
      {
        name: 'Minerva Hooper',
        country: 'Curaçao',
        city: 'Sinaai-Waas',
        salary: '$23,738'
      }, {
        name: 'Sage Rodriguez',
        country: 'Netherlands',
        city: 'Overland Park',
        salary: '$56,142'
      }, {
        name: 'Philip Chanley',
        country: 'Korea, South',
        city: 'Gloucester',
        salary: '$38,735'
      }, {
        name: 'Doris Greene',
        country: 'Malawi',
        city: 'Feldkirchen in Kārnten',
        salary: '$63,542'
      }, {
        name: 'Mason Porter',
        country: 'Chile',
        city: 'Gloucester',
        salary: '$78,615'
      }
    ]
  }),
		methods: {
			...mapActions('keyspace', ['getKeyspaces']),
			...mapActions('table', ['getTables','getTable']),
			loadTables () {
				this.getTables(this.keyspace)
			},
			loadTable () {
				this.getTable({
					keyspace: this.keyspace,
					table: this.currentTable
				})
			}
		},
		computed: {
			...mapState({
				'keyspaces': state => state.keyspace.keyspaces,
				'tables': state => state.table.tables,
				'table': state => state.table.table,
			}),
		},
		created(){
			var self = this;
			this.getKeyspaces();
		},
}
</script>
