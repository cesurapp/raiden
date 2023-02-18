<template>
  <q-btn-dropdown
    rounded
    dense
    size="md"
    :flat="!$authStore.isSwitchedUser()"
    :outline="$authStore.isSwitchedUser()"
    class="q-ml-sm disable-icon"
    icon="account_circle"
    :class="{ 'text-amber-7': $authStore.isSwitchedUser() }"
    :menu-offset="[0, 14]"
  >
    <q-list style="min-width: 140px">
      <q-item clickable v-close-popup to="/account/profile">
        <q-item-section side><q-icon name="person" /></q-item-section>
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
        <q-item-section side><q-icon color="amber-7" name="switch_account" /></q-item-section>
        <q-item-section
          ><q-item-label>{{ $t('Return Admin') }}</q-item-label></q-item-section
        >
      </q-item>
      <q-item clickable v-close-popup class="text-red-5" @click="$authStore.logout()">
        <q-item-section side><q-icon color="red-5" name="logout" /></q-item-section>
        <q-item-section
          ><q-item-label>{{ $t('Sign out') }}</q-item-label></q-item-section
        >
      </q-item>
    </q-list>
  </q-btn-dropdown>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
export default defineComponent({
  name: 'AdminProfileDropdown',
});
</script>
