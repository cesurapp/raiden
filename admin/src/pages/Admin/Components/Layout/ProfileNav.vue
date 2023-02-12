<template>
  <q-btn
    class="q-ml-sm"
    :class="{ 'text-amber-7': $authStore.isSwitchedUser() }"
    dense
    :flat="!$authStore.isSwitchedUser()"
    :outline="$authStore.isSwitchedUser()"
    round
    icon="account_circle"
    size="md"
  >
    <q-menu fit anchor="bottom end" self="top end" :offset="[0, 12]">
      <q-list>
        <q-item clickable to="/account/profile">
          <q-icon name="person" /><span>{{ $t('Edit Profile') }}</span>
        </q-item>
        <q-item
          class="text-amber-7"
          clickable
          v-if="$authStore.isSwitchedUser()"
          @click="$authStore.switchUserLogout(true)"
        >
          <q-icon name="switch_account" /><span>{{ $t('Return Admin') }}</span>
        </q-item>
        <q-item clickable @click="$authStore.logout()">
          <q-icon name="logout" /><span>{{ $t('Sign out') }}</span>
        </q-item>
      </q-list>
    </q-menu>
  </q-btn>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
export default defineComponent({
  name: 'AdminProfileDropdown',
});
</script>
