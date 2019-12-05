<template>
  <v-app :class="showLogin ? 'need-login' : ''">
<!--
    <core-filter />
-->

    <core-toolbar />

    <core-drawer />

    <core-view />
  </v-app>
</template>


<script>
    import {
        mapState,mapActions
    } from 'vuex'

    export default {
        computed: {
            ...mapState({
                'showLogin': state => state.user.showLogin,
            }),
        },
        methods: {
            ...mapActions('user', ['setUsername']),
        },
        mounted() {
            this.setUsername();
        }
    }

</script>

<style lang="scss">
@import '@/styles/index.scss';

/* Remove in 1.2 */
.v-datatable thead th.column.sortable i {
  vertical-align: unset;
}
</style>




