<template>
  <router-view />
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'App',

  /**
   * Global Init
   */
  created() {
    this.$authStore.reloadUser();
    this.loadUserDefinedLocale();
    this.loadDarkMode();
    document.body.classList.add(this.$q.platform.is.name);
  },
  methods: {
    loadUserDefinedLocale() {
      const ul = localStorage.getItem('user_locale');
      if (ul) {
        const lang = this.$i18n.availableLocales.find((l) => l.indexOf(ul) !== -1);
        if (lang) {
          localStorage.removeItem('user_locale');
          localStorage.setItem('locale', lang);
          this.$i18n.locale = lang;
        }
      }

      document.documentElement.lang = this.$i18n.locale;
    },
    loadDarkMode() {
      if (this.$q.localStorage.has('dark_mode')) {
        this.$q.dark.set(this.$q.localStorage.getItem('dark_mode'));
      }
    },
  },
});
</script>
