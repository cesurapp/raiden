<template>
  <SimpleEditor
    :class="{ borderless: $q.dark.isActive }"
    ref="editor"
    :updating="form.id !== undefined"
    :icon="mdiAccount"
    title-create="New User"
    title-update="Edit User"
  >
    <template #content>
      <q-form @keydown.enter.prevent="save" class="q-gutter-xs" ref="form">
        <!--FirstName-->
        <q-input
          outlined
          lazy-rules
          v-model="form.first_name"
          :label="$t('First Name')"
          :rules="[$rules.required(), $rules.minLength(2)]"
        ></q-input>

        <!--LastName-->
        <q-input
          outlined
          lazy-rules
          v-model="form.last_name"
          :label="$t('Last Name')"
          :rules="[$rules.required(), $rules.minLength(2)]"
        ></q-input>

        <!--Email-->
        <q-input
          outlined
          lazy-rules
          v-model="form.email"
          :label="$t('Email')"
          :error="$rules.ssrValid('email')"
          :error-message="$rules.ssrException('email')"
          :rules="[$rules.email()]"
        ></q-input>

        <!--Phone-->
        <PhoneInput
          outlined
          :required="false"
          :label="$t('Phone')"
          :modelValue="form.phone"
          v-model:full-number="form.phone"
          v-model:phone-country="form.phone_country"
          :error="$rules.ssrValid('phone')"
          :error-message="$rules.ssrException('phone')"
        ></PhoneInput>

        <!-- Language -->
        <LanguageInput v-model="form.language"></LanguageInput>

        <!--Password-->
        <q-input
          outlined
          lazy-rules
          :type="isPwd ? 'password' : 'text'"
          v-model="form.password"
          :label="$t('Password')"
          :rules="[$rules.minLength(8)]"
        >
          <template v-slot:append>
            <q-icon :name="isPwd ? mdiEyeOff : mdiEye" class="cursor-pointer" @click="isPwd = !isPwd" />
          </template>
        </q-input>

        <!--UserType-->
        <q-select
          bottom-slots
          outlined
          lazy-rules
          label="Tür"
          v-model="form.type"
          :options="Object.values(UserType)"
          :error="$rules.ssrValid('type')"
          :error-message="$rules.ssrException('type')"
        ></q-select>

        <!--Email Approved-->
        <q-checkbox :label="$t('Email Approved')" v-model="form.email_approved" :indeterminate-value="0" />

        <!--Phone Approved-->
        <q-checkbox :label="$t('Phone Approved')" v-model="form.phone_approved" :indeterminate-value="0" />

        <!--Frozen-->
        <q-checkbox :label="$t('Frozen')" v-model="form.frozen" :indeterminate-value="0" />
      </q-form>
    </template>
    <template #actionsRight>
      <q-btn
        flat
        :label="$t('Save')"
        color="primary"
        :icon="mdiContentSave"
        @click="save"
        :loading="$appStore.isBusy"
      ></q-btn>
    </template>
  </SimpleEditor>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import SimpleEditor from 'components/SimpleEditor/Index.vue';
import { AccountEditRequest } from 'src/api/Request/AccountEditRequest';
import { mdiAccount, mdiContentSave, mdiEyeOff, mdiEye } from '@quasar/extras/mdi-v7';
import PhoneInput from 'components/Phone/PhoneInput.vue';
import LanguageInput from 'components/Language/LanguageInput.vue';
import { UserType } from 'src/api/Enum/UserType';

export default defineComponent({
  name: 'UserEditor',
  components: { LanguageInput, PhoneInput, SimpleEditor },
  setup: () => ({ mdiAccount, mdiContentSave, mdiEyeOff, mdiEye, UserType }),
  data: () => ({
    isPwd: false,
    form: {} as AccountEditRequest,
  }),
  methods: {
    /**
     * Create or Edit Current Proxy Object
     */
    init(user: AccountEditRequest | object = {}) {
      this.form = user;
      this.$refs.editor.toggle();
    },

    /**
     * Load and Show Editor
     */
    load(id: string) {
      this.$api.accountShow(id).then((r) => {
        this.form = r.data.data;
        this.$refs.editor.toggle();
      });
    },

    /**
     * Create or Update
     */
    save() {
      if (this.form.id) {
        return this.$api.accountEdit(this.form.id, this.form).then((r) => {
          console.log(r);
        });
      }

      this.$api.accountCreate(this.form).then((r) => {
        this.$emit('created', r.data.data);
      });
    },
  },
});
</script>
