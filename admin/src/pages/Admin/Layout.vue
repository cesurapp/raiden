<template>
  <q-layout id="layout" view="lHr lpR fFf">
    <!--Header-->
    <q-header elevated>
      <q-toolbar>
        <q-btn flat dense round icon="menu" size="md" @click="leftDrawerOpen = !leftDrawerOpen"/>
        <q-toolbar-title>Quasar App</q-toolbar-title>

        <!--Right-->
        <div class="q-pl-sm q-gutter-sm row items-center no-wrap">
          <!-- Notifications-->
          <Notifications ref="notifications"></Notifications>

          <!-- Profile Menu-->
          <q-btn dense flat round icon="account_circle" size="md">
            <q-tooltip>Account</q-tooltip>
            <q-menu fit anchor="bottom end" self="top end">
              <q-list>
                <q-item><q-item-section><div>Hi, <strong>{{ authStore.user.first_name }} {{ authStore.user.last_name }}</strong></div></q-item-section></q-item>
                <q-separator />
                <q-item clickable @click="authStore.logout()"><q-item-section>Sign out</q-item-section></q-item>
              </q-list>
            </q-menu>
          </q-btn>
        </div>
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

export default defineComponent({
  name: 'AdminLayout',
  components: {Notifications},
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
