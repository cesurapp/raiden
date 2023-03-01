<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">
        {{ $t('Change Password') }}
      </h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">
        {{ $t('Reset your password.') }}
      </h6>
    </div>

    <q-form @submit.stop="onSubmit" class="q-gutter-xs" ref="form">
      <!--OTP Key-->
      <q-input
        outlined
        lazy-rules
        v-model="otp_key"
        mask="# # # # # #"
        fill-mask
        unmasked-value
        :error="$rules.ssrValid('otp_key')"
        :error-message="$rules.ssrException('otp_key')"
        :label="$t('Code')"
        :rules="[$rules.required(), $rules.minLength(6), $rules.maxLength(6)]"
      >
        <template v-slot:prepend><q-icon :name="mdiKey" /></template>
      </q-input>

      <!--Password-->
      <q-input
        outlined
        :type="isPwd ? 'password' : 'text'"
        v-model="password"
        :label="$t('Password')"
        lazy-rules
        :rules="[$rules.required(), $rules.minLength(8)]"
      >
        <template v-slot:prepend><q-icon :name="mdiKey" /></template>
        <template v-slot:append>
          <q-icon :name="isPwd ? mdiEyeOff : mdiEye" class="cursor-pointer" @click="isPwd = !isPwd" />
        </template>
      </q-input>

      <!--Password-->
      <q-input
        outlined
        :type="isPwd ? 'password' : 'text'"
        v-model="password_confirm"
        :label="$t('Password Confirm')"
        lazy-rules
        :rules="[$rules.required(), $rules.minLength(8), $rules.sameAs(this.password)]"
      >
        <template v-slot:prepend><q-icon :name="mdiKey" /></template>
        <template v-slot:append>
          <q-icon :name="isPwd ? mdiEyeOff : mdiEye" class="cursor-pointer" @click="isPwd = !isPwd" />
        </template>
      </q-input>

      <div>
        <q-btn :label="$t('Change')" :loading="$appStore.isBusy" type="submit" color="primary" :icon="mdiLockReset" />
        <q-btn :label="$t('Login')" color="primary" flat :to="{ name: 'auth.login' }" class="q-ml-sm" />
      </div>
    </q-form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import { mdiKey, mdiEye, mdiEyeOff, mdiLockReset } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'ResetPassword',
  setup: () => ({ mdiKey, mdiEye, mdiEyeOff, mdiLockReset }),
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t('Change Password'),
      };
    }),
  ],
  data: () => ({
    isPwd: true,
    id: null,
    otp_key: null,
    password: null,
    password_confirm: null,
  }),
  methods: {
    onSubmit() {
      this.$refs.form.validate().then((success) => {
        if (success) {
          this.$api
            .securityResetPassword({
              username: atob(this.$route.params.id),
              otp_key: this.otp_key,
              password: this.password,
              password_confirm: this.password_confirm,
            })
            .then(() => {
              this.$router.push({ name: 'auth.login' });
            });
        }
      });
    },
  },
});
</script>
