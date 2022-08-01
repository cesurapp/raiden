<template>
  <q-page class="row items-center justify-evenly">
    <q-btn :to="{ path: '/' }" label="Go Admin"></q-btn>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'AdminDashboard2',
});
</script>
