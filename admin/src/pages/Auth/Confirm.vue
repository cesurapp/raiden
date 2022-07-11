<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">{{ $t('Change Password') }}</h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">{{ $t('Reset your password.') }}</h6>
    </div>

    <q-form @submit.stop="onSubmit" class="q-gutter-xs" ref="form">
      <!--Password-->
      <q-input outlined :type="isPwd ? 'password' : 'text'" v-model="password" :label="$t('Password')" lazy-rules :rules="[$rules.required(),$rules.minLength(8)]">
        <template v-slot:prepend><q-icon name="key"/></template>
        <template v-slot:append>
          <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="isPwd = !isPwd"/>
        </template>
      </q-input>
      <!--Password-->
      <q-input outlined :type="isPwd ? 'password' : 'text'" v-model="password_confirm" :label="$t('Password Confirm')" lazy-rules :rules="[$rules.required(),$rules.minLength(8),$rules.sameAs(this.password)]">
        <template v-slot:prepend><q-icon name="key"/></template>
        <template v-slot:append>
          <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="isPwd = !isPwd"/>
        </template>
      </q-input>

      <div>
        <q-btn :label="$t('Change')" type="submit" padding="sm md" color="primary" icon="how_to_reg"/>
        <q-btn :label="$t('Login')" padding="sm md" color="primary" flat :to="{ name: 'auth.login' }" class="q-ml-sm"/>
      </div>
    </q-form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
  name: 'AuthConfirm',
  data() {
    return {
      isPwd: true,
      password: null,
      password_confirm: null,
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
