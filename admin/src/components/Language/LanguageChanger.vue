<template>
  <q-btn-dropdown
    flat
    rounded
    class="disable-icon"
    icon="language"
    :menu-offset="[0, 8]"
    no-caps
    :label="$t(curentLocale)"
  >
    <q-list style="min-width: 140px" v-close-popup>
      <q-item v-for="locale in localeOptions" :key="locale" @click="$i18n.locale = locale.value" clickable>
        <q-item-section side><q-icon :name="locale.icon" /></q-item-section>
        <q-item-section
          ><q-item-label>{{ locale.label }}</q-item-label></q-item-section
        >
      </q-item>
    </q-list>
  </q-btn-dropdown>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'LanguageChanger',
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
  },
  watch: {
    curentLocale(val) {
      localStorage.setItem('locale', val);
    },
  },
});
</script>
