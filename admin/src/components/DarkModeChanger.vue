<template>
  <slot>
    <q-btn :color="!isDarkMode ? 'dark' : 'white'" round :ripple="false" size="md" flat @click="$q.dark.set(!$q.dark.isActive)" :icon="!isDarkMode ? 'dark_mode' : 'mode_night'"/>
  </slot>
</template>

<script lang="ts">
import {defineComponent} from 'vue';

export default defineComponent({
  name: 'DarkModeChanger',
  computed: {
    isDarkMode() {
      return this.$q.dark.isActive;
    },
  },
  watch: {
    isDarkMode(val) {
      this.$q.localStorage.set('dark_mode', val);
    }
  }
})
</script>
