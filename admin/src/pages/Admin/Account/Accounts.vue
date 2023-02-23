<template>
  <q-page>
    <PageContent borderless clear liquid>
      <SimpleTable ref='table' :table-columns='tableColumns' :table-column-config='columnConfig'>
        <!--Selected Actions-->
        <template #selectedActions='{props}'>
          <q-btn color='red' size='sm' unelevated icon='delete' @click='removeAll(props)'><q-tooltip>Tümünü Sil</q-tooltip></q-btn>
          <q-btn color='secondary' size='sm' unelevated icon='do_not_disturb_on' @click='disableAll(props)'><q-tooltip>Devre Dışı Bırak</q-tooltip></q-btn>
        </template>

        <!--Table Actions-->
        <template #tableActions>
          <q-btn size='12px' v-close-popup color='green' icon='add' v-if='$authStore.hasPermission($permission.AdminAccount.CREATE)'><q-tooltip>Yeni</q-tooltip></q-btn>
        </template>

        <!--Row Actions-->
        <template #rowActions='{props}'>
          <q-item clickable v-close-popup @click='disableItem(props)'>
            <q-item-section side><q-icon name='edit' /></q-item-section>
            <q-item-section>{{ $t('Edit') }}</q-item-section>
          </q-item>
          <q-item clickable v-close-popup class='text-red-5' @click='removeItem(props)'>
            <q-item-section side><q-icon color='red-5' name='delete' /></q-item-section>
            <q-item-section>{{ $t('Delete') }}</q-item-section>
          </q-item>
        </template>
      </SimpleTable>
    </PageContent>
  </q-page>
</template>

<script lang='ts'>
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import SimpleTable from 'components/SimpleTable/Index.vue';
import AccountListTable from 'src/api/Table/AccountListTable';
import PageContent from 'pages/Admin/Components/Layout/PageContent.vue';

export default defineComponent({
  name: 'AccountListing',
  components: { PageContent, SimpleTable },
  mixins: [
    createMetaMixin(function() {
      return {
        title: this.$t(String(this.$route.meta.breadcrumb))
      };
    })
  ],
  data: () => ({
    tableColumns: AccountListTable,
    columnConfig: {
      'email_approved': { format: (v) => (`<span class='q-badge'>${v ? 'Yes' : 'No'}</span>`) },
      'roles': { format: (v) => (`<span class='q-badge'>${v}</span>`) },
    }
  }),
  methods:{
    removeItem(props) {
      this.$appStore.confirmDelete().then(() => {
        this.$refs.table.removeIndex(props.rowIndex);
      })
    },
    removeAll(props) {
      this.$appStore.confirmDeleteAll().then(() => {
        props.forEach((item) => this.$refs.table.removeItem(item))
      })
    },
    disableItem(props) {
      console.log(props);
    },
    disableAll(props) {
      console.log(props);
    },
  }
});
</script>
