<template>
  <q-select
    ref="lang"
    bottom-slots outlined
    v-model="locale"
    :options="locales"
    @filter="filterFn"
    :label="$t('Language')"
    emit-value map-options
    use-input hideSelected fillInput
    class="emoji-input"
    input-debounce="0"
    transition-duration="0"
    virtual-scroll-slice-ratio-before="12"
    virtual-scroll-slice-ratio-after="12"
    :class="{ default: !locale }"
  >
    <template v-slot:prepend v-if="!all">
      <q-icon v-if="!locale" :name="mdiWeb" />
      <q-icon v-else class="language-emoji" :name="getSelectedIcon" />
    </template>
    <template v-slot:option="scope">
      <q-item v-bind="scope.itemProps">
        <q-item-section avatar v-if="!all"><q-icon class="language-emoji" :name="scope.opt.icon" /></q-item-section>
        <q-item-section><q-item-label>{{ scope.opt.label }}</q-item-label></q-item-section>
      </q-item>
    </template>
    <template #append v-if="$slots.append">
      <slot name="append"></slot>
    </template>
  </q-select>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiWeb, mdiInformation } from '@quasar/extras/mdi-v7';
import { getCountryFlag } from 'components/Localization/LocalizationLoader';
import languages from './data/en.json';
import {mapObject} from "src/helper/AppHelper";

export default defineComponent({
  name: 'LanguageInput',
  setup: () => ({ mdiWeb,mdiInformation }),
  props: {
    all: {
      type: Boolean,
      default: false,
    },
    addAuto: {
      type: Boolean,
      default: false,
    }
  },
  data: () => ({
    locale: null,
    locales: [],
    localesRaw: [],
    searchText: '',
  }),
  mounted() {
    if (this.$attrs.modelValue) {
      this.locale = this.$attrs.modelValue;
    }

    this.loadLocales();
  },
  computed: {
    getSelectedIcon() {
      return this.locales.find((l) => l.value === (this.locale ?? this.$attrs.modelValue))?.icon;
    },
  },
  methods: {
    loadLocales() {
      // Load All
      if (this.all) {
        this.locales = [];
        if (this.addAuto) {
          this.locales.push({label: 'Auto', value: null});
        }
        this.locales.push(...Object.values(mapObject(languages.data, (name,code) => ({
          value: code,
          label: name,
        }))))

        this.localesRaw = this.locales;
        return;
      }

      // Load App Locale
      this.$i18n.availableLocales.forEach((locale) => {
        let country = locale.split('-')[1].toLowerCase();
        this.locales.push({
          value: locale.split('-')[0].toLowerCase(),
          label: this.$t(locale),
          icon: getCountryFlag(country.toUpperCase()) ?? '',
        });
      });
      this.localesRaw = this.locales;
    },
    filterFn(val, update) {
      val = val.toLowerCase();
      update(() => {
        this.locales = this.localesRaw.filter((c) => c.label.toLowerCase().indexOf(val) !== -1);
      });
    },
  }
});
</script>
