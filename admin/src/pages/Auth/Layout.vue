<template>
  <q-layout view="lHh Lpr lFf" class="bg-indigo-12 row justify-center items-center">
    <div class="auth-wrapper row bg-white rounded-borders q-ma-lg">
      <div class="row full-width">
        <div class="xs-hide sm-hide col-12 col-md-7 q-pa-lg flex items-center">
          <q-img src="/images/login.svg" style="width: 100%"/>
        </div>
        <div class="col-12 col-md-5 justify-center items-center flex">
          <div class="q-pa-xs-lg q-pa-sm-xl full-width">
            <transition appear enter-active-class="animated fadeIn" leave-active-class="animated fadeOut">
              <router-view/>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </q-layout>
</template>

<script lang="ts">
export default {
  name: 'AuthLayout'
}
</script>

<style lang="scss" scoped>
.auth-wrapper {
  max-width: 1200px;
  width: 100%;
}
</style>
