<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">{{ $t('Welcome') }}</h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">{{ $t('Login to continue') }}</h6>
    </div>

    <!-- Login Form-->
    <q-form @submit.stop="onSubmit" class="q-gutter-xs" ref="form">
      <!--Username-->
      <q-input outlined v-model="username" :label="$t('Email / Phone')" lazy-rules :rules="[$rules.required(),$rules.isIdentity()]">
        <template v-slot:prepend><q-icon :name="!getCountry ? 'mail' : `img:/images/flags/${getCountry}.svg`"/></template>
      </q-input>

      <!--Password-->
      <q-input outlined :type="isPwd ? 'password' : 'text'" v-model="password" :label="$t('Password')" lazy-rules :rules="[$rules.required(),$rules.minLength(8)]">
        <template v-slot:prepend><q-icon name="key"/></template>
        <template v-slot:append>
          <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="isPwd = !isPwd"/>
        </template>
      </q-input>

      <div>
        <q-btn :label="$t('Login')" type="submit" color="primary" padding="sm md" icon="login"/>
        <q-btn :label="$t('Forgot Password')" color="primary" flat padding="sm md" :to="{ name: 'auth.reset.request' }" class="q-ml-sm"/>
      </div>
    </q-form>

    <!-- Footer-->
    <div class="auth-footer q-mt-xl">
      <div class="or-hr q-mb-xl"><span>or</span></div>

      <!-- Register Link-->
      <div class="register-actions">
        <q-btn :to="{ name: 'auth.register' }" :label="$t('Register')" type="button" color="primary"  outline icon="email" padding="sm" class="full-width"/>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import {extractPhone} from 'components/PhoneValidation/PhoneCodeList';

export default defineComponent({
  name: 'AuthLogin',
  data() {
    return {
      isPwd: true,
      username: null,
      password: null,
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

<style lang="scss" scoped>
.or-hr {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;

  &:before {
    content: " ";
    position: absolute;
    height: 2px;
    left: 0;
    width: 100%;
    background: $grey-4;
  }

  span {
    background: #FFF;
    z-index: 2;
    padding: 0 2rem;
    line-height: 5px;
  }
}
</style>
