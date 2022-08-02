<template>
  <q-layout view="lHh Lpr lFf">
    <!--Header-->
    <q-header elevated>
      <q-toolbar>
        <q-btn flat dense round icon="menu" @click="leftDrawerOpen = !leftDrawerOpen"/>
        <q-toolbar-title>Quasar App</q-toolbar-title>

        <!--Right-->
        <div class="q-pl-sm q-gutter-sm row items-center no-wrap">
          <!-- Notification Menu-->
          <q-btn dense flat round icon="notifications">
            <q-tooltip>Notifications</q-tooltip>
            <q-badge color="red" text-color="white" floating>2</q-badge>
          </q-btn>

          <!-- Profile Menu-->
          <q-btn dense flat round icon="account_circle">
            <q-tooltip>Account</q-tooltip>
            <q-menu fit anchor="bottom end" self="top end">
              <q-list>
                <q-item><q-item-section><div>Hi, <strong>Ramazaas dsad asdasn</strong></div></q-item-section></q-item>
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
    <q-page-container><router-view /></q-page-container>
  </q-layout>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import {useAuthStore} from "stores/AuthStore";

export default defineComponent({
  name: 'AdminLayout',
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
