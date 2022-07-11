<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">{{ $t('Forgot Password') }}</h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">{{ $t('Reset password by Email or Phone.') }}</h6>
    </div>

    <q-form @submit.stop="onSubmit" class="q-gutter-xs" ref="form">
      <!--Username-->
      <q-input outlined v-model="username" :label="$t('Email / Phone')" lazy-rules :rules="[$rules.required(),$rules.isIdentity()]">
        <template v-slot:prepend><q-icon :name="!getCountry ? 'mail' : `img:/images/flags/${getCountry}.svg`"/></template>
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
import {extractPhone} from 'components/PhoneValidation/PhoneCodeList';
export default defineComponent({
  name: 'ResetRequest',
  data() {
    return {
      username: null,
    }
  },
  computed: {
    getCountry() {
      return !isNaN(this.username) ? extractPhone(this.username)?.country : null
    }
  },
  methods: {
    onSubmit() {
      this.$refs.form.validate().then(success => {
        if (success) {

        }
      })
    }
  }
})
</script>
