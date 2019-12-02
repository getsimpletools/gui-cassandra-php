<template>

  <v-card
    v-bind="$attrs"
    :style="styles"
    v-on="$listeners"
  >

		<v-snackbar
				:color="snackbarColor"
				:top="true"
				v-model="snackbar"
				dark
		>
			<v-icon
					color="white"
					class="mr-3"
			>
				mdi-bell-plus
			</v-icon>
			{{snackbarText}}
			<v-icon
					size="16"
					@click="snackbar = false"
			>
				mdi-close-circle
			</v-icon>
		</v-snackbar>

		<v-dialog v-model="dialog" width="1600px">
			<v-card>
				<v-card-title>
					<span class="headline">Edit Item</span>
				</v-card-title>
				<v-card-text>
					<v-textarea
							box
							auto-grow
							v-model="editingItemJson"
					></v-textarea>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn color="green darken-1" flat="flat" @click="dialog = false">cancel</v-btn>
					<v-btn color="blue darken-1"  @click="saveItem">Save</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>

    <helper-offset
      v-if="hasOffset"
      :inline="inline"
      :full-width="fullWidth"
      :offset="offset"
    >
      <v-card
        v-if="!$slots.offset"
        :color="color"
        :class="`elevation-${elevation}`"
        class="v-card--material__header"
        dark
      >
        <span>
          <h4
            class="title font-weight-light mb-2"
            v-text="table.name"
          />

					<v-layout
							justify-left
							row
							wrap
					>
						<v-flex md2 xs3 v-for="(keyType, keyName) in table.primaryKey" :key="keyName" >
							<v-text-field v-model="keys[keyName]" class="primary-key" box :label="keyName" ></v-text-field>
						</v-flex>
					<v-flex xs1>
						 <v-btn outline fab color="teal" :loading="loading" @click="loadTableData">
							<v-icon>mdi-send</v-icon>
						</v-btn>
					</v-flex>
					</v-layout>

        </span>
      </v-card>
      <slot
        v-else
        name="offset"
      />
    </helper-offset>

    <v-card-text>
			<v-data-table
					v-if="tableData"
					:headers="headers"
					:items="tableData"
			>
				<template
						slot="headerCell"
						slot-scope="{ header }"
				>
              <span
									class="subheading font-weight-light text-success text--darken-3"
									v-text="header.text"
							/>
				</template>
				<template
						slot="items"
						slot-scope="{ item, index }"
				>
					<td>
						<v-btn flat icon color="red" @click="editItem(item)">
							<v-icon>mdi-pencil</v-icon>
						</v-btn>
						<v-btn flat icon @click="removeItem(item, index)">
							<v-icon>mdi-delete</v-icon>
						</v-btn>
					</td>
					<td :key="key" v-for="(val,key) in item">

						<template v-if="typeof val === 'object'">object</template>
						<template v-else-if="typeof val === 'array'">array</template>
						<template v-else>{{ val }}</template>
					</td>
				</template>
			</v-data-table>
    </v-card-text>

    <v-divider
      v-if="$slots.actions"
      class="mx-3"
    />

    <v-card-actions v-if="$slots.actions">
      <slot name="actions" />
    </v-card-actions>
  </v-card>
</template>

<script>

	import { mapState, mapActions } from 'vuex'

export default {
	inheritAttrs: false,
	data: () => ({
		keys:{},
		dialog: false,
		editingItem:{},
		editingItemJson:'',

		snackbar: false,
		snackbarColor:'success',
		snackbarText: ''
	}),


  props: {
    color: {
      type: String,
      default: 'secondary'
    },
    elevation: {
      type: [Number, String],
      default: 10
    },
    inline: {
      type: Boolean,
      default: false
    },
    fullWidth: {
      type: Boolean,
      default: false
    },
    offset: {
      type: [Number, String],
      default: 24
    },
    title: {
      type: String,
      default: undefined
    },
    text: {
      type: String,
      default: undefined
    },
		keyspace: {
			type: String,
			default: null
		}
  },
	methods: {
		...mapActions('table', ['getTableData','setTableItem','removeTableItem']),
		loadTableData () {
			var self = this;
			this.getTableData({
				keyspace: this.keyspace,
				table: this.table.name,
				keys: this.keys,
				limit: 10
			}).catch(error =>{
				self.snackbarColor = 'error';
				self.snackbarText = error.response.data.msg;
				self.snackbar = true;
			})
		},
		editItem(item){
			this.editingItem = item;
			this.editingItemJson = JSON.stringify(item, null, 2);
			this.dialog = true;
		},
		saveItem(){
			var self = this;
			this.dialog = false;
			var keys = {};
			var item = JSON.parse(this.editingItemJson);



			Object.keys(this.table.primaryKey).forEach(function(key,type) {
				keys[key] = item[key];
			});

			this.setTableItem({
				keyspace: this.keyspace,
				table: this.table.name,
				keys: keys,
				item: item
			}).then(res => {
				if(res.data.status == 'OK')
				{
					self.snackbarColor = 'success';
					self.snackbarText = 'Item Saved!';

					Object.keys(item).forEach(function(key,index) {
						self.editingItem[key] = item[key];
					});
				}
				else
				{
					self.snackbarColor = 'error';
					self.snackbarText = 'Oops something went wrong :(';
				}
				self.snackbar = true;
			}).catch(error =>{
				self.snackbarColor = 'error';
				self.snackbarText = error.response.data.msg;
				self.snackbar = true;
			})
		},
		removeItem(item, index){
			var self = this;
			var keys = {};
			Object.keys(this.table.primaryKey).forEach(function(key,type) {
				keys[key] = item[key];
			});

			if(confirm('Are you sure you want to delete item: '+JSON.stringify(keys)+' ?'))
			{
				console.log('remove',{
					keyspace: this.keyspace,
					table: this.table.name,
					keys,
					index
				});

				this.removeTableItem({
					keyspace: this.keyspace,
					table: this.table.name,
					keys,
					index
				}).then(res => {
					if(res.data.status == 'OK')
					{
						self.snackbarColor = 'success';
						self.snackbarText = 'Item removed!';
					}
					else
					{
						self.snackbarColor = 'error';
						self.snackbarText = 'Oops something went wrong :(';
					}
					self.snackbar = true;
				}).catch(error =>{
					self.snackbarColor = 'error';
					self.snackbarText = error.response.data.msg;
					self.snackbar = true;
				})
			}
		}
	},
  computed: {
		...mapState({
			'table': state => state.table.table,
			'tableData': state => state.table.tableData,
			'loading': state => state.table.loading,
		}),
    hasOffset () {
      return this.$slots.header ||
        this.$slots.offset ||
        this.title ||
        this.text
    },
		headers(){
			var headers = [{
				sortable: false,
				text: 'Actions',
				value: 'Actions'
			}];

			Object.keys(this.table.columns).forEach(function(key,index) {
					headers.push({
						sortable: false,
						text: key,
						value: key
					});

			});


			return headers;
		},
    styles () {
      if (!this.hasOffset) return null

      return {
        marginBottom: `${this.offset}px`,
        marginTop: `${this.offset * 2}px`
      }
    }
  },
	watch: {
		table(newVal){
			this.keys = {}
		}
	}
}
</script>

<style lang="scss">
	.v-datatable .v-btn .v-btn__content .v-icon {
		color: #999;
	}
	.primary-key .v-text-field__slot .v-label{
		color: #fff !important;
	}
  .v-card--material {
    &__header {
      &.v-card {
        border-radius: 4px;
      }
    }
  }
</style>
