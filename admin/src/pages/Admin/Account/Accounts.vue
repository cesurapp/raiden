<template>
  <q-page>
    <PageContent borderless clear liquid>
      <SimpleTable
        ref="table"
        trans-key="account"
        :columns="AccountListTable"
        :request-prop="(req, config) => $api.accountList(req, config)"
        :delete-prop="(row) => $api.accountDelete(row.id)"
      >
        <!--Selected Actions-->
        <!--<template #selectedActions="{ props }">
          <q-btn color="secondary" size="12px" v-close-popup :icon="mdiCancel" @click="disableAll(props)"><q-tooltip>Devre Dışı Bırak</q-tooltip></q-btn>
        </template>-->

        <!--Table Actions-->
        <template #tableActions>
          <q-btn
            size="12px"
            v-close-popup
            color="green"
            :icon="mdiPlus"
            v-if="$authStore.hasPermission($permission.AdminAccount.CREATE)"
            @click="$refs.editor.init()"
            ><q-tooltip>{{ $t('New') }}</q-tooltip></q-btn
          >
        </template>

        <!--Row Actions-->
        <template #rowActions="{ props }">
          <q-item
            clickable
            v-close-popup
            @click="$refs.editor.init(props.row)"
            v-if="$authStore.hasPermission($permission.AdminAccount.EDIT)"
          >
            <q-item-section side><q-icon :name="mdiPencil" /></q-item-section>
            <q-item-section>{{ $t('Edit') }}</q-item-section>
          </q-item>
          <q-item
            clickable
            v-close-popup
            :disable="props.row.type === UserType.USER"
            @click="switchUser(props.row)"
            v-if="$authStore.hasPermission($permission.AdminCore.ALLOWED_TO_SWITCH)"
          >
            <q-item-section side><q-icon :name="mdiAccountMultipleOutline" /></q-item-section>
            <q-item-section>{{ $t('Switch User') }}</q-item-section>
          </q-item>
        </template>

        <!--Custom Column Filter-->
        <template #filter_type="{ column, values, refresh }">
          <UserTypeInput
            v-model="values[column.name]"
            :label="column.label || ''"
            @update:modelValue="refresh"
            clearable
            class="q-mb-sm"
            outlined
            dense
            style="min-width: 200px"
          ></UserTypeInput>
        </template>

        <!--Custom Column Template-->
        <template #column_phone_country="{ props }">
          {{ props.value && CountryHelper.hasOwnProperty(props.value) ? CountryHelper[props.value].name : '' }}
        </template>
        <template #column_language="{ props }">
          {{ props.value && LanguageHelper.hasOwnProperty(props.value) ? LanguageHelper[props.value] : '' }}
        </template>
        <template #column_type="{ props }">
          {{ $t(props.value) }}
        </template>
        <template #column_email_approved="{ props }">
          <q-badge :color="props.value ? 'primary' : 'secondary'">{{ props.value ? $t('Yes') : $t('No') }}</q-badge>
        </template>
        <template #column_phone_approved="{ props }">
          <q-badge :color="props.value ? 'primary' : 'secondary'">{{ props.value ? $t('Yes') : $t('No') }}</q-badge>
        </template>
        <template #column_approved="{ props }">
          <q-badge :color="props.value ? 'primary' : 'secondary'">{{ props.value ? $t('Yes') : $t('No') }}</q-badge>
        </template>
        <template #column_frozen="{ props }">
          <q-badge :color="props.value ? 'negative' : 'primary'">{{ props.value ? $t('Yes') : $t('No') }}</q-badge>
        </template>
        <template #column_roles="{ props }">
          <q-badge v-for="role in props.value" :key="role">{{ role }}</q-badge>
        </template>
      </SimpleTable>
    </PageContent>

    <!--User Editor-->
    <UserEditor ref="editor" @created="(item) => $refs.table.addFirst(item)"></UserEditor>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import SimpleTable from 'components/SimpleTable/Index.vue';
import AccountListTable from 'src/api/Table/AccountListTable';
import PageContent from 'pages/Admin/Components/Layout/PageContent.vue';
import { mdiPencil, mdiPlus, mdiCancel, mdiAccountMultipleOutline } from '@quasar/extras/mdi-v7';
import { UserType } from 'src/api/Enum/UserType';
import UserEditor from 'pages/Admin/Account/UserEditor.vue';
import { UserResource } from 'src/api/Resource/UserResource';
import UserTypeInput from 'pages/Admin/Components/UserTypeInput.vue';
import LanguageHelper from 'src/helper/LanguageHelper';
import CountryHelper from 'src/helper/CountryHelper';

export default defineComponent({
  name: 'AccountListing',
  components: { UserTypeInput, UserEditor, PageContent, SimpleTable },
  setup: () => ({ AccountListTable, UserType, mdiPencil, mdiPlus, mdiCancel, mdiAccountMultipleOutline, CountryHelper, LanguageHelper }),
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t(String(this.$route.meta.breadcrumb)),
      };
    }),
  ],
  methods: {
    switchUser(user: UserResource) {
      if (user.id === this.$authStore.user.id) {
        return this.$appStore.notifyDanger(this.$t('You cannot switch to your own account!'));
      }

      this.$appStore
        .confirmInfo('Do you want to switch to the user')
        .then(() => this.$authStore.switchUser(user.email));
    },
  },
});
</script>
