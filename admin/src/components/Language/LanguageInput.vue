<template>
  <q-select
    bottom-slots
    outlined
    clearable
    v-model="locale"
    :options="localeOptions"
    :label="$t('Language')"
    emit-value
    map-options
  >
    <template v-slot:prepend><q-icon :name="mdiWeb" /></template>
    <template v-slot:option="scope">
      <q-item v-bind="scope.itemProps">
        <q-item-section avatar><q-icon :name="scope.opt.icon" /></q-item-section>
        <q-item-section
          ><q-item-label>{{ scope.opt.label }}</q-item-label></q-item-section
        >
      </q-item>
    </template>
  </q-select>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiWeb } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'LanguageInput',
  setup: () => ({ mdiWeb }),
  data: () => ({
    locale: null,
  }),
  computed: {
    localeOptions() {
      let locales: Record<any, string>[] = [];
      this.$i18n.availableLocales.forEach((locale) => {
        let country = locale.split('-')[1].toLowerCase();

        locales.push({
          value: locale.split('-')[0].toLowerCase(),
          label: this.$t(locale),
          icon: `img:/images/flags/${String(country).toLowerCase()}.svg`,
        });
      });
      return locales;
    },
  },
});
</script>
