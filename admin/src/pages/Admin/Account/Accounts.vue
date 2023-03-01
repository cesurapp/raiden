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
            ><q-tooltip>{{ $t('New') }}</q-tooltip></q-btn
          >
        </template>

        <!--Row Actions-->
        <template #rowActions="{ props }">
          <q-item clickable v-close-popup @click="editItem(props)">
            <q-item-section side><q-icon :name="mdiPencil" /></q-item-section>
            <q-item-section>{{ $t('Edit') }}</q-item-section>
          </q-item>
        </template>

        <!--Custom Column Filter-->
        <template #filter_type="{ column, values, refresh }">
          <q-select
            v-model="values[column.name]"
            :label="column.label || ''"
            :options="Object.values(UserType)"
            @update:modelValue="refresh"
            clearable
            class="q-mb-sm"
            outlined
            dense
            style="min-width: 200px"
          ></q-select>
        </template>

        <!--Custom Column Template-->
        <template #column_email_approved="{ props }">
          <q-badge :color="props.value ? 'primary' : 'secondary'">{{ props.value ? $t('Yes') : $t('No') }}</q-badge>
        </template>
        <template #column_roles="{ props }">
          <q-badge v-for="role in props.value" :key="role">{{ role }}</q-badge>
        </template>
      </SimpleTable>
    </PageContent>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import SimpleTable from 'components/SimpleTable/Index.vue';
import AccountListTable from 'src/api/Table/AccountListTable';
import PageContent from 'pages/Admin/Components/Layout/PageContent.vue';
import { mdiDeleteOutline, mdiPencil, mdiPlus, mdiCancel } from '@quasar/extras/mdi-v7';
import { UserType } from 'src/api/Enum/UserType';

export default defineComponent({
  name: 'AccountListing',
  components: { PageContent, SimpleTable },
  setup: () => ({ AccountListTable, mdiDeleteOutline, mdiPencil, mdiPlus, mdiCancel, UserType }),
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t(String(this.$route.meta.breadcrumb)),
      };
    }),
  ],
});
</script>
