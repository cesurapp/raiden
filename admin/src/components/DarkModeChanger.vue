<template>
  <slot>
    <q-item clickable class="custom-dense-q-item" @click="$q.dark.set(!$q.dark.isActive)">
      <q-item-section
        ><q-item-label>{{ $t('Dark Mode') }}</q-item-label></q-item-section
      >
      <q-item-section avatar
        ><q-toggle dense color="primary" @click="$q.dark.set(!$q.dark.isActive)" :model-value="isDarkMode"
      /></q-item-section>
    </q-item>
  </slot>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiWeatherNight, mdiWeatherSunny } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'DarkModeChanger',
  setup: () => ({
    mdiWeatherNight,
    mdiWeatherSunny,
  }),
  props: {
    onlyWhite: {
      type: Boolean,
      default: null,
    },
  },
  computed: {
    isDarkMode() {
      return this.$q.dark.isActive;
    },
  },
  watch: {
    isDarkMode(val) {
      this.$q.localStorage.set('dark_mode', val);
    },
  },
});
</script>
