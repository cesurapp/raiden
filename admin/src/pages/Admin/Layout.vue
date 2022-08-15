<template>
  <q-layout class="admin" view="lHr lpR fFf">
    <!--Header-->
    <q-header elevated>
      <q-toolbar class="q-pl-md-md q-pr-md-sm">
        <div id="head-toolbar"></div>

        <!--BreadCrumbs & Title-->
        <q-toolbar-title class="q-pl-none">
          <q-breadcrumbs class="breadcrumbs">
            <q-breadcrumbs-el v-for="(route, index) in getBreadcrumbs" :key="index" :label="$t(route.meta.breadcrumb)" :to="route.path"/>
          </q-breadcrumbs>
        </q-toolbar-title>

        <!--Right-->
        <Notifications></Notifications>

        <!-- Profile Menu-->
        <Profile></Profile>
      </q-toolbar>
    </q-header>

    <!--Left Navigation-->
    <Navigation ref="nav"></Navigation>

    <!--Container-->
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
import {defineComponent} from 'vue'
import {createMetaMixin} from 'quasar';
import Notifications from 'components/Notification/Notification.vue';
import Navigation from './Components/Navigation.vue';
import Profile from './Components/Profile.vue';

export default defineComponent({
  name: 'AdminLayout',
  components: {Notifications, Navigation, Profile},
  mixins: [
    createMetaMixin(function () {
      return {
        title: 'Raiden Admin',
        titleTemplate: title => `${title} - ` + process.env.APP_TITLE,
      }
    })
  ],
  computed: {
    getBreadcrumbs() {
      return this.$route.matched.filter((route) => {
        return route.meta?.breadcrumb;
      })
    }
  }
})
</script>

<style lang="scss">
.breadcrumbs {
  font-size: 16px;
  font-weight: 500;
  color: rgba(255, 255, 255, .8);

  a {
    color: rgba(255, 255, 255, .8);
    transition: .2s all;

    &:hover {
      color: #FFF;
    }
  }

  .q-breadcrumbs--last a {
    color: #FFF;
  }

  @media (max-width: $breakpoint-xs-max) {
    & > div > div:not(.q-breadcrumbs--last) {
      display: none;
    }
  }
}

body{
  background: #f6f7f9;
}
</style>
