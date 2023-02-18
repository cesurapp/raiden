<template>
  <q-page class="row items-center justify-evenly">
    <q-btn label="asdass" @click="test"></q-btn>

    <div>{{ $authStore.user.id }}</div>
    <div>{{ $authStore.user.first_name }}</div>
    <div>{{ $authStore.user.last_name }}</div>

    <q-btn rounded size="md" color="primary" icon="add" v-if="$authStore.hasPermission($permission.AdminAccount.LIST)"></q-btn>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import { Permission } from 'src/api/Enum/Permission';

export default defineComponent({
  name: 'AdminDashboard',
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t('Dashboard'),
      };
    }),
  ],
  methods: {
    test() {
      // Super
      this.$authStore.switchUser('asd@sadsa.com');
    },
  },
});
</script>
