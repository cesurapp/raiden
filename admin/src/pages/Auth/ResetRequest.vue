<template>
  <div>
    <!--Header-->
    <div class="q-mb-xl">
      <h4 class="q-mt-none q-mb-sm text-h4 text-weight-medium">{{ $t('Forgot Password') }}</h4>
      <h6 class="q-ma-none text-grey-7 text-subtitle1">{{ $t('Reset password by Email or Phone.') }}</h6>
    </div>

    <q-form @keydown.enter.prevent="onSubmit" class="q-gutter-xs" ref="form">
      <q-tabs v-model="type" inline-label no-caps active-bg-color="dark-transparent-1" class="text-primary q-mb-lg">
        <q-tab name="email" icon="mail" :label="$t('Email')" />
        <q-tab name="phone" icon="phone" :label="$t('Phone')" />
      </q-tabs>

      <!--Username-->
      <q-input v-if="type === 'email'" bottom-slots outlined v-model="username" :label="$t('Email')" lazy-rules :rules="[$rules.required(),$rules.email()]">
        <template v-slot:prepend><q-icon name="mail"/></template>
      </q-input>

      <!--Phone-->
      <PhoneInput v-else v-model="username" :label="$t('Phone')"></PhoneInput>

      <div>
        <q-btn :label="$t('Send')" @click="onSubmit" padding="sm md" type="button" color="primary" icon="login"/>
        <q-btn :label="$t('Login')" padding="sm md" color="primary" flat :to="{ name: 'auth.login' }" class="q-ml-sm"/>
      </div>
    </q-form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import PhoneInput from 'components/PhoneValidation/PhoneInput.vue';

export default defineComponent({
  name: 'ResetRequest',
  components: {PhoneInput},
  data() {
    return {
      type: 'email',
      username: null,
    }
  },
  methods: {
    onSubmit() {
      this.$refs.form.validate().then((success: boolean) => {
        if (success) {
          this.$api.securityResetRequest({username: this.username}).then((r) => {
            this.$router.push({name: 'auth.reset.password', params:{id: r.data}})
          })
        }
      })
    }
  }
})
</script>
