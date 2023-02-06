<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">
        {{ $t('Welcome') }}
      </h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">
        {{ $t('Login to continue') }}
      </h6>
    </div>

    <!-- Login Form-->
    <q-form @keydown.enter.prevent="onSubmit" class="q-gutter-xs" ref="form">
      <q-tabs
        v-model="type"
        align="left"
        inline-label
        no-caps
        active-bg-color="dark-transparent-1"
        class="text-primary q-mb-md"
      >
        <q-tab :ripple="false" name="email" icon="mail" :label="$t('Email')" />
        <q-tab :ripple="false" name="phone" icon="phone" :label="$t('Phone')" />
      </q-tabs>

      <!--Username-->
      <q-input
        v-if="type === 'email'"
        :class="{ 'q-pb-xs': isOtp }"
        bottom-slots
        outlined
        v-model="username"
        :label="$t('Email')"
        lazy-rules
        :rules="[$rules.required(), $rules.email()]"
      >
        <template v-slot:prepend><q-icon name="mail" /></template>
      </q-input>

      <!--Phone-->
      <PhoneInput
        v-else
        v-model:phone-number="username"
        :class="{ 'q-pb-xs': isOtp }"
        :label="$t('Phone')"
      ></PhoneInput>

      <!--Password-->
      <q-input
        :disable="isOtp"
        v-show="!isOtp"
        class="q-pb-xs"
        outlined
        :type="isPwd ? 'password' : 'text'"
        v-model="password"
        :label="$t('Password')"
        lazy-rules
        :rules="[$rules.required(), $rules.minLength(8)]"
      >
        <template v-slot:prepend><q-icon name="key" /></template>
        <template v-slot:append>
          <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="isPwd = !isPwd" />
        </template>
      </q-input>

      <!--PasswordLess Login-->
      <div class="flex justify-between items-center">
        <q-checkbox v-model="isOtp" dense :label="$t('Passwordless Login')" />
        <q-btn
          :disable="isOtp"
          color="grey-7"
          v-show="!isOtp"
          flat
          dense
          no-caps
          size="md"
          class="q-px-sm"
          :to="{ name: 'auth.reset.request' }"
          :label="$t('Forgot Password')"
        ></q-btn>
      </div>

      <!--Submit-->
      <q-btn
        class="q-mt-md"
        no-caps
        :label="$t('Login')"
        :loading="$isBusy.value"
        @click="onSubmit"
        color="primary"
        icon="login"
      />
    </q-form>

    <!-- Footer-->
    <div class="auth-footer q-mt-xl">
      <div class="or-hr q-mb-xl"><span>or</span></div>

      <!-- Register Link-->
      <div class="register-actions">
        <q-btn
          :to="{ name: 'auth.register' }"
          :label="$t('Register')"
          type="button"
          color="primary"
          icon="email"
          class="full-width"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import { useAuthStore } from 'stores/AuthStore';
import PhoneInput from 'components/Phone/PhoneInput.vue';

export default defineComponent({
  name: 'AuthLogin',
  components: { PhoneInput },
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t('Login'),
      };
    }),
  ],
  data() {
    return {
      type: 'email',
      isPwd: true,
      isOtp: false,
      username: null,
      password: null,
    };
  },
  methods: {
    onSubmit() {
      this.$refs.form.validate().then((success: any) => {
        if (success) {
          if (this.isOtp) {
            useAuthStore().loginOtpRequest(this.username);
          } else {
            useAuthStore().loginUsername(this.username, this.password);
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
