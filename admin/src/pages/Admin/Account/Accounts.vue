<template>
  <q-page>
    <PageContent borderless clear liquid>
      <SimpleTable
        ref="table"
        trans-key="account"
        :columns="AccountListTable"
        :request-prop="(req, config) => $api.admin.AccountList(req, config)"
        :delete-prop="(row) => $api.admin.AccountDelete(row.id)"
        :delete-permission="$permission.AdminAccount.DELETE"
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
            ><q-tooltip>{{ $t('New') }}</q-tooltip>
          </q-btn>
        </template>

        <!--Row Actions-->
        <template #rowActions="{ props }">
          <q-item
            clickable
            v-close-popup
            @click="$refs.editor.init(props.row)"
            :disable="!isEditable(props.row)"
            v-if="$authStore.hasPermission($permission.AdminAccount.EDIT)"
          >
            <q-item-section side><q-icon :name="mdiPencil" /></q-item-section>
            <q-item-section>{{ $t('Edit') }}</q-item-section>
          </q-item>
          <q-item
            clickable
            v-close-popup
            :disable="!isSwitchable(props.row)"
            @click="switchUser(props.row)"
            v-if="$authStore.hasPermission($permission.AdminCore.SWITCH)"
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
          {{ props.value && countries.hasOwnProperty(props.value) ? countries[props.value].name : '' }}
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
          <q-btn
            flat
            dense
            rounded
            size="sm"
            v-if="props.row.type !== UserType.USER"
            :icon="mdiMagnify"
            @click="
              selectedPerm = props.row;
              $refs.permViewer.toggle();
            "
          ></q-btn>
        </template>
      </SimpleTable>
    </PageContent>

    <!--User Editor-->
    <UserEditor ref="editor" @created="(item) => $refs.table.addFirst(item)"></UserEditor>

    <!--Permission Viewer-->
    <SimpleDialog ref="permViewer">
      <template #content>
        <div class="text-h5 q-mb-md q-mt-sm">{{ $t('Permissions') }}</div>
        <q-list bordered class="rounded-borders">
          <q-expansion-item
            :model-value="true"
            expand-separator
            :label="$t('perm_group.' + key)"
            :key="key"
            v-for="(perms, key) in $authStore.getReadablePermission($permission, selectedPerm)"
          >
            <q-card
              ><q-card-section
                ><div class="q-gutter-md">
                  <q-checkbox
                    dense
                    :model-value="true"
                    :label="$t('perm.' + permName)"
                    :key="permName"
                    v-for="(permVal, permName) in perms"
                  /></div></q-card-section
            ></q-card>
          </q-expansion-item>
        </q-list>
      </template>
      <template #actions>
        <q-btn flat :label="$t('Close')" color="primary" v-close-popup />
      </template>
    </SimpleDialog>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import SimpleTable from 'components/SimpleTable/Index.vue';
import AccountListTable from 'api/admin/table/AccountListTable';
import PageContent from 'components/Layout/PageContent.vue';
import { mdiPencil, mdiPlus, mdiCancel, mdiAccountMultipleOutline, mdiMagnify } from '@quasar/extras/mdi-v7';
import { UserType } from 'api/enum/UserType';
import UserEditor from 'pages/Admin/Account/UserEditor.vue';
import { UserResource } from 'api/admin/resource/UserResource';
import UserTypeInput from 'pages/Admin/Components/UserTypeInput.vue';
import LanguageHelper from 'src/helper/LanguageHelper';
import SimpleDialog from 'components/SimpleDialog/Index.vue';
import { countries } from 'countries-list';

export default defineComponent({
  name: 'AccountListing',
  components: { SimpleDialog, UserTypeInput, UserEditor, PageContent, SimpleTable },
  setup: () => ({
    AccountListTable,
    UserType,
    mdiPencil,
    mdiPlus,
    mdiCancel,
    mdiAccountMultipleOutline,
    mdiMagnify,
    LanguageHelper,
    countries,
  }),
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t(String(this.$route.meta.breadcrumb)),
      };
    }),
  ],
  data: () => ({
    selectedPerm: null,
  }),
  methods: {
    isEditable(user: UserResource) {
      if (user.type === UserType.SUPERADMIN) {
        return this.$authStore.user.type === UserType.SUPERADMIN;
      }

      return true;
    },
    isSwitchable(user: UserResource) {
      if (user.id === this.$authStore.user.id) {
        return false;
      }

      if (user.type === UserType.SUPERADMIN) {
        return this.$authStore.user.type === UserType.SUPERADMIN;
      }

      return user.type !== UserType.USER;
    },
    switchUser(user: UserResource) {
      this.$appStore
        .confirmInfo('Do you want to switch to the user')
        .then(() => this.$authStore.switchUser(user.email));
    },
  },
});
</script>
