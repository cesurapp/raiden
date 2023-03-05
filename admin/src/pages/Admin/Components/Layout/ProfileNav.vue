<template>
  <q-btn-dropdown
    rounded
    dense
    size="15px"
    :flat="!$authStore.isSwitchedUser()"
    :outline="$authStore.isSwitchedUser()"
    class="q-ml-sm disable-icon"
    :icon="mdiAccount"
    :class="{ 'text-amber-7': $authStore.isSwitchedUser() }"
    :menu-offset="[0, 14]"
  >
    <q-list style="min-width: 140px">
      <q-item clickable v-close-popup to="/account/profile">
        <q-item-section side><q-icon :name="mdiAccount" /></q-item-section>
        <q-item-section
          ><q-item-label>{{ $t('Edit Profile') }}</q-item-label></q-item-section
        >
      </q-item>
      <q-item
        clickable
        v-close-popup
        class="text-amber-7"
        v-show="$authStore.isSwitchedUser()"
        @click="$authStore.switchUserLogout(true)"
      >
        <q-item-section side><q-icon color="amber-7" :name="mdiAccountMultipleOutline" /></q-item-section>
        <q-item-section
          ><q-item-label>{{ $t('Return Admin') }}</q-item-label></q-item-section
        >
      </q-item>
      <q-item clickable v-close-popup class="text-red-5" @click="$authStore.logout()">
        <q-item-section side><q-icon color="red-5" :name="mdiLogout" /></q-item-section>
        <q-item-section
          ><q-item-label>{{ $t('Sign out') }}</q-item-label></q-item-section
        >
      </q-item>
    </q-list>
  </q-btn-dropdown>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiAccount, mdiLogout, mdiAccountMultipleOutline } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'AdminProfileDropdown',
  setup: () => ({ mdiAccount, mdiLogout, mdiAccountMultipleOutline }),
});
</script>
