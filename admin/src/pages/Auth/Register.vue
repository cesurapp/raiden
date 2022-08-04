<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">{{ $t('Register') }}</h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">{{ $t('Create a new account.') }}</h6>
    </div>

    <q-form class="q-gutter-xs" ref="form">
      <!--Email-->
      <q-input outlined v-model="email" :label="$t('Email')" lazy-rules debounce="250" :rules="[$rules.required(),$rules.serverSide('email'),$rules.email()]">
        <template v-slot:prepend><q-icon name="email"/></template>
      </q-input>
      <!--Phone-->
      <PhoneInput v-model="phone" :label="$t('Phone')"></PhoneInput>
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
      <q-input outlined v-model="lastName" :label="$t('Last Name')" lazy-rules :rules="[$rules.required(),$rules.minLength(2)]">
        <template v-slot:prepend><q-icon name="person"/></template>
      </q-input>

      <div>
        <q-btn :label="$t('Register')" @click="onSubmit" padding="sm md" color="primary" icon="how_to_reg"/>
        <q-btn :label="$t('Login')" padding="sm md" color="primary" flat :to="{ name: 'auth.login' }" class="q-ml-sm"/>
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
      this.$rules.clearServerSide();
      this.$refs.form.validate().then((success) => {
        if (success) {
          this.$api.securityRegister({
            password: this.password,
            firstName: this.firstName,
            lastName: this.lastName,
            email: this.email,
            phone: this.phone,
            phoneCountry: 'TR',
          }).then((r) => {
            console.log(r.data)
          }).catch(() => {
            this.$refs.form.validate()
          })
        } else {
          console.log('as');
        }
      })
    }
  }
})
</script>
