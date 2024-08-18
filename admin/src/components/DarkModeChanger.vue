<template>
  <q-btn
    round
    flat
    :ripple="false"
    v-bind="$attrs"
    :color="!this.$q.dark.isActive ? (onlyWhite ? 'white' : 'dark') : 'white'"
    @click="toggle"
    :icon="getIcon"
    v-if="!listItem"
  >
    <q-tooltip>{{ $t(getText) }}</q-tooltip>
  </q-btn>
  <q-item v-else clickable @click="toggle">
    <q-item-section
      ><q-item-label>{{ $t(getText) }}</q-item-label></q-item-section
    >
    <q-item-section avatar>
      <q-toggle dense color="primary" @click="toggle" :model-value="isDarkMode" />
    </q-item-section>
  </q-item>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiWeatherNight, mdiWeatherSunny, mdiThemeLightDark } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'DarkModeChanger',
  setup: () => ({
    mdiWeatherNight,
    mdiWeatherSunny,
    mdiThemeLightDark,
  }),
  props: {
    onlyWhite: {
      type: Boolean,
      default: null,
    },
    listItem: {
      type: Boolean,
      default: null,
    },
  },
  computed: {
    isDarkMode() {
      return this.$q.dark.mode;
    },
    getIcon() {
      switch (this.isDarkMode) {
        case 'auto':
          return mdiThemeLightDark;
        case true:
          return mdiWeatherNight;
        default:
          return mdiWeatherSunny;
      }
    },
    getText() {
      switch (this.isDarkMode) {
        case 'auto':
          return 'Dark Mode - Auto';
        case true:
          return 'Dark Mode';
        default:
          return 'Light Mode';
      }
    },
  },
  methods: {
    toggle() {
      switch (this.$q.dark.mode) {
        case 'auto':
          this.$q.dark.set(true);
          break;
        case true:
          this.$q.dark.set(false);
          break;
        case false:
          this.$q.dark.set('auto');
          break;
      }
    },
  },
  watch: {
    isDarkMode() {
      this.$q.localStorage.set('dark_mode', this.$q.dark.mode);
    },
  },
});
</script>
