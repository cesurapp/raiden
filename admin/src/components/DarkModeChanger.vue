<template>
  <slot>
    <q-btn
      v-bind="$attrs"
      :color="!isDarkMode ? (onlyWhite ? 'white' : 'dark') : 'white'"
      round
      :ripple="false"
      flat
      @click="$q.dark.set(!$q.dark.isActive)"
      :icon="!isDarkMode ? mdiWeatherNight : mdiWeatherSunny"
    >
      <q-tooltip>{{ $t('Dark Mode') }}</q-tooltip>
    </q-btn>
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
