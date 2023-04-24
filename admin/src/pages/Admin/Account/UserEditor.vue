<template>
  <SimpleEditor
    ref="editor"
    clean
    :icon="mdiAccount"
    v-model:tab="tab"
    :updating="isUpdating"
    title-create="New User"
    title-update="Edit User"
  >
    <template #tabsVertical>
      <q-tab name="profile" :label="$t('Details')" class="text-primary" :icon="mdiAccount" />
      <q-tab
        name="permission"
        :label="$t('Permission')"
        class="text-red"
        :disable="!isPermissionEditor"
        :icon="mdiSecurity"
      />
    </template>

    <template #tabsContent>
      <!--Profile-->
      <q-tab-panel name="profile">
        <div class="text-h5 q-mb-lg">{{ $t('Details') }}</div>
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
          <UserTypeInput
            outlined
            lazy-rules
            label="Tür"
            v-model="form.type"
            :excluded="getTypeExcluded"
            :error="$rules.ssrValid('type')"
            :error-message="$rules.ssrException('type')"
          ></UserTypeInput>

          <!--Email Approved-->
          <q-checkbox :label="$t('Email Approved')" v-model="form.email_approved" />

          <!--Phone Approved-->
          <q-checkbox :label="$t('Phone Approved')" v-model="form.phone_approved" />

          <!--Frozen-->
          <q-checkbox :label="$t('Frozen')" v-model="form.frozen" />
        </q-form>
      </q-tab-panel>

      <!--Permission-->
      <q-tab-panel name="permission" v-if="isPermissionEditor">
        <div class="text-h5 q-mb-md">{{ $t('Permission') }}</div>
        <q-list bordered class="rounded-borders">
          <q-expansion-item
            expand-separator
            :disable="!checkPermGroup(key)"
            :label="$t('perm_group.' + key)"
            :key="key"
            v-for="(perms, key) in getAccessPermission"
          >
            <q-card
              ><q-card-section>
                <div class="q-gutter-md">
                  <q-checkbox
                    v-model="form.roles"
                    dense
                    :val="permVal"
                    :label="$t('perm.' + permName)"
                    :key="permName"
                    v-for="(permVal, permName) in perms"
                  />
                </div> </q-card-section
            ></q-card>
          </q-expansion-item>
        </q-list>
      </q-tab-panel>
    </template>

    <template #actionsRight>
      <q-btn
        flat
        :label="$t('Save')"
        color="primary"
        :icon="mdiContentSave"
        @click="tab === 'permission' ? savePermission() : save()"
        :loading="$appStore.isBusy"
      ></q-btn>
    </template>
  </SimpleEditor>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import SimpleEditor from 'components/SimpleEditor/Index.vue';
import { AccountEditRequest } from 'src/api/Request/AccountEditRequest';
import { mdiAccount, mdiContentSave, mdiEye, mdiEyeOff, mdiSecurity } from '@quasar/extras/mdi-v7';
import PhoneInput from 'components/Phone/PhoneInput.vue';
import LanguageInput from 'components/Language/LanguageInput.vue';
import { UserType } from 'src/api/Enum/UserType';
import UserTypeInput from 'pages/Admin/Components/UserTypeInput.vue';

export default defineComponent({
  name: 'UserEditor',
  components: { UserTypeInput, LanguageInput, PhoneInput, SimpleEditor },
  setup: () => ({ UserType, mdiAccount, mdiContentSave, mdiEyeOff, mdiEye, mdiSecurity }),
  data: () => ({
    isPwd: true,
    form: {} as AccountEditRequest,
    permissions: [],
    proxy: null,
    tab: 'profile',
  }),
  computed: {
    isUpdating() {
      return this.form.id !== undefined;
    },
    isPermissionEditor() {
      return (
        this.isUpdating &&
        ![UserType.USER, UserType.SUPERADMIN].includes(this.form.type ?? '') &&
        this.$authStore.hasPermission(this.$permission.AdminAccount.PERMISSION)
      );
    },
    getTypeExcluded() {
      return !this.$authStore.hasUserType(UserType.SUPERADMIN) ? [UserType.SUPERADMIN] : [];
    },
    getAccessPermission() {
      return this.$authStore.getReadablePermission(this.$permission);
    },
  },
  methods: {
    checkPermGroup(groupId) {
      return groupId.toUpperCase().startsWith(this.form.type.split('_')[1]);
    },

    /**
     * Create or Edit Current Proxy Object
     */
    init(user: AccountEditRequest | null = null) {
      this.tab = 'profile';
      this.proxy = user;
      this.form = user ? user : {
        phone_approved: true,
        email_approved: true,
        frozen: false,
      };

      this.$refs.editor.toggle();
    },

    /**
     * Load and Show Editor
     */
    load(id: string) {
      this.tab = 'profile';

      this.$api.accountShow(id).then((r) => {
        this.form = r.data.data;
        this.$refs.editor.toggle();
      });
    },

    /**
     * Create or Update
     */
    save() {
      // Clear Backend Validation Errors
      this.$rules.clearSSRException();

      this.$refs.form.validate().then((success: any) => {
        if (success) {
          // Edit
          if (this.form.hasOwnProperty('id')) {
            return this.$api.accountEdit(this.form.id, this.form).then((r) => {
              this.proxy = Object.assign(this.proxy || {}, r.data.data);
              this.$refs.editor.toggle();
            });
          }

          // Create
          this.$api.accountCreate(this.form).then((r) => {
            this.$emit('created', r.data.data);
            this.$refs.editor.toggle();
          });
        }
      });
    },

    /**
     * Save Permission
     */
    savePermission() {
      this.$api.accountEditPermission(this.form.id, { permissions: this.form.roles }).then(() => {
        this.proxy = Object.assign(this.proxy || {}, this.form);
      });
    },
  },
});
</script>
