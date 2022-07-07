<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">Forgot Password</h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">Reset password by Email or Phone.</h6>
    </div>

    <!-- Login Form-->
    <q-form @submit="onSubmit" @reset="onReset" class="q-gutter-sm" ref="form">
      <!--Username-->
      <q-input outlined bottom-slots v-model="identity" label="Kullanıcı Adı" lazy-rules :rules="[ val => val && val.length > 0 || 'Please type something']">
        <template v-slot:prepend><q-icon name="person"/></template>
      </q-input>

      <div>
        <q-btn label="Gönder" type="submit" color="primary" icon="login"/>
        <q-btn label="Giriş" color="primary" flat :to="{ name: 'auth.login' }" class="q-ml-sm"/>
      </div>
    </q-form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
  name: 'ResetRequest',
  data() {
    return {
      identity: null,
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
