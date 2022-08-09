<template>
  <q-layout id="layout" view="lHr lpR fFf">
    <!--Header-->
    <q-header elevated>
      <q-toolbar>
        <q-btn flat dense round icon="menu" size="md" @click="leftDrawerOpen = !leftDrawerOpen"/>
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
              <q-item><q-item-section><div>Hi, <strong>{{ authStore.user.first_name }} {{ authStore.user.last_name }}</strong></div></q-item-section></q-item>
              <q-separator />
              <q-item clickable><q-item-section>Change Password</q-item-section></q-item>
              <q-item clickable><q-item-section>Edit Profile</q-item-section></q-item>
              <q-separator />
              <q-item clickable @click="authStore.logout()"><q-item-section>Sign out</q-item-section></q-item>
            </q-list>
          </q-menu>
        </q-btn>
      </q-toolbar>
    </q-header>

    <!--Drawer-->
    <q-drawer v-model="leftDrawerOpen" show-if-above bordered>
      <q-list>
        <q-item-label header>Essential Links</q-item-label>
      </q-list>
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
import {useAuthStore} from 'stores/AuthStore';
import Notifications from "components/Notifications.vue";
import LanguageChanger from "components/LanguageChanger.vue";
import DarkModeChanger from "components/DarkModeChanger.vue";
import {createMetaMixin} from "quasar";

export default defineComponent({
  name: 'AdminLayout',
  components: {LanguageChanger, DarkModeChanger, Notifications},
  mixins: [
    createMetaMixin({
      title: 'Raiden Admin',
      titleTemplate: title => `${title} - ` + process.env.APP_TITLE,
    })
  ],
  setup() {
    const authStore = useAuthStore();
    return {
      authStore
    }
  },
  data() {
    return {
      leftDrawerOpen: false
    }
  }
})
</script>
