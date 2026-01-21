<template>
  <q-layout class="admin" view="lHh LpR lFr">
    <!--Drawers-->
    <Navigation :navs="navs" :title="$appStore.title">
      <q-btn-group flat spread class="nav-actions">
        <q-btn
          @click="($refs.notification as any).toggle(); $appStore.closeNav();"
          size="12px"
          class="q-px-sm"
          :icon="mdiBell"
        >
          <q-badge v-if="unreadCount > 0" color="red" rounded floating></q-badge>
        </q-btn>
      </q-btn-group>
    </Navigation>

    <Notifications v-model:unreadcount="unreadCount" ref="notification"></Notifications>

    <!--Page Container-->
    <q-page-container>
      <router-view v-slot="{ Component, route }">
        <Transition appear mode="out-in" enter-active-class="animated fadeIn" leave-active-class="animated fadeOut">
          <component :is="Component" :key="route.path"></component>
        </Transition>
      </router-view>
    </q-page-container>
  </q-layout>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import Notifications from 'components/Notification/Notification.vue';
import Navigation from 'components/Layout/Navigation.vue';
import {
  mdiBell,
  mdiMagnify,
  mdiAccountMultiple,
  mdiCalendarClock,
  mdiFirebase,
  mdiTabletCellphone,
  mdiViewDashboard,
} from '@quasar/extras/mdi-v7';
import { Permission } from '@api/enum/Permission';

export default defineComponent({
  name: 'AdminLayout',
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t(String(this.$route.meta.breadcrumb)),
        titleTemplate: (title) => `${title} - ` + this.$appStore.title,
      };
    }),
  ],
  components: { Notifications, Navigation },
  setup: () => ({ mdiBell, mdiMagnify }),
  mounted() {
    this.$appStore.panel = 'admin';
  },
  data: () => ({
    unreadCount: 0,
    navs: [
      { icon: mdiViewDashboard, text: 'Dashboard', to: '/admin' },
      {
        header: 'Account Management',
        items: [
          {
            icon: mdiAccountMultiple,
            text: 'Accounts',
            to: '/admin/account',
            permission: [Permission.AdminAccount.LIST],
          },
          /*{
            icon: mdiAccountMultiple,
            text: 'Restoranlar',
            to: '/admin/dddd',
            permission: [Permission.AdminAccount.LIST],
          },*/
        ],
      },
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
                to: '/admin/firebase/devices',
                permission: [Permission.AdminDevice.LIST],
              },
              {
                icon: mdiCalendarClock,
                text: 'Scheduled Notifications',
                to: '/admin/firebase/scheduler',
                permission: [Permission.AdminScheduler.LIST],
              },
            ],
          },
        ],
      },
    ],
  }),
});
</script>

<style lang="scss">
body {
  background: $body-color;
}

.breadcrumbs {
  font-size: $button-font-size + 2;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.8);

  a {
    color: rgba(255, 255, 255, 0.8);
    transition: 0.2s all;

    &:hover {
      color: #fff;
    }
  }

  .q-breadcrumbs--last a {
    color: #fff;
  }

  @media (max-width: $breakpoint-xs-max) {
    & > div > div:not(.q-breadcrumbs--last) {
      display: none;
    }
  }
}
</style>
