<template>
  <q-layout
    view="lHh Lpr lFf"
    class="bg-indigo-12 column justify-center items-center"
    :class="[$q.dark.isActive ? 'bg-dark-page' : 'bg-indigo-12']"
  >
    <div class="auth-main flex column no-wrap q-pa-lg">
      <div :class="[$q.dark.isActive ? 'bg-dark' : 'bg-white']" class="wrapper row full-width rounded-borders">
        <div class="col-12 justify-center items-center flex">
          <div class="q-pa-xs-lg q-pa-sm-xl full-width">
            <router-view v-slot="{ Component }">
              <transition appear mode="out-in" enter-active-class="animated fadeIn" leave-active-class="animated fadeOut">
                <component :is="Component"></component>
              </transition>
            </router-view>
          </div>
        </div>
      </div>

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
  max-width: 550px;
  width: 100%;

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
