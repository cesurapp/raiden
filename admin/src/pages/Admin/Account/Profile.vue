<template>
  <q-page>
    <!--Page Header-->
    <PageHeader liquid borderless></PageHeader>

    <!--Page Content-->
    <PageContent borderless liquid>
      <q-form @keydown.enter.prevent="onSubmit" class="q-gutter-xs" ref="form">
        <!--Email-->
        <q-input outlined lazy-rules readonly v-model="$authStore.user.email" :label="$t('Email')" bottom-slots>
          <template v-slot:prepend><q-icon :name="mdiEmail" /></template>
          <template #append>
            <template v-if="$authStore.user.email">
              <q-btn
                flat
                size="12px"
                color="negative"
                v-if="!$authStore.user.email_approved"
                :icon="mdiCheckDecagram"
                @click="$refs.approveEditor.init('approve', 'email')"
              >
                <q-tooltip>{{ $t('Verify') }}</q-tooltip>
              </q-btn>
              <q-btn flat size="12px" color="positive" disable v-else :icon="mdiCheckDecagram"></q-btn>
            </template>
            <q-btn flat size="12px" :icon="mdiPencil" @click="$refs.approveEditor.init('change', 'email')">
              <q-tooltip>{{ $t('Update') }}</q-tooltip>
            </q-btn>
          </template>
        </q-input>

        <!--Phone-->
        <PhoneInput
          :label="$t('Phone')"
          outlined
          readonly
          v-model="$authStore.user.phone"
          v-model:fullNumber="$authStore.user.phone"
          v-model:phoneCountry="$authStore.user.phone_country"
          :required="false"
          :error="false"
        >
          <template #append>
            <template v-if="$authStore.user.phone">
              <q-btn
                flat
                size="12px"
                color="negative"
                v-if="!$authStore.user.phone_approved"
                :icon="mdiCheckDecagram"
                @click="$refs.approveEditor.init('approve', 'phone')"
              >
                <q-tooltip>{{ $t('Verify') }}</q-tooltip>
              </q-btn>
              <q-btn flat size="12px" color="positive" disable v-else :icon="mdiCheckDecagram"></q-btn>
            </template>
            <q-btn flat size="12px" :icon="mdiPencil" @click="$refs.approveEditor.init('change', 'phone')">
              <q-tooltip>{{ $t('Update') }}</q-tooltip>
            </q-btn>
          </template>
        </PhoneInput>

        <!--FirstName-->
        <q-input
          outlined
          lazy-rules
          v-model="data.first_name"
          :label="$t('First Name')"
          :rules="[$rules.required(), $rules.minLength(2)]"
        >
          <template v-slot:prepend><q-icon :name="mdiAccount" /></template>
        </q-input>

        <!--LastName-->
        <q-input
          outlined
          lazy-rules
          v-model="data.last_name"
          :label="$t('Last Name')"
          :rules="[$rules.required(), $rules.minLength(2)]"
        >
          <template v-slot:prepend><q-icon :name="mdiAccount" /></template>
        </q-input>

        <!-- Language -->
        <LanguageInput v-model="data.language"></LanguageInput>

        <!--Current Password-->
        <q-input
          outlined
          autocomplete
          :type="isPwd ? 'password' : 'text'"
          v-model="data.current_password"
          :label="$t('Current Password')"
          :error="$rules.ssrValid('current_password')"
          :error-message="$rules.ssrException('current_password')"
          lazy-rules
          :rules="[$rules.minLength(8)]"
        >
          <template v-slot:prepend><q-icon :name="mdiKey" /></template>
          <template v-slot:append>
            <q-icon :name="isPwd ? mdiEyeOff : mdiEye" class="cursor-pointer" @click="isPwd = !isPwd" />
          </template>
        </q-input>

        <!--Password-->
        <q-input
          outlined
          autocomplete
          :type="isPwd ? 'password' : 'text'"
          v-model="data.password"
          :label="$t('Password')"
          lazy-rules
          :rules="[$rules.minLength(8)]"
        >
          <template v-slot:prepend><q-icon :name="mdiKey" /></template>
          <template v-slot:append>
            <q-icon :name="isPwd ? mdiEyeOff : mdiEye" class="cursor-pointer" @click="isPwd = !isPwd" />
          </template>
        </q-input>

        <!--Actions-->
        <div>
          <q-btn :label="$t('Save')" @click="onSubmit" :loading="$appStore.isBusy" color="primary" :icon="mdiAccountPlus" />
        </div>
      </q-form>
    </PageContent>

    <CredentialApproveEditor ref="approveEditor"></CredentialApproveEditor>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import PageHeader from 'components/Layout/PageHeader.vue';
import PageContent from 'components/Layout/PageContent.vue';
import PhoneInput from 'components/Localization/PhoneInput.vue';
import LanguageInput from 'components/Language/LanguageInput.vue';
import CredentialApproveEditor from './CredentialApproveEditor.vue';
import {
  mdiEmail,
  mdiAccount,
  mdiWeb,
  mdiKey,
  mdiAccountPlus,
  mdiEye,
  mdiEyeOff,
  mdiCheckDecagram,
  mdiPencil,
} from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'EditProfile',
  setup: () => ({
    mdiEmail,
    mdiAccount,
    mdiWeb,
    mdiKey,
    mdiAccountPlus,
    mdiEye,
    mdiEyeOff,
    mdiCheckDecagram,
    mdiPencil,
  }),
  components: { CredentialApproveEditor, LanguageInput, PageHeader, PageContent, PhoneInput },
  data: () => ({
    isPwd: true,
    data: {
      first_name: null,
      last_name: null,
      language: null,
      current_password: null,
      password: null,
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
          this.$api.main.ProfileEdit(this.data).then((r) => {
            this.$authStore.updateUser(r.data.data);
          });
        }
      });
    },
  },
});
</script>
