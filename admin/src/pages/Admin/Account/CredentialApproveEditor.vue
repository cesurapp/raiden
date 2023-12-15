<template>
  <SimpleDialog ref="editor" no-backdrop-dismiss>
    <template #header>
      <q-avatar :icon="mdiSecurity" color="primary" text-color="white" class="self-start" />
      <span class="q-ml-md text-h6">
        <template v-if="mode === 'approve'">
          <template v-if="credential === 'email'">{{ $t('Verify Email') }}</template>
          <template v-else>{{ $t('Verify Phone') }}</template>
        </template>
        <template v-else>
          <template v-if="credential === 'email'">{{ $t('Update Email') }}</template>
          <template v-else>{{ $t('Update Phone') }}</template>
        </template>
      </span>
    </template>
    <template #content>
      <q-form @keydown.enter.prevent="approve" class="q-gutter-xs" ref="approveForm">
        <!--Change Mode-->
        <template v-if="mode === 'change'">
          <q-input
            v-if="credential === 'email'"
            outlined
            lazy-rules
            v-model="form.email"
            hide-bottom-space
            :label="$t('Email')"
            :error="$rules.ssrValid('email')"
            :error-message="$rules.ssrException('email')"
            :rules="[$rules.email()]"
          >
            <template v-slot:prepend>
              <q-icon :name="mdiEmail" />
            </template>
          </q-input>
          <PhoneInput
            v-else
            outlined
            :required="false"
            :label="$t('Phone')"
            :modelValue="form.phone"
            hide-bottom-space
            v-model:full-number="form.phone"
            v-model:phone-country="form.phone_country"
          ></PhoneInput>
        </template>

        <!--Approve Mode-->
        <template v-else>
          <q-banner rounded class="q-mb-lg" :class="$q.dark.isActive ? 'bg-green-9' : 'bg-green-2'">
            <span v-html="$t('send_verify_code').replace('msg', `<b>&quot;${form[credential]}&quot;</b>`)"></span>
          </q-banner>
          <q-input
            outlined
            lazy-rules
            fill-mask
            unmasked-value
            hide-bottom-space
            mask="# # # # # #"
            v-model="form.otp_key"
            :label="$t('Code')"
            :error="$rules.ssrValid('otp_key')"
            :error-message="$rules.ssrException('otp_key')"
            :rules="[$rules.required(), $rules.minLength(6), $rules.maxLength(6)]"
          >
            <template v-slot:prepend>
              <q-icon :name="mdiKey" />
            </template>
          </q-input>
        </template>
      </q-form>
    </template>
    <template #actions>
      <q-btn
        flat
        color="primary"
        :label="$t(mode === 'approve' ? 'Approve' : 'Update')"
        :icon="mdiContentSave"
        :loading="$appStore.isBusy"
        @click="approve()"
      ></q-btn>
    </template>
  </SimpleDialog>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiContentSave, mdiSecurity, mdiKey, mdiEmail } from '@quasar/extras/mdi-v7';
import PhoneInput from 'components/Phone/PhoneInput.vue';
import { MainCredentialsApproveRequest } from 'src/api/Request/MainCredentialsApproveRequest';
import SimpleDialog from 'components/SimpleDialog/Index.vue';

export default defineComponent({
  name: 'CredentialApproveEditor',
  components: { SimpleDialog, PhoneInput },
  setup: () => ({ mdiContentSave, mdiSecurity, mdiKey, mdiEmail }),
  data: () => ({
    mode: null,
    credential: null,
    form: {} as MainCredentialsApproveRequest,
  }),
  methods: {
    /**
     * Approve or Change User Credential
     */
    init(mode: 'approve' | 'change', credential: 'email' | 'phone') {
      this.mode = mode;
      this.credential = credential;
      this.form = {};

      // Approve Mode
      if (mode === 'approve') {
        if (credential === 'phone') {
          this.form.phone = this.$authStore.user.phone;
          this.form.phone_country = this.$authStore.user.phone_country;
        } else {
          this.form.email = this.$authStore.user.email;
        }

        this.$appStore.confirmPromise('mdiCheck', 'info', 'approve_message').then(() => {
          this.$refs.editor.toggle();
          this.$api.mainCredentialsRequest(this.form, { showMessage: false }).catch(() => {
            this.mode = 'change';
          });
        });

        return;
      }

      // Change Mode
      this.$refs.editor.toggle();
    },

    /**
     * Update
     */
    approve() {
      // Clear Backend Validation Errors
      this.$rules.clearSSRException();

      this.$refs.approveForm.validate().then((success: any) => {
        if (success) {
          // Generate OTP Key
          if (this.mode === 'change') {
            return this.$api.mainCredentialsRequest(this.form, { showMessage: false }).then(() => {
              this.mode = 'approve';
            });
          }

          // Approve
          this.$api.mainCredentialsApprove(this.form).then(() => {
            this.$refs.editor.toggle();

            // Update User State
            if (this.credential === 'phone') {
              this.$authStore.user.phone = this.form.phone;
              this.$authStore.user.phone_country = this.form.phone_country;
              this.$authStore.user.phone_approved = true;
            } else {
              this.$authStore.user.email = this.form.email;
              this.$authStore.user.email_approved = true;
            }
          });
        }
      });
    },
  },
});
</script>
