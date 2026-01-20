<template>
  <q-btn v-if="!listItem" flat :icon="mdiWeb" :menu-offset="[0, 8]" :label="$t(curentLocale)" no-caps :loading="loading">
    <q-popup-proxy :breakpoint="600" class="popup-dropdown">
      <q-list style="min-width: 140px" v-close-popup>
        <q-item
          v-for="locale in localeOptions"
          :key="locale.value"
          @click="changeLanguage(locale.value)"
          clickable
          :disable="loading"
        >
          <q-item-section side><q-icon class="language-emoji" :name="locale.icon" /></q-item-section>
          <q-item-section>
            <q-item-label>{{ locale.label }}</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-popup-proxy>
  </q-btn>
  <q-item v-else clickable :disable="loading">
    <q-item-section>{{ $t(curentLocale) }}</q-item-section>
    <q-item-section side>
      <q-spinner v-if="loading" size="20px" />
      <q-icon v-else class="language-emoji" :name="localeFlag" />
    </q-item-section>
    <q-popup-proxy :breakpoint="5000" class="popup-dropdown">
      <q-list style="min-width: 140px" v-close-popup>
        <q-item
          v-for="locale in localeOptions"
          :key="locale.value"
          @click="changeLanguage(locale.value)"
          clickable
          :disable="loading"
        >
          <q-item-section side><q-icon class="language-emoji" :name="locale.icon" /></q-item-section>
          <q-item-section>
            <q-item-label>{{ locale.label }}</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-popup-proxy>
  </q-item>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiWeb } from '@quasar/extras/mdi-v7';
import { getCountryFlag } from 'components/Localization/LocalizationLoader';
import { loadLanguageAsync, SUPPORT_LOCALES, type MessageLanguages } from 'boot/app';

export default defineComponent({
  name: 'LanguageChanger',
  setup: () => ({ mdiWeb }),
  props: {
    listItem: {
      type: Boolean,
      default: null,
    },
  },
  data() {
    return {
      loading: false,
    };
  },
  computed: {
    curentLocale(): string {
      return this.$i18n.locale as string;
    },
    localeOptions() {
      const locales: Array<{ value: string; label: string; icon: string }> = [];

      SUPPORT_LOCALES.forEach((locale) => {
        const country = locale.split('-')[1].toLowerCase();
        locales.push({
          value: locale,
          label: this.$t(locale),
          icon: getCountryFlag(country.toUpperCase()) ?? '',
        });
      });

      return locales;
    },
    localeFlag(): string {
      const found = this.localeOptions.find((locales) => {
        return locales.value === this.curentLocale;
      });
      return found?.icon ?? '';
    },
  },
  methods: {
    async changeLanguage(locale: string) {
      if (this.curentLocale === locale || this.loading) {
        return;
      }

      this.loading = true;

      try {
        await loadLanguageAsync(locale as MessageLanguages);
        localStorage.setItem('locale', locale);
        await this.updateProfileLanguage(locale);
      } catch (error) {
        console.error('Language change failed:', error);
      } finally {
        this.loading = false;
      }
    },

    async updateProfileLanguage(val: string) {
      // Check Auth
      if (!this.$authStore.user?.id) {
        return;
      }

      const lang = val.split('-')[0];

      try {
        await this.$api.main.ProfileChangeLanguage({ language: lang }, { showMessage: false });
        this.$authStore.user.language = lang;
      } catch (error) {
        console.error('Failed to update profile language:', error);
      }
    },
  },
});
</script>
