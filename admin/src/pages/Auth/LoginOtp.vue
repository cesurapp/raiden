<template>
  <div>
    <!--Header-->
    <div class="q-mb-lg q-mb-md-xl text-center">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">{{ $t('Security Code') }}</h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">
        {{ $t('Enter the 6-digit code sent to your account (mail/phone) to login.') }}
      </h6>
    </div>

    <q-form @keydown.enter.prevent="onSubmit" class="q-gutter-xs" ref="form">
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
        <template v-slot:prepend><q-icon :name="mdiCellphoneKey" /></template>
      </q-input>

      <div class="flex justify-between items-center gap-x-md">
        <q-btn
          class="flex-1"
          :label="$t('Login')"
          @click="onSubmit"
          :loading="$appStore.isBusy"
          color="primary"
          :icon="mdiLogin"
        />
        <q-btn :label="$t('Back')" color="primary" flat :to="{ name: 'auth.login' }" class="q-ml-sm" />
      </div>
    </q-form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiCellphoneKey, mdiLogin } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'AuthLoginOtp',
  setup: () => ({ mdiCellphoneKey, mdiLogin }),
  data: () => ({
    otp_key: null,
  }),
  methods: {
    onSubmit() {
      this.$rules.clearSSRException();
      this.$refs.form.validate().then((success) => {
        if (success) {
          this.$authStore.loginOtp(atob(this.$route.params.id), this.otp_key);
        }
      });
    },
  },
});
</script>
