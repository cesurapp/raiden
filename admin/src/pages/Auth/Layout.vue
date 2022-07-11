<template>
  <q-layout view="lHh Lpr lFf" class="bg-indigo-12 column justify-center items-center">
    <div class="auth-main flex column no-wrap q-pa-lg">
      <div class="wrapper row full-width bg-white rounded-borders">
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
        <LanguageChanger></LanguageChanger>
      </div>
    </div>
  </q-layout>
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import LanguageChanger from 'components/LanguageChanger.vue';
import {createMetaMixin} from 'quasar';

export default defineComponent( {
  name: 'AuthLayout',
  components: {LanguageChanger},
  mixins: [
    createMetaMixin({
      title: 'App',
      titleTemplate: title => `${title} - Auth`,
    })
  ],
});
</script>

<style lang="scss" scoped>
.auth-main {
  max-width: 1200px;
  width: 100%;
  .wrapper {
    min-height: 610px;
  }
}
</style>
