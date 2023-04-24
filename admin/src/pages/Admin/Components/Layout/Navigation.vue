<template>
  <q-drawer show-if-above class="main-nav text-white" v-model="menu" :width="280">
    <!--Logo-->
    <q-toolbar class="logo">
      <q-toolbar-title>{{ this.$appStore.title }}</q-toolbar-title>
    </q-toolbar>

    <!--Menu List-->
    <q-list class="menus">
      <div class="item" v-for="(nav, index) in getNavs" :key="index">
        <!--With Header-->
        <template v-if="nav.header && nav.hasOwnProperty('items')">
          <q-item-label v-if="nav.header" header>{{ $t(nav.header) }}</q-item-label>
          <template v-for="(subNav, index) in nav.items" :key="index">
            <!--Single-->
            <q-item
              exact
              clickable
              v-ripple
              dense
              class="menu-link"
              active-class="active-link"
              v-if="!subNav.child"
              :to="subNav.to"
            >
              <q-item-section avatar v-if="subNav.icon"><q-icon :name="subNav.icon" /></q-item-section>
              <q-item-section class="text-weight-medium">{{ $t(subNav.text) }}</q-item-section>
            </q-item>

            <!--Dropdown with Childed-->
            <q-expansion-item
              v-else
              dense
              group="navigation"
              active-class="active-item-grup"
              :icon="subNav.icon"
              :label="$t(subNav.text)"
              v-model="subNav.active"
            >
              <q-list>
                <q-item
                  exact
                  clickable
                  v-ripple
                  dense
                  class="menu-link"
                  active-class="active-link"
                  v-for="(childNav, childIndex) in subNav.child"
                  :key="childIndex"
                  :to="childNav.to"
                >
                  <q-item-section avatar v-if="childNav.icon"><q-icon :name="childNav.icon" /></q-item-section>
                  <q-item-section class="text-weight-medium">{{ $t(childNav.text) }}</q-item-section>
                </q-item>
              </q-list>
            </q-expansion-item>
          </template>
        </template>

        <!-- Headerless-->
        <template v-else-if="!nav.header">
          <!--Single-->
          <q-item
            exact
            clickable
            v-ripple
            dense
            class="menu-link"
            active-class="active-link"
            v-if="!nav.child"
            :to="nav.to"
          >
            <q-item-section avatar v-if="nav.icon"><q-icon :name="nav.icon" /></q-item-section>
            <q-item-section class="text-weight-medium">{{ $t(nav.text) }}</q-item-section>
          </q-item>

          <!--Dropdown with Childed-->
          <q-expansion-item
            v-else
            dense
            group="navigation"
            active-class="active-item-grup"
            :icon="nav.icon"
            :label="$t(subNav.text)"
            v-model="nav.active"
          >
            <q-list>
              <q-item
                exact
                clickable
                v-ripple
                dense
                class="menu-link"
                active-class="active-link"
                v-for="(childNav, childIndex) in nav.child"
                :key="childIndex"
                :to="childNav.to"
              >
                <q-item-section avatar v-if="childNav.icon"><q-icon :name="childNav.icon" /></q-item-section>
                <q-item-section class="text-weight-medium">{{ $t(childNav.text) }}</q-item-section>
              </q-item>
            </q-list>
          </q-expansion-item>
        </template>
      </div>
    </q-list>

    <!--Footer-->
    <div class="footer flex items-center justify-evenly">
      <LanguageChanger size="sm"></LanguageChanger>
      <DarkModeChanger size="sm" :only-white="true"></DarkModeChanger>
    </div>

    <!--Toogle Button-->
    <teleport to="#head-toolbar" v-if="mounted">
      <q-btn
        flat
        dense
        round
        :icon="mdiMenu"
        size="md"
        class="q-mr-sm"
        color="blue-2"
        @click="this.menu = !this.menu"
      />
    </teleport>
  </q-drawer>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import LanguageChanger from 'components/Language/LanguageChanger.vue';
import DarkModeChanger from 'components/DarkModeChanger.vue';
import { Permission } from 'src/api/Enum/Permission';
import {
  mdiMenu,
  mdiViewDashboard,
  mdiAccountMultiple,
  mdiFirebase,
  mdiCalendarClock,
  mdiTabletCellphone,
} from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'AdminNavigation',
  setup: () => ({ mdiMenu }),
  components: { LanguageChanger, DarkModeChanger },
  data: () => ({
    mounted: false,
    menu: false,
    navs: [
      { icon: mdiViewDashboard, text: 'Dashboard', to: '/' },

      // Account Management
      {
        header: 'Account Management',
        items: [
          {
            icon: mdiAccountMultiple,
            text: 'Accounts',
            to: '/account',
            permission: [Permission.AdminAccount.LIST],
          },
        ],
      },

      // Tools
      {
        header: 'Tools',
        items: [
          {
            icon: mdiFirebase,
            text: 'Firebase',
            child: [
              {
                icon: mdiTabletCellphone,
                text: 'Devices',
                to: '/firebase/devices',
                permission: [Permission.AdminDevice.LIST],
              },
              {
                icon: mdiCalendarClock,
                text: 'Scheduled Notifications',
                to: '/firebase/scheduler',
                permission: [Permission.AdminScheduler.LIST],
              },
            ],
          },
        ],
      },

      /* With Header
      {
        header: 'Tools',
        items: [
          {
            icon: mdiFirebase,
            text: 'Firebase Devices',
            to: '/fcm/devices',
            permission: [Permission.AdminDevice.LIST],
          }
        ],
      }, */

      /* Headerless
      {
        icon: mdiFirebase,
        text: 'Firebase Devices',
        to: '/fcm/devices',
        permission: [Permission.AdminDevice.LIST],
        child: []
      }, */
    ],
  }),
  computed: {
    getNavs() {
      return this.navs
        .filter((nav) => {
          // With Header
          if (nav.hasOwnProperty('items')) {
            nav.items = nav.items.filter((c) => {
              if (c.hasOwnProperty('child')) {
                c.child = c.child.filter(
                  (cd) => !(cd.hasOwnProperty('permission') && !this.$authStore.hasPermission(cd.permission))
                );
                return c.child.length !== 0;
              }
              return !(c.hasOwnProperty('permission') && !this.$authStore.hasPermission(c.permission));
            });

            return nav.items.length !== 0;
          }

          // Headerless
          if (nav.hasOwnProperty('child')) {
            nav.child = nav.child.filter(
              (c) => !(c.hasOwnProperty('permission') && !this.$authStore.hasPermission(c.permission))
            );

            return nav.child.length !== 0;
          }

          return !(nav.hasOwnProperty('permission') && !this.$authStore.hasPermission(nav.permission));
        })
        .map((nav) => {
          // With Header
          if (nav.hasOwnProperty('items')) {
            nav.items.map((n) => {
              if (
                n.hasOwnProperty('child') &&
                n.child.some((child) =>
                  typeof child.to === 'string' ? child.to === this.$route.path : child.to.path === this.$route.path
                )
              ) {
                n.active = true;
              }

              return n;
            });
          }

          // Headerless
          if (
            nav.hasOwnProperty('child') &&
            nav.child.some((child) =>
              typeof child.to === 'string' ? child.to === this.$route.path : child.to.path === this.$route.path
            )
          ) {
            nav.active = true;
          }

          return nav;
        });
    },
  },
  mounted() {
    this.mounted = true;
  },
  watch: {
    menu(v) {
      this.$emit('update:activated', v);
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
  box-shadow: 0 0 5px 2px rgb(0 0 0 / 10%);

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
    padding: 0 24px;
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

  .q-expansion-item--expanded > div > .q-item {
    border-radius: $button-border-radius $button-border-radius 0 0;
  }

  .q-expansion-item__content {
    .q-item {
      border-radius: 0;

      &:last-of-type {
        border-radius: 0 0 $button-border-radius $button-border-radius;
      }
    }
  }

  .q-expansion-item--expanded {
    background: rgba(255, 255, 255, 0.15);
    border-radius: $button-border-radius;
  }

  .q-item__section--avatar {
    min-width: 40px;
  }

  .q-item__section--side > .q-icon {
    font-size: $button-font-size + 8;
  }

  .active-link {
    color: white;
    background: $primary;
  }

  .q-item__label--header {
    color: rgba(255, 255, 255, 0.64);
  }

  .footer {
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    min-height: 44px;
    & > * {
      transform: scale(1.1);
    }
  }
}
</style>
