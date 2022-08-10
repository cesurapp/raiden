<template>
  <q-layout id="layout" view="lHr lpR fFf">
    <!--Header-->
    <q-header elevated>
      <q-toolbar>
        <q-toolbar-title>Dashboard</q-toolbar-title>

        <!--Right-->
        <DarkModeChanger dense></DarkModeChanger>
        <q-separator class="q-mx-sm" dark vertical inset/>
        <Notifications></Notifications>

        <!-- Profile Menu-->
        <q-btn class="q-ml-sm" dense flat round icon="account_circle" size="md">
          <q-tooltip>Account</q-tooltip>
          <q-menu fit anchor="bottom end" self="top end">
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
    <q-drawer show-if-above bordered :mini="menu" mini-to-overlay class="bg-grey-3">
      <q-toolbar>
        <q-btn flat dense round icon="menu" size="md" @click="menu = !menu"/>
        <q-toolbar-title>Raiden Admin</q-toolbar-title>
      </q-toolbar>

      <Navigation></Navigation>
    </q-drawer>

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
import LanguageChanger from 'components/LanguageChanger.vue';
import DarkModeChanger from 'components/DarkModeChanger.vue';
import Navigation from './Components/Navigation.vue';

export default defineComponent({
  name: 'AdminLayout',
  components: {LanguageChanger, DarkModeChanger, Notifications, Navigation},
  mixins: [
    createMetaMixin({
      title: 'Raiden Admin',
      titleTemplate: title => `${title} - ` + process.env.APP_TITLE,
    })
  ],
  data: () => ({
    menu: false
  })
})
</script>
