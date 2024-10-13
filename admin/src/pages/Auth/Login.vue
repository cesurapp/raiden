<template>
  <div>
    <!--Header-->
    <div class="q-mb-lg q-mb-md-xl text-center">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">{{ $t('Welcome') }}</h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">{{ $t('Login to continue') }}</h6>
    </div>

    <!-- Login Form-->
    <q-form @keydown.enter.prevent="onSubmit" class="q-gutter-xs" ref="form">
      <q-tabs
        align="left"
        inline-label
        switch-indicator
        indicator-color="primary"
        active-bg-color="primary"
        active-class="text-white"
        class="q-mb-md login-tab"
        v-model="type"
      >
        <q-tab :ripple="false" name="email" :icon="mdiEmail" :label="$t('Email')" />
        <q-tab :ripple="false" name="phone" :icon="mdiPhone" :label="$t('Phone')" />
      </q-tabs>

      <!--Username-->
      <q-input
        v-if="type === 'email'"
        type="email"
        :class="{ 'q-pb-xs': isOtp }"
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
        v-model:fullNumber="username"
        v-model:phoneCountry="phone_country"
        :class="{ 'q-pb-xs': isOtp }"
        :label="$t('Phone')"
      ></PhoneInput>

      <!--Password-->
      <q-input
        :disable="isOtp"
        v-if="!isOtp"
        class="q-pb-xs"
        outlined
        autocomplete
        :type="isPwd ? 'password' : 'text'"
        v-model="password"
        :label="$t('Password')"
        lazy-rules
        :rules="[$rules.required(), $rules.minLength(8)]"
      >
        <template v-slot:prepend><q-icon :name="mdiKey" /></template>
        <template v-slot:append>
          <q-icon :name="isPwd ? mdiEyeOff : mdiEye" class="cursor-pointer q-ml-sm" @click="isPwd = !isPwd" />
          <q-icon :name="mdiLockQuestion" class="cursor-pointer q-ml-sm" @click="$router.push({ name: 'auth.reset.request' })">
            <q-tooltip>{{ $t('Forgot Password') }}</q-tooltip>
          </q-icon>
        </template>
      </q-input>

      <div class="flex justify-between items-center gap-x-md">
        <q-btn
          unelevated
          color="primary"
          class="flex-1"
          :label="$t('Login')"
          :loading="$appStore.isBusy"
          @click="onSubmit"
          :icon="mdiLogin"
        />
        <q-checkbox v-model="isOtp" dense :label="$t('Passwordless Login')" />
      </div>
    </q-form>

    <!-- Footer-->
    <div class="auth-footer q-mt-xl">
      <div class="or-hr q-mb-xl"><span>or</span></div>

      <!-- Register Link-->
      <div class="register-actions">
        <q-btn
          :to="{ name: 'auth.register' }"
          :label="$t('Register')"
          color="primary"
          :icon="mdiEmail"
          class="full-width"
          unelevated
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import PhoneInput from 'components/Localization/PhoneInput.vue';
import { mdiEmail, mdiPhone, mdiKey, mdiLogin, mdiEye, mdiEyeOff, mdiLockQuestion } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'AuthLogin',
  setup: () => ({ mdiEmail, mdiPhone, mdiKey, mdiLogin, mdiEye, mdiEyeOff, mdiLockQuestion }),
  components: { PhoneInput },
  data: () => ({
    type: 'email',
    isPwd: true,
    isOtp: false,
    username: '',
    password: null,
    phone_country: 'TR',
  }),
  methods: {
    onSubmit() {
      this.$refs.form.validate().then((success: any) => {
        if (success) {
          if (this.isOtp) {
            this.$authStore.loginOtpRequest(this.username);
          } else {
            this.$authStore.loginUsername(this.username, this.password);
          }
        }
      });
    },
  },
});
</script>

<style lang="scss" scoped>
.or-hr {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;

  &:before {
    content: ' ';
    position: absolute;
    height: 2px;
    left: 0;
    width: 100%;
    background: $grey-4;
  }

  span {
    background: #fff;
    z-index: 2;
    padding: 0 2rem;
    line-height: 5px;
  }
}

body.body--dark {
  .or-hr {
    &:before {
      background: $grey-6;
    }
    span {
      background: var(--q-dark);
    }
  }
}
</style>
