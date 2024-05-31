<template>
  <q-page>
    <PageContent borderless clear liquid>
      <SimpleTable
        ref="table"
        trans-key="scheduler"
        :export-button="false"
        :columns="AdminSchedulerListTable"
        :request-prop="(req, config) => $api.adminSchedulerList(req, config)"
        :delete-prop="(row) => $api.adminSchedulerDelete(row.id)"
        :delete-permission="$permission.AdminScheduler.DELETE"
      >
        <template #tableActions>
          <q-btn
            size="12px"
            v-close-popup
            color="green"
            :icon="mdiPlus"
            v-if="$authStore.hasPermission($permission.AdminScheduler.CREATE)"
            @click="$refs.editor.init(null)"
          >
            <q-tooltip>{{ $t('New') }}</q-tooltip>
          </q-btn>
        </template>

        <!--Row Actions-->
        <template #rowActions="{ props }">
          <q-item
            clickable
            v-close-popup
            @click="$refs.editor.init(props.row)"
            :disable="props.row.sended"
            v-if="$authStore.hasPermission($permission.AdminScheduler.EDIT)"
          >
            <q-item-section side><q-icon :name="mdiPencil" /></q-item-section>
            <q-item-section>{{ $t('Edit') }}</q-item-section>
          </q-item>
        </template>

        <!--Custom Column Template-->
        <template #column_persist_notification="{ props }">
          <q-badge color="positive" :label="$t('Yes')" v-if="props.value"></q-badge>
          <q-badge color="secondary" :label="$t('No')" v-else></q-badge>
        </template>
        <template #column_status="{ props }">
          <q-badge
            color="secondary"
            class="text-capitalize"
            :label="props.value"
            v-if="props.value === 'init'"
          ></q-badge>
          <q-badge
            color="warning"
            class="text-capitalize"
            :label="props.value"
            v-else-if="props.value === 'processing'"
          ></q-badge>
          <q-badge
            color="positive"
            class="text-capitalize"
            :label="props.value"
            v-else-if="props.value === 'sended'"
          ></q-badge>
          <q-badge color="negative" class="text-capitalize" :label="props.value" v-else></q-badge>
        </template>
      </SimpleTable>
    </PageContent>

    <SchedulerEditor
      ref="editor"
      @created="(item) => $refs.table.addFirst(item)"
      @updated="(item) => $refs.table.updateItem(item, 'id')"
    ></SchedulerEditor>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import SimpleTable from 'components/SimpleTable/Index.vue';
import PageContent from 'components/Layout/PageContent.vue';
import { mdiPlus, mdiPencil } from '@quasar/extras/mdi-v7';
import AdminSchedulerListTable from 'src/api/Table/AdminSchedulerListTable';
import SchedulerEditor from 'pages/Admin/Firebase/SchedulerEditor.vue';

export default defineComponent({
  name: 'AccountListing',
  components: { SchedulerEditor, PageContent, SimpleTable },
  setup: () => ({ AdminSchedulerListTable, mdiPlus, mdiPencil }),
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t(String(this.$route.meta.breadcrumb)),
      };
    }),
  ],
});
</script>
