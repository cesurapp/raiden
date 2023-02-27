<template>
  <q-page>
    <PageContent borderless liquid>
      <q-btn label="Open" @click="$appStore.notifyDanger('asdaas11')" :loading="$appStore.isBusy"></q-btn>
      <q-btn label="Open" @click="$appStore.notifyWarning('asdaas222')" :loading="$appStore.isBusy"></q-btn>
      <q-btn label="Open" @click="$appStore.notifyInfo('asdaas333', null, 90000)" :loading="$appStore.isBusy"></q-btn>
      <q-btn label="Open" @click="$appStore.notifySuccess('asdaas444')" :loading="$appStore.isBusy"></q-btn>

      <q-dialog v-model="fixed">
        <q-card>
          <SimpleTable></SimpleTable>
        </q-card>
      </q-dialog>
    </PageContent>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import PageContent from 'pages/Admin/Components/Layout/PageContent.vue';
import SimpleTable from 'components/SimpleTable/Index.vue';

export default defineComponent({
  name: 'AdminDashboard',
  components: { SimpleTable, PageContent },
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t('Dashboard'),
      };
    }),
  ],
  data: () => ({
    fixed: false,
  }),
  methods: {
    test() {
      this.$api.accountList();
      // Super
      //this.$authStore.switchUser('asd@sadsa.com');
    },
  },
});
</script>
