<template>
  <q-layout view="lHr lpR fFf">
    <!--Header-->
    <q-header elevated>
      <q-toolbar class="q-px-md">
        <q-btn flat dense round icon="menu" size="md" class="mobile-only q-mr-sm" @click="$refs.nav.toggle()"/>
        <q-toolbar-title class="q-pl-none">
          <q-breadcrumbs class="breadcrumbs">
            <q-breadcrumbs-el v-for="(route, index) in getBreadcrumbs" :key="index"
                              :label="$t(route.meta.breadcrumb)"
                              :to="route.path"
                              class="text-white"/>
          </q-breadcrumbs>
        </q-toolbar-title>

        <!--Right-->
        <Notifications></Notifications>

        <!-- Profile Menu-->
        <q-btn class="q-ml-sm" dense flat round icon="account_circle" size="md">
          <q-tooltip>Account</q-tooltip>
          <q-menu fit anchor="bottom end" self="top end" :offset="[0,12]">
            <q-list>
              <q-item><q-item-section><div>Hi, <strong>{{ $auth.user.first_name }} {{ $auth.user.last_name }}</strong></div></q-item-section></q-item>
              <q-separator />
              <q-item clickable><q-item-section>Change Password</q-item-section></q-item>
              <q-item clickable><q-item-section>Edit Profile</q-item-section></q-item>
              <q-separator />
              <q-item clickable @click="$auth.logout()"><q-item-section>Sign out</q-item-section></q-item>
            </q-list>
          </q-menu>
        </q-btn>
      </q-toolbar>
    </q-header>

    <!--Left Menu-->
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

export default defineComponent({
  name: 'AdminLayout',
  components: {Notifications, Navigation},
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
}
</style>
