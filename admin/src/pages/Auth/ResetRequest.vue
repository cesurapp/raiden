<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">
        {{ $t('Forgot Password') }}
      </h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">
        {{ $t('Reset password by Email or Phone.') }}
      </h6>
    </div>

    <q-form @keydown.enter.prevent="onSubmit" class="q-gutter-xs" ref="form">
      <q-tabs
        align="left"
        inline-label
        no-caps
        v-model="type"
        active-bg-color="dark-transparent-1"
        class="text-primary q-mb-md login-tab"
      >
        <q-tab :ripple="false" name="email" :icon="mdiEmail" :label="$t('Email')" />
        <q-tab :ripple="false" name="phone" :icon="mdiPhone" :label="$t('Phone')" />
      </q-tabs>

      <!--Username-->
      <q-input
        v-if="type === 'email'"
        bottom-slots
        outlined
        v-model="username"
        :label="$t('Email')"
        lazy-rules
        :rules="[$rules.required(), $rules.email()]"
      >
        <template v-slot:prepend><q-icon :name="mdiEmail" /></template>
      </q-input>

      <!--Phone-->
      <PhoneInput
        v-else
        outlined
        :modelValue="username"
        v-model:full-number="username"
        phone-country="TR"
        :label="$t('Phone')"
      ></PhoneInput>

      <div>
        <q-btn
          :label="$t('Reset')"
          :loading="$appStore.isBusy"
          @click="onSubmit"
          type="button"
          color="primary"
          :icon="mdiLockReset"
        />
        <q-btn :label="$t('Login')" color="primary" flat :to="{ name: 'auth.login' }" class="q-ml-sm" />
      </div>
    </q-form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import PhoneInput from 'components/Phone/PhoneInput.vue';
import { createMetaMixin } from 'quasar';
import { mdiLockReset, mdiEmail, mdiPhone } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'ResetRequest',
  setup: () => ({ mdiLockReset, mdiEmail, mdiPhone }),
  components: { PhoneInput },
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t(String(this.$route.meta.breadcrumb)),
      };
    }),
  ],
  data: () => ({
    type: 'email',
    username: null,
  }),
  methods: {
    onSubmit() {
      this.$refs.form.validate().then((success: boolean) => {
        if (success) {
          this.$api.authSecurityResetRequest({ username: this.username }).then(() => {
            this.$router.push({
              name: 'auth.reset.password',
              params: { id: btoa(this.username) },
            });
          });
        }
      });
    },
  },
});
</script>
