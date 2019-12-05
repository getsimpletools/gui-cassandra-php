<template>
  <div id="core-toolbar-box">

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

    <v-dialog
            class="test"
            v-model="showLogin"
            max-width="400"
            persistent
    >
      <v-card>
        <v-text-field
                label=""
                prepend-inner-icon="mdi-account"
                solo
                style="margin: 0 16px"
                v-model="username"
        ></v-text-field>
        <v-text-field
                label=""
                prepend-inner-icon="mdi-lock"
                style="margin: 0 16px"
                solo
                type="password"
                v-model="password"
        ></v-text-field>

        <v-card-actions>
          <v-spacer></v-spacer>

          <v-btn
                  :loading="loading"
                  color="green darken-1"
                  @click="signIn"
          >
            Login
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-toolbar
            id="core-toolbar"

            flat
            prominent
            style="background: #eee;"
    >
      <div class="v-toolbar-title">
        <v-toolbar-title
                class="tertiary--text font-weight-light"
        >
          <v-btn
                  v-if="responsive"
                  class="default v-btn--simple"
                  dark
                  icon
                  @click.stop="onClickBtn"
          >
            <v-icon>mdi-view-list</v-icon>
          </v-btn>
          {{ title }}
        </v-toolbar-title>
      </div>

      <v-spacer />
      <v-toolbar-items>
        <v-flex
                align-center
                layout
                py-2
        >
         <!-- <v-text-field
                  v-if="responsiveInput"
                  class="mr-4 mt-2 purple-input"
                  label="Search..."
                  hide-details
                  color="purple"
          />
          <router-link
                  v-ripple
                  class="toolbar-items"
                  to="/"
          >
            <v-icon color="tertiary">mdi-view-dashboard</v-icon>
          </router-link>
          <v-menu
                  bottom
                  left
                  content-class="dropdown-menu"
                  offset-y
                  transition="slide-y-transition">
            <router-link
                    v-ripple
                    slot="activator"
                    class="toolbar-items"
                    to="/notifications"
            >
              <v-badge
                      color="error"
                      overlap
              >
                <template slot="badge">
                  {{ notifications.length }}
                </template>
                <v-icon color="tertiary">mdi-bell</v-icon>
              </v-badge>
            </router-link>
            <v-card>
              <v-list dense>
                <v-list-tile
                        v-for="notification in notifications"
                        :key="notification"
                        @click="onClick"
                >
                  <v-list-tile-title
                          v-text="notification"
                  />
                </v-list-tile>
              </v-list>
            </v-card>
          </v-menu>-->

          {{user}}
          <button
                  v-ripple
                  class="toolbar-items"
                  @click="logout"
          >
            <v-icon color="tertiary">mdi-logout</v-icon>
          </button>
        </v-flex>
      </v-toolbar-items>
    </v-toolbar>

  </div>


</template>

<script>

import {
  mapMutations,mapState, mapActions
} from 'vuex'

export default {
  data: () => ({
    notifications: [
      'Mike, John responded to your email',
      'You have 5 new tasks',
      'You\'re now a friend with Andrew',
      'Another Notification',
      'Another One'
    ],
    title: null,
    responsive: false,
    responsiveInput: false,
    dialog: true,
    username: '',
    password:'',
    loading:false,
    snackbar: false,
    snackbarColor:'success',
    snackbarText: '',
  }),

  watch: {
    '$route' (val) {
      this.title = val.name
    }
  },

  mounted () {
    this.onResponsiveInverted()
    window.addEventListener('resize', this.onResponsiveInverted)
  },
  beforeDestroy () {
    window.removeEventListener('resize', this.onResponsiveInverted)
  },

  methods: {
    ...mapMutations('app', ['setDrawer', 'toggleDrawer']),
    ...mapActions('user', ['login', 'logout']),
    onClickBtn () {
      this.setDrawer(!this.$store.state.app.drawer)
    },
    onClick () {
      //
    },
    signIn(){
      self = this;
      this.loading = true;
      this.login({
        username: this.username,
        password: this.password
      }).then(res => {

        var d = new Date();
        d.setTime(d.getTime() + (31*24*60*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = 'token' + "=" + res.body.token + ";" + expires + ";path=/";
        document.cookie = 'username' + "=" + res.body.username + ";" + expires + ";path=/";

        location.reload();
      }).catch(error =>{
        self.snackbarColor = 'error';
        self.snackbarText = error.data.msg;
        self.snackbar = true;
      }).finally(() => self.loading = false)
    },
    onResponsiveInverted () {
      if (window.innerWidth < 991) {
        this.responsive = true
        this.responsiveInput = false
      } else {
        this.responsive = false
        this.responsiveInput = true
      }
    }
  },
  computed: {
    ...mapState({
      'showLogin': state => state.user.showLogin,
      'user': state => state.user.username,
    }),
  },
}
</script>

<style>
  #core-toolbar a {
    text-decoration: none;
  }


  .need-login .v-overlay--active:before {
    opacity: .96;
    /*box-shadow: 0 3px 5px rgba(0, 0, 0, 0.3);*/
  }

  .need-login .v-dialog{
    box-shadow: none;
    background-color: transparent;
  }

  .need-login .theme--light.v-card{
    background-color: transparent;
  }



</style>
