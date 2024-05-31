<template>
  <q-item clickable class="custom-dense-q-item">
    <q-item-section>{{ $t(curentLocale) }}</q-item-section>
    <q-item-section side><q-icon :name="localeFlag" /></q-item-section>
    <q-popup-proxy :breakpoint="5000" class="popup-dropdown">
      <q-list style="min-width: 140px" v-close-popup>
        <q-item v-for="locale in localeOptions" :key="locale" @click="$i18n.locale = locale.value" clickable>
          <q-item-section side><q-icon :name="locale.icon" /></q-item-section>
          <q-item-section
            ><q-item-label>{{ locale.label }}</q-item-label></q-item-section
          >
        </q-item>
      </q-list>
    </q-popup-proxy>
  </q-item>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiWeb } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'LanguageChanger',
  setup: () => ({ mdiWeb }),
  computed: {
    curentLocale() {
      return this.$i18n.locale as string;
    },
    localeOptions() {
      let locales: Record<any, string>[] = [];
      this.$i18n.availableLocales.forEach((locale) => {
        let country = locale.split('-')[1].toLowerCase();

        locales.push({
          value: locale,
          label: this.$t(locale),
          icon: `img:/images/flags/${String(country)}.svg`,
        });
      });
      return locales;
    },
    localeFlag() {
      return this.localeOptions.find((locales) => {
        return locales.value === this.curentLocale;
      }).icon;
    },
  },
  watch: {
    curentLocale(val) {
      localStorage.setItem('locale', val);
    },
  },
});
</script>
