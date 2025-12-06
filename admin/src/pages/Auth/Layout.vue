<template>
  <q-layout
    view="lHh Lpr lFf"
    class="bg-indigo-12 column justify-center items-center"
    :class="[$q.dark.isActive ? 'bg-dark-page' : 'bg-indigo-12']"
  >
    <div class="auth-main flex column no-wrap q-ma-lg">
      <!--Pages-->
      <div :class="[$q.dark.isActive ? 'bg-dark' : 'bg-white']" class="wrapper full-width rounded-borders q-pa-lg">
        <router-view v-slot="{ Component }">
          <transition appear mode="out-in" enter-active-class="animated fadeIn" leave-active-class="animated fadeOut">
            <component :is="Component"></component>
          </transition>
        </router-view>
      </div>

      <!--Actions-->
      <div class="lang-wrap full-width flex items-center justify-end q-pt-md text-white">
        <DarkModeChanger size="md" :only-white="true"></DarkModeChanger>
        <span class="divider"></span>
        <LanguageChanger></LanguageChanger>
      </div>
    </div>
  </q-layout>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import LanguageChanger from 'components/Language/LanguageChanger.vue';
import DarkModeChanger from 'components/DarkModeChanger.vue';
import { createMetaMixin } from 'quasar';

export default defineComponent({
  name: 'AuthLayout',
  components: { LanguageChanger, DarkModeChanger },
  mixins: [
    createMetaMixin(function () {
      return {
        titleTemplate: (title) => `${title} - ` + this.$appStore.title,
      };
    }),
  ],
  mounted() {
    if (this.$authStore.isLoggedIn()) {
      this.$router.push({ path: '/' });
    }
  },
});
</script>

<style lang="scss">
.auth-main {
  display: flex;
  max-width: 100%;
  width: 500px;

  .q-btn {
    min-height: 42px;
  }
}
.login-tab {
  .q-tabs__content {
    gap: 10px;
  }
  .q-tab {
    border-radius: 4px;
    overflow: hidden;

    .q-tab__indicator {
      display: none !important;
    }
  }
}
</style>
