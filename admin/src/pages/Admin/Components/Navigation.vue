<template>
  <q-drawer show-if-above class="main-nav text-white" v-model="menu" :width="280">
    <!--Logo-->
    <q-toolbar class="logo">
      <q-toolbar-title>Raiden Admin</q-toolbar-title>
    </q-toolbar>

    <!--Menu List-->
    <q-list class="menus">
      <div class="item" v-for="(nav, index) in getNavs" :key="index">
        <!--Header-->
        <q-item-label v-if="nav.header" header>{{ $t(nav.header) }}</q-item-label>

        <!--Single-->
        <q-item
          exact
          v-if="!nav.child"
          :to="nav.to"
          clickable
          v-ripple
          dense
          class="menu-link"
          active-class="active-link"
        >
          <q-item-section avatar><q-icon :name="nav.icon" /></q-item-section>
          <q-item-section class="text-weight-medium">{{ $t(nav.text) }}</q-item-section>
        </q-item>

        <!--Multiple-->
        <q-expansion-item
          :icon="nav.icon"
          :label="nav.text"
          v-model="nav.active"
          v-else
          group="navigation"
          dense
          active-class="active-item-grup"
        >
          <q-list>
            <q-item
              exact
              v-for="(childNav, childIndex) in nav.child"
              :key="childIndex"
              :to="childNav.to"
              clickable
              v-ripple
              dense
              active-class="active-link"
              class="menu-link"
            >
              <q-item-section avatar><q-icon :name="childNav.icon" /></q-item-section>
              <q-item-section class="text-weight-medium">{{ $t(childNav.text) }}</q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>
      </div>
    </q-list>

    <!--Footer-->
    <div class="footer flex items-center justify-evenly">
      <LanguageChanger :only-white="true"></LanguageChanger>
      <q-separator class="q-mx-sm" dark vertical inset />
      <DarkModeChanger dense :only-white="true"></DarkModeChanger>
      <q-separator class="q-mx-sm" dark vertical inset />
      <q-btn flat dense round icon="arrow_back_ios_new" size="md" @click="toggle" />
    </div>

    <teleport to="#head-toolbar" v-if="!menu && mounted">
      <q-btn flat dense round icon="menu" size="md" class="q-mr-sm" @click="toggle" />
    </teleport>
  </q-drawer>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import LanguageChanger from 'components/Language/LanguageChanger.vue';
import DarkModeChanger from 'components/DarkModeChanger.vue';

export default defineComponent({
  name: 'AdminNavigation',
  components: { LanguageChanger, DarkModeChanger },
  data: () => ({
    mounted: false,
    menu: false,
    navs: [
      { icon: 'dashboard', text: 'Dashboard', to: '/' },

      // Account Management
      {
        icon: 'group',
        text: 'Accounts',
        to: '/account',
        header: 'Account Management',
        roles: ['ROLE_ACCOUNT_LIST'],
      },
      {
        icon: 'workspaces',
        text: 'Organization',
        to: '/organization',
        roles: [],
      },

      /*{icon: 'logout', text: 'Logout', header:'Header Text', to: {name: 'auth.logout'}, roles: ['ROLE_USER'], child: []},*/
    ],
  }),
  computed: {
    getNavs() {
      return this.navs
        .filter((nav) => {
          if (nav.hasOwnProperty('roles') && !this.$auth.hasPermission(nav.roles)) {
            return false;
          }
          if (nav.hasOwnProperty('child')) {
            nav.child = nav.child.filter(
              (navChild) => !(navChild.hasOwnProperty('roles') && !this.$auth.hasPermission(navChild.roles))
            );
            if (nav.child.length === 0) {
              return false;
            }
          }

          return true;
        })
        .map((nav) => {
          if (nav.hasOwnProperty('child')) {
            if (
              nav.child.some((child) => {
                return typeof child.to === 'string'
                  ? child.to === this.$route.path
                  : child.to.path === this.$route.path;
              })
            ) {
              nav.active = true;
            }
          }

          return nav;
        });
    },
  },
  mounted() {
    this.mounted = true;
  },
  methods: {
    toggle() {
      this.menu = !this.menu;
    },
  },
});
</script>

<style lang="scss">
.main-nav {
  background: $dark;
  overflow: hidden !important;
  display: flex;
  flex-direction: column;
  box-shadow: 0 0 5px 2px rgb(0 0 0 / 15%);

  .menus {
    flex: 1;
    overflow: auto;
    padding: 8px 12px;
  }

  .item {
    margin: 1px 0;
  }

  .logo {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 0 30px;
  }

  .q-item {
    padding-top: 8px;
    padding-bottom: 8px;
  }
  .q-item.q-router-link--active,
  .q-item {
    color: white;
    border-radius: $button-border-radius;
  }

  .q-expansion-item--expanded {
    background: rgba(255, 255, 255, 0.15);
    border-radius: $button-border-radius;
  }

  .q-item__section--avatar {
    min-width: 40px;
  }

  .q-item__section--side > .q-icon {
    font-size: 22px;
  }

  .active-link {
    color: white;
    background: $primary;
  }

  .footer {
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    & > * {
      transform: scale(0.8);
    }
  }
}
</style>
