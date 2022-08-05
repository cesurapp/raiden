<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">{{ $t('Register') }}</h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">{{ $t('Create a new account.') }}</h6>
    </div>

    <q-form @keydown.enter.prevent="onSubmit" class="q-gutter-xs" ref="form">
      <!--Email-->
      <q-input outlined lazy-rules v-model="email" :label="$t('Email')"
               :error="$rules.ssrValid('email')" :error-message="$rules.ssrException('email')"
               :rules="[$rules.required(), $rules.email()]">
        <template v-slot:prepend><q-icon name="email"/></template>
      </q-input>

      <!--Phone-->
      <PhoneInput ref="phone" v-model="phone" :label="$t('Phone')"></PhoneInput>

      <!--Password-->
      <q-input outlined :type="isPwd ? 'password' : 'text'" v-model="password" :label="$t('Password')" lazy-rules :rules="[$rules.required(),$rules.minLength(8)]">
        <template v-slot:prepend><q-icon name="key"/></template>
        <template v-slot:append>
          <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="isPwd = !isPwd"/>
        </template>
      </q-input>

      <!--FirstName-->
      <q-input outlined v-model="firstName" :label="$t('First Name')" lazy-rules :rules="[$rules.required(),$rules.minLength(2)]">
        <template v-slot:prepend><q-icon name="person"/></template>
      </q-input>

      <!--LastName-->
      <q-input outlined v-model="lastName" :label="$t('Last Name')" lazy-rules key="222" :rules="[$rules.required(),$rules.minLength(2)]">
        <template v-slot:prepend><q-icon name="person"/></template>
      </q-input>

      <div>
        <q-btn :label="$t('Register')" @click="onSubmit" no-caps padding="sm md" color="primary" icon="how_to_reg"/>
        <q-btn :label="$t('Login')" padding="sm md" no-caps color="primary" flat :to="{ name: 'auth.login' }" class="q-ml-sm"/>
      </div>
    </q-form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import PhoneInput from 'components/PhoneValidation/PhoneInput.vue';
import {createMetaMixin} from 'quasar';

export default defineComponent({
  name: 'AuthRegister',
  components: {PhoneInput},
  mixins: [
    createMetaMixin({
      title: 'Register'
    })
  ],
  data: () => ({
    isPwd: true,
    email: null,
    phone: null,
    password: null,
    firstName: null,
    lastName: null,
  }),
  methods: {
    onSubmit() {
      // Clear Backend Validation Errors
      this.$rules.clearSSRException();

      // Register
      this.$refs.form.validate().then((success) => {
        if (success) {
          this.$api.securityRegister({
            password: this.password,
            firstName: this.firstName,
            lastName: this.lastName,
            email: this.email,
            phone: this.phone,
            phoneCountry: this.$refs.phone.country,
          }).then((r) => {
            // Redirect Login Page
            if (r.data.data.approved) {
              return this.$router.push({name: 'auth.login'});
            }

            // Redirect Approve Page
            return this.$router.push({name: 'auth.register.confirm', params: {id: btoa(this.email ?? this.phone)}});
          })
        }
      })
    }
  }
})
</script>
