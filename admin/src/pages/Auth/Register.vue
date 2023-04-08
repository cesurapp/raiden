<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">
        {{ $t('Register') }}
      </h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">
        {{ $t('Create a new account.') }}
      </h6>
    </div>

    <q-form @keydown.enter.prevent="onSubmit" class="q-gutter-xs" ref="form">
      <!--Email-->
      <q-input
        outlined
        lazy-rules
        v-model="data.email"
        :label="$t('Email')"
        :error="$rules.ssrValid('email')"
        :error-message="$rules.ssrException('email')"
        :rules="[$rules.required(), $rules.email()]"
      >
        <template v-slot:prepend><q-icon :name="mdiEmail" /></template>
      </q-input>

      <!--Phone-->
      <PhoneInput
        outlined
        :modelValue="data.phone"
        v-model:full-number="data.phone"
        v-model:phone-country="data.phone_country"
        :label="$t('Phone')"
      ></PhoneInput>

      <!--Password-->
      <q-input
        outlined
        :type="isPwd ? 'password' : 'text'"
        v-model="data.password"
        :label="$t('Password')"
        lazy-rules
        :rules="[$rules.required(), $rules.minLength(8)]"
      >
        <template v-slot:prepend><q-icon :name="mdiKey" /></template>
        <template v-slot:append>
          <q-icon :name="isPwd ? mdiEyeOff : mdiEye" class="cursor-pointer" @click="isPwd = !isPwd" />
        </template>
      </q-input>

      <!--FirstName-->
      <q-input
        outlined
        v-model="data.first_name"
        :label="$t('First Name')"
        lazy-rules
        :rules="[$rules.required(), $rules.minLength(2)]"
      >
        <template v-slot:prepend><q-icon :name="mdiAccount" /></template>
      </q-input>

      <!--LastName-->
      <q-input
        outlined
        v-model="data.last_name"
        :label="$t('Last Name')"
        lazy-rules
        :rules="[$rules.required(), $rules.minLength(2)]"
      >
        <template v-slot:prepend><q-icon :name="mdiAccount" /></template>
      </q-input>

      <div>
        <q-btn
          :label="$t('Register')"
          @click="onSubmit"
          :loading="$appStore.isBusy"
          color="primary"
          :icon="mdiAccountPlus"
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
import { mdiEmail, mdiEye, mdiEyeOff, mdiKey, mdiAccount, mdiAccountPlus } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'AuthRegister',
  setup: () => ({ mdiEmail, mdiKey, mdiEye, mdiEyeOff, mdiAccount, mdiAccountPlus }),
  components: { PhoneInput },
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t(String(this.$route.meta.breadcrumb)),
      };
    }),
  ],
  data: () => ({
    isPwd: true,
    data: {
      email: null,
      phone: null,
      phone_country: 'TR',
      password: null,
      first_name: null,
      last_name: null,
    },
  }),
  methods: {
    onSubmit() {
      // Clear Backend Validation Errors
      this.$rules.clearSSRException();

      // Register
      this.$refs.form.validate().then((success) => {
        if (success) {
          this.$api.securityRegister(this.data).then((r) => {
            // Redirect Login Page
            if (r.data.data.approved) {
              return this.$router.push({ name: 'auth.login' });
            }

            // Redirect Approve Page
            return this.$router.push({
              name: 'auth.register.confirm',
              params: { id: btoa(this.data.email ?? this.data.phone) },
            });
          });
        }
      });
    },
  },
});
</script>
