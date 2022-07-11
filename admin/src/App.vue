<template>
  <router-view/>
</template>

<script lang="ts">
import {defineComponent} from 'vue';

export default defineComponent({
  name: 'App',
  /**
   * Global Init
   */
  created() {
    if (this.$q.localStorage.has('dark_mode')) {
      this.$q.dark.set(this.$q.localStorage.getItem('dark_mode'));
    }
  }
});
</script>
