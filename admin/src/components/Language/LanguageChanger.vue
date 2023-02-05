<template>
  <slot :options="localeOptions">
    <q-select :dark="onlyWhite" v-model="$i18n.locale" :options="localeOptions" borderless emit-value map-options dense>
      <template v-slot:prepend><q-icon name="language" /></template>

      <template v-slot:option="scope">
        <q-item v-bind="scope.itemProps" dense>
          <q-item-section avatar>
            <q-icon :name="scope.opt.icon" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ scope.opt.label }}</q-item-label>
            <q-item-label>{{ scope.opt.description }}</q-item-label>
          </q-item-section>
        </q-item>
      </template>
    </q-select>
  </slot>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'LanguageChanger',
  props: {
    onlyWhite: {
      type: Boolean,
      default: null,
    },
  },
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
