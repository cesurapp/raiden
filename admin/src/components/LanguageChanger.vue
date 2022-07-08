<template>
  <slot :options="localeOptions">
    <q-select v-model="$i18n.locale" :options="localeOptions" standout="bg-blue-grey text-white" emit-value map-options dense>
      <template v-slot:prepend><q-icon name="language"/></template>
    </q-select>
  </slot>
</template>

<script lang="ts">
import {defineComponent} from 'vue';

export default defineComponent({
  name: 'LanguageChanger',
  computed:{
    curentLocale() {
      return this.$i18n.locale as string;
    },
    localeOptions() {
      let locales: Record<any, string>[] = []
      this.$i18n.availableLocales.forEach((locale) => {
        locales.push({value: locale, label: this.$t(locale)})
      });
      return locales;
    }
  },
  watch: {
    curentLocale(val) {
      localStorage.setItem('locale', val);
      this.$i18n.locale = val;
    }
  }
})
</script>
