<template>
  <q-page>
    <PageContent borderless clear liquid>
      <SimpleTable
        ref="table"
        trans-key="devices"
        :export-button="false"
        :columns="DeviceListTable"
        :request-prop="(req, config) => $api.admin.DeviceList(req, config)"
        :delete-prop="(row) => $api.admin.DeviceDelete(row.id)"
        :delete-permission="$permission.AdminDevice.DELETE"
      >
        <!--Row Actions-->
        <template #rowActions="{ props }">
          <q-item
            clickable
            v-close-popup
            @click="$refs.editor.init(props.row.id)"
            v-if="$authStore.hasPermission($permission.AdminDevice.SEND)"
          >
            <q-item-section side><q-icon :name="mdiSend" /></q-item-section>
            <q-item-section>{{ $t('Send') }}</q-item-section>
          </q-item>
        </template>

        <!--Custom Column Filter-->
        <template #filter_type="{ column, values, refresh }">
          <q-select
            emit-value
            map-options
            clearable
            outlined
            dense
            class="q-mb-sm"
            style="min-width: 200px"
            multiple
            v-model="values[column.name]"
            :options="[
              { label: 'Web', value: DeviceType.WEB },
              { label: 'Android', value: DeviceType.ANDROID },
              { label: 'Ios', value: DeviceType.IOS },
            ]"
            :label="column.label || ''"
            @update:modelValue="refresh"
          ></q-select>
        </template>
        <template #filter_owner_type="{ column, values, refresh }">
          <UserTypeInput
            v-model="values[column.name]"
            :label="column.label || ''"
            @update:modelValue="refresh"
            multiple
            clearable
            class="q-mb-sm"
            outlined
            dense
            style="min-width: 200px"
          ></UserTypeInput>
        </template>

        <!--Custom Column Template-->
        <template #column_type="{ props }">
          {{ props.value[0].toUpperCase() + props.value.slice(1) }}
        </template>
        <template #column_owner_type="{ props }">
          {{ $t(props.value) }}
        </template>
      </SimpleTable>
    </PageContent>

    <DeviceSendEditor ref="editor"></DeviceSendEditor>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import SimpleTable from 'components/SimpleTable/Index.vue';
import PageContent from 'components/Layout/PageContent.vue';
import { mdiSend } from '@quasar/extras/mdi-v7';
import DeviceListTable from 'api/admin/table/DeviceListTable';
import UserTypeInput from 'pages/Admin/Components/UserTypeInput.vue';
import { DeviceType } from 'api/enum/DeviceType';
import DeviceSendEditor from 'pages/Admin/Firebase/DeviceSendEditor.vue';

export default defineComponent({
  name: 'FirebaseDeviceListing',
  components: { DeviceSendEditor, UserTypeInput, PageContent, SimpleTable },
  setup: () => ({ DeviceListTable, DeviceType, mdiSend }),
});
</script>
