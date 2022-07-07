<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">Welcome</h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">Login to continue</h6>
    </div>

    <!-- Login Form-->
    <q-form @submit="onSubmit" @reset="onReset" class="q-gutter-sm" ref="form">
      <!--Username-->
      <q-input outlined bottom-slots v-model="username" label="Kullanıcı Adı" lazy-rules :rules="[ val => val && val.length > 0 || 'Please type something']">
        <template v-slot:prepend><q-icon name="person"/></template>
      </q-input>

      <!--Password-->
      <q-input outlined bottom-slots v-model="password" label="Şifre">
        <template v-slot:prepend><q-icon name="key"/></template>
      </q-input>

      <div>
        <q-btn label="Giriş" type="submit" color="primary" icon="login"/>
        <q-btn label="Şifremi Unuttum" size="sm" color="primary" flat :to="{ name: 'auth.reset.request' }" class="q-ml-sm"/>
      </div>
    </q-form>

    <!-- Footer-->
    <div class="auth-footer q-mt-xl">
      <div class="or-hr q-mb-xl"><span>or</span></div>

      <!-- Register Link-->
      <div class="register-actions">
        <q-btn label="Kayıt Ol" type="button" color="primary" icon="fa-solid fa-envelope" padding="sm" class="full-width"/>
        <q-btn label="Google ile Giriş" type="button" color="primary" icon="fa-brands fa-google" padding="sm" class="full-width q-mt-sm"/>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
  name: 'Login',
  data() {
    return {
      username: null,
      password: null,
    }
  },
  methods: {
    onSubmit() {
      this.$refs.form.validate().then(success => {
        if (success) {
          this.$q.notify({
            position: 'top',
            color: 'red-5',
            textColor: 'white',
            icon: 'warning',
            message: 'You need to accept the license and terms first'
          })
        }
        else {
        }
      })
    },
    onReset() {
      this.username = null;
      this.password = null;
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
</style>
