<template>
  <q-page>
    <!--Page Header-->
    <PageHeader borderless liquid></PageHeader>

    <!--Page Content-->
    <PageContent liquid borderless>
      <q-form @keydown.enter.prevent="onSubmit" class="q-gutter-xs" ref="form">
        <!--Email-->
        <q-input
          outlined
          lazy-rules
          v-model="data.email"
          :label="$t('Email')"
          :error="$rules.ssrValid('email')"
          :error-message="$rules.ssrException('email')"
          :rules="[$rules.email()]"
        >
          <template v-slot:prepend><q-icon name="email" /></template>
        </q-input>

        <!--Phone-->
        <PhoneInput
          outlined
          ref="phone"
          v-model:phone-number="data.phone"
          v-model:phone-country="data.phone_country"
          :required="false"
          :label="$t('Phone')"
        ></PhoneInput>

        <!--Current Password-->
        <q-input
          filled
          :type="isPwd ? 'password' : 'text'"
          v-model="data.current_password"
          :label="$t('Current Password')"
          :error="$rules.ssrValid('current_password')"
          :error-message="$rules.ssrException('current_password')"
          lazy-rules
          :rules="[$rules.minLength(8)]"
        >
          <template v-slot:prepend><q-icon name="key" /></template>
          <template v-slot:append>
            <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="isPwd = !isPwd" />
          </template>
        </q-input>

        <!--Password-->
        <q-input
          filled
          :type="isPwd ? 'password' : 'text'"
          v-model="data.password"
          :label="$t('Password')"
          lazy-rules
          :rules="[$rules.minLength(8)]"
        >
          <template v-slot:prepend><q-icon name="key" /></template>
          <template v-slot:append>
            <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="isPwd = !isPwd" />
          </template>
        </q-input>

        <!--FirstName-->
        <q-input
          filled
          v-model="data.first_name"
          :label="$t('First Name')"
          lazy-rules
          :rules="[$rules.required(), $rules.minLength(2)]"
        >
          <template v-slot:prepend><q-icon name="person" /></template>
        </q-input>

        <!--LastName-->
        <q-input
          filled
          v-model="data.last_name"
          :label="$t('Last Name')"
          lazy-rules
          key="222"
          :rules="[$rules.required(), $rules.minLength(2)]"
        >
          <template v-slot:prepend><q-icon name="person" /></template>
        </q-input>

        <!--Actions-->
        <div>
          <q-btn
            :label="$t('Save')"
            @click="onSubmit"
            :loading="$isBusy.value"
            no-caps
            color="primary"
            icon="how_to_reg"
          />
        </div>
      </q-form>
    </PageContent>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import PageHeader from '../Components/Layout/PageHeader.vue';
import PageContent from '../Components/Layout/PageContent.vue';
import PhoneInput from 'components/Phone/PhoneInput.vue';
import { createMetaMixin } from 'quasar';

export default defineComponent({
  name: 'EditProfile',
  components: { PageHeader, PageContent, PhoneInput },
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t('Edit Profile'),
      };
    }),
  ],
  data: () => ({
    isPwd: true,
    data: {
      email: null,
      phone: null,
      phone_country: null,
      password: null,
      current_password: null,
      first_name: null,
      last_name: null,
    },
  }),
  created() {
    Object.keys(this.data).forEach((id) => {
      if (this.$authStore.user.hasOwnProperty(id)) {
        this.data[id] = this.$authStore.user[id];
      }
    });
  },
  methods: {
    onSubmit() {
      // Clear Backend Validation Errors
      this.$rules.clearSSRException();

      // Register
      this.$refs.form.validate().then((success) => {
        if (success) {
          this.$api.accountEditProfile(this.data).then((r) => {
            this.$authStore.updateUser(r.data.data);
          });
        }
      });
    },
  },
});
</script>
