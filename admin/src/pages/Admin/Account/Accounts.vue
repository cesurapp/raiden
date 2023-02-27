<template>
  <q-page>
    <PageContent borderless clear liquid>
      <SimpleTable ref="table"
                   :columns="AccountListTable"
                   :column-config="columnConfig"
                   :request-prop='(req,config) => ($api.accountList(req,config))'
                   :delete-prop='(row) => ($api.accountDelete(row.id))'
      >
        <!--Selected Actions-->
        <template #selectedActions="{ props }">
          <q-btn color="secondary" size="12px" v-close-popup :icon="mdiCancel" @click="disableAll(props)"><q-tooltip>Devre Dışı Bırak</q-tooltip></q-btn>
        </template>

        <!--Table Actions-->
        <template #tableActions>
          <q-btn size="12px" v-close-popup color="green" :icon="mdiPlus" v-if="$authStore.hasPermission($permission.AdminAccount.CREATE)"><q-tooltip>{{ $t('New') }}</q-tooltip></q-btn>
        </template>

        <!--Row Actions-->
        <template #rowActions="{ props }">
          <q-item clickable v-close-popup @click="disableItem(props)">
            <q-item-section side><q-icon :name="mdiPencil" /></q-item-section>
            <q-item-section>{{ $t('Edit') }}</q-item-section>
          </q-item>
        </template>

        <!--Custom Filter-->
        <template #filter_id='{column, values}'>
          <q-input v-model='values[column.name]' :label='column.label || ""' :debounce='75' clearable class='q-mb-sm' outlined dense></q-input>
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

export default defineComponent({
  name: 'AccountListing',
  components: { PageContent, SimpleTable },
  setup: () => ({ AccountListTable, mdiDeleteOutline, mdiPencil, mdiPlus, mdiCancel }),
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t(String(this.$route.meta.breadcrumb)),
      };
    }),
  ],
  data: () => ({
    columnConfig: {
      email_approved: { format: (v) => `<span class='q-badge'>${v ? 'Yes' : 'No'}</span>` },
      roles: { format: (v) => `<span class='q-badge'>${v}</span>` },
    },
  }),
  methods: {
    disableItem(event, row, index) {
      console.log(event, row, index);
    },
    disableAll(props) {
      //console.log(props);
    },
  },
});
</script>
