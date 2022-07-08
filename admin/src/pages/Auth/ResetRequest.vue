<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">{{ $t('Forgot Password') }}</h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">{{ $t('Reset password by Email or Phone.') }}</h6>
    </div>

    <!-- Login Form-->
    <q-form @submit="onSubmit" class="q-gutter-xs" ref="form">
      <!--Username-->
      <q-input outlined bottom-slots v-model="identity" :label="$t('Email / Phone')" lazy-rules :rules="[ val => val && val.length > 6]">
        <template v-slot:prepend><q-icon name="person"/></template>
      </q-input>

      <div>
        <q-btn :label="$t('Send')" padding="sm md" type="submit" color="primary" icon="login"/>
        <q-btn :label="$t('Login')" padding="sm md" color="primary" flat :to="{ name: 'auth.login' }" class="q-ml-sm"/>
      </div>
    </q-form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
  name: 'ResetRequest',
  data() {
    return {
      identity: null,
    }
  },
  methods: {
    onSubmit() {
      this.$refs.form.validate().then(success => {
        if (success) {
          this.$q.notify({
            position: 'top',
            color: 'red-5',
            textColor: 'white',
            icon: 'warning',
            message: 'You need to accept the license and terms first'
          })
        }
        else {
        }
      })
    }
  }
})
</script>
