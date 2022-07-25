<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">{{ $t('Welcome') }}</h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">{{ $t('Login to continue') }}</h6>
    </div>

    <!-- Login Form-->
    <q-form @submit.stop="onSubmit" class="q-gutter-xs" ref="form">
      <!--Username-->
      <q-input outlined v-model="username" :label="$t('Email / Phone')" lazy-rules :rules="[$rules.required(),$rules.isIdentity()]">
        <template v-slot:prepend><q-icon :name="!getCountry ? 'mail' : `img:/images/flags/${getCountry}.svg`"/></template>
      </q-input>

      <!--Password-->
      <q-input outlined :type="isPwd ? 'password' : 'text'" v-model="password" :label="$t('Password')" lazy-rules :rules="[$rules.required(),$rules.minLength(8)]">
        <template v-slot:prepend><q-icon name="key"/></template>
        <template v-slot:append>
          <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="isPwd = !isPwd"/>
        </template>
      </q-input>

      <div>
        <q-btn :label="$t('Login')" :loading="$isBusy.value" type="submit" color="primary" padding="sm md" icon="login"/>
        <q-btn :label="$t('Forgot Password')" color="primary" flat padding="sm md" :to="{ name: 'auth.reset.request' }" class="q-ml-sm"/>
      </div>
    </q-form>

    <q-btn label="asdsa" @click="test">asdsad</q-btn>
    <!-- Footer-->
    <div class="auth-footer q-mt-xl">
      <div class="or-hr q-mb-xl"><span>or</span></div>

      <!-- Register Link-->
      <div class="register-actions">
        <q-btn :to="{ name: 'auth.register' }" :label="$t('Register')" type="button" color="primary"  outline icon="email" padding="sm" class="full-width"/>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import {extractPhone} from 'components/PhoneValidation/PhoneCodeList';
import {createMetaMixin} from 'quasar';
import { initializeApp } from "firebase/app";
import { getMessaging } from "firebase/messaging";
const firebaseConfig = {
  apiKey: "AIzaSyC_ZbH7KHkv9s72ZEgjZbwduYBmFR2aN_E",
  authDomain: "yeyee-app.firebaseapp.com",
  projectId: "yeyee-app",
  storageBucket: "yeyee-app.appspot.com",
  messagingSenderId: "858568967918",
  appId: "1:858568967918:web:1355ef685eb5b69a4b95ff",
  measurementId: "G-KW1YHX0979"
};
const app = initializeApp(firebaseConfig);

export default defineComponent({
  name: 'AuthLogin',
  mixins: [
    createMetaMixin({
      title: 'Login'
    })
  ],
  data() {
    return {
      isPwd: true,
      username: 'demo@demo.com',
      password: '123123123',
    }
  },
  computed: {
    getCountry() {
      return !isNaN(this.username) ? extractPhone(this.username)?.country : null
    }
  },
  methods: {
    test() {
      console.log(
        getMessaging(app)
      );
    },
    onSubmit() {
      console.log(
        this.$api.securityLogin({
          username: this.username,
          password: this.password
        })
      )

      this.$refs.form.validate().then((success: any) => {
        if (success) {
        }
      })
    }
  }
})
</script>

<style lang="scss" scoped>
.or-hr {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;

  &:before {
    content: " ";
    position: absolute;
    height: 2px;
    left: 0;
    width: 100%;
    background: $grey-4;
  }

  span {
    background: #FFF;
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
