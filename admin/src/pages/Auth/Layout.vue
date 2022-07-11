<template>
  <q-layout view="lHh Lpr lFf" class="auth-layout column justify-center items-center">
    <div class="auth-main flex column no-wrap q-pa-lg">
      <div class="wrapper row full-width rounded-borders">
        <div class="xs-hide sm-hide col-12 col-md-7 q-pa-lg flex items-center">
          <q-img src="/images/login.svg" style="width: 100%"/>
        </div>
        <div class="col-12 col-md-5 justify-center items-center flex">
          <div class="q-pa-xs-lg q-pa-sm-xl full-width">
            <router-view v-slot="{ Component }">
              <transition appear leave-active-class="animated fadeOut" mode="out-in" enter-active-class="animated fadeIn">
                <component :is="Component"></component>
              </transition>
            </router-view>
          </div>
        </div>
      </div>

      <div class="lang-wrap full-width flex justify-between items-center q-pt-md text-white">
        <!--Footer-->
        <div>{{ $t('Copyright') }}</div>

        <!--Language Changer-->
        <div class="flex row items-center">
          <DarkModeChanger></DarkModeChanger>
          <LanguageChanger></LanguageChanger>
        </div>
      </div>
    </div>
  </q-layout>
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import LanguageChanger from 'components/LanguageChanger.vue';
import DarkModeChanger from 'components/DarkModeChanger.vue';
import {createMetaMixin} from 'quasar';

export default defineComponent( {
  name: 'AuthLayout',
  components: {LanguageChanger, DarkModeChanger},
  mixins: [
    createMetaMixin({
      title: 'Authentication',
      titleTemplate: title => `${title} - ` + process.env.APP_TITLE,
    })
  ],
});
</script>

<style lang="scss" scoped>
.auth-layout{
  background: $indigo-12;
  .wrapper{
    background: white;
  }
}

body.body--dark {
  .auth-layout {
    background: var(--q-dark-page);
    .wrapper {
      background-color: var(--q-dark);
    }
  }
}

.auth-main {
  max-width: 1200px;
  width: 100%;
  .wrapper {
    min-height: 610px;
  }
}
</style>
