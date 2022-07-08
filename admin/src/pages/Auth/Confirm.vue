<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">{{ $t('Confirmation') }}</h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">{{ $t('Reset password by Email or Phone.') }}</h6>
    </div>

    <!--Message-->
    <div class="q-mb-lg text-subtitle1">Hesabınız doğrulandı, şimdi giriş yapabilirsiniz.</div>
    <q-btn :label="$t('Login')" padding="sm md" color="primary" icon="login" :to="{ name: 'auth.login' }"/>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
  name: 'RegisterConfirm',
})
</script>
