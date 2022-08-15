<template>
  <q-btn class="q-ml-sm" dense flat round icon="account_circle" size="md">
    <q-tooltip>{{ $t('Account') }}</q-tooltip>
    <q-menu fit anchor="bottom end" self="top end" :offset="[0,12]">
      <q-list>
        <q-item><q-item-section>
          <div><strong>{{ $auth.user.first_name }} {{ $auth.user.last_name }}</strong></div>
        </q-item-section></q-item>
        <q-separator />

        <q-item clickable><q-item-section>{{ $t('Change Password') }}</q-item-section></q-item>
        <q-item clickable><q-item-section>{{ $t('Edit Profile') }}</q-item-section></q-item>
        <q-separator />
        <q-item clickable @click="$auth.logout()"><q-item-section>{{ $t('Sign out') }}</q-item-section></q-item>
      </q-list>
    </q-menu>
  </q-btn>
</template>

<script lang="ts">
import {defineComponent} from "vue";
export default defineComponent({
  name: 'AdminProfileDropdown'
})
</script>
