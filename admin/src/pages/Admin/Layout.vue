<template>
  <q-layout class="admin" view="lHh LpR lFr">
    <!--Header-->
    <q-header elevated>
      <q-toolbar class="q-pr-md-sm q-pr-lg-md q-pl-md-sm q-pl-lg-md">
        <div id="head-toolbar"></div>

        <!--BreadCrumbs & Title-->
        <q-toolbar-title class="q-pl-none">
          <q-breadcrumbs class="breadcrumbs">
            <q-breadcrumbs-el
              v-for="(route, index) in getBreadcrumbs"
              :key="index"
              :label="$t(route.meta.breadcrumb)"
              :to="route.path"
            />
          </q-breadcrumbs>
        </q-toolbar-title>

        <!--Notification Button-->
        <q-btn @click="$refs.notification.toggle()" dense flat round icon="notifications" size="md">
          <q-badge v-if="unreadCount > 0" color="red" rounded floating></q-badge>
        </q-btn>

        <!-- Profile Menu-->
        <Profile></Profile>
      </q-toolbar>
    </q-header>

    <!--Navigation Drawer-->
    <Navigation v-model:activated="menuActived"></Navigation>

    <!--Notification Drawer-->
    <Notifications v-model:unreadcount="unreadCount" ref="notification"></Notifications>

    <!--Page Container-->
    <q-page-container>
      <router-view v-slot="{ Component }">
        <transition appear mode="out-in" enter-active-class="animated fadeIn" leave-active-class="animated fadeOut">
          <component :is="Component"></component>
        </transition>
      </router-view>
    </q-page-container>
  </q-layout>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import Notifications from 'components/Notification/Notification.vue';
import Navigation from './Components/Layout/Navigation.vue';
import Profile from './Components/Layout/ProfileNav.vue';

export default defineComponent({
  name: 'AdminLayout',
  components: { Notifications, Navigation, Profile },
  data: () => ({
    menuActived: true,
    unreadCount: 0,
  }),
  mixins: [
    createMetaMixin(function () {
      return {
        titleTemplate: (title) => `${title} - ` + this.$appStore.title,
      };
    }),
  ],
  computed: {
    getBreadcrumbs() {
      return this.$route.matched.filter((route) => {
        return route.meta?.breadcrumb;
      });
    },
  },
  created() {
    this.$authStore.reloadUser();
  },
});
</script>

<style lang="scss">
body {
  background: $body-color;
}

.breadcrumbs {
  font-size: 16px;
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
