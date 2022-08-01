<template>
  <q-page class="row items-center justify-evenly">
    <q-btn :to="{ path: '/page2' }" label="Go Page 2"></q-btn>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'AdminDashboard',
});
</script>
