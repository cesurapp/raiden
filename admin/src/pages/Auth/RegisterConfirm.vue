<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">
        {{ $t('Approve Account') }}
      </h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">
        {{ $t('Enter the code sent to your phone or e-mail address.') }}
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

      <div>
        <q-btn :label="$t('Approve')" @click="onSubmit" :loading="$appStore.isBusy" color="primary" :icon="mdiLogin" />
        <q-btn :label="$t('Login')" color="primary" flat :to="{ name: 'auth.login' }" class="q-ml-sm" />
      </div>
    </q-form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import { mdiCellphoneKey, mdiLogin } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'RegisterConfirm',
  setup: () => ({ mdiCellphoneKey, mdiLogin }),
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t('Approve Account'),
      };
    }),
  ],
  data: () => ({
    otp_key: null,
  }),
  methods: {
    onSubmit() {
      this.$rules.clearSSRException();
      this.$refs.form.validate().then((success) => {
        if (success) {
          this.$api
            .securityApprove({
              otp_key: this.otp_key,
              username: atob(this.$route.params.id),
            })
            .then(() => {
              this.$router.push({ name: 'auth.login' });
            })
            .catch(() => {
              const errors = this.$rules.ssrException('id', false);
              if (errors) {
                errors.forEach((error) => this.$appStore.notifyDanger(error));
              }
            });
        }
      });
    },
  },
});
</script>
