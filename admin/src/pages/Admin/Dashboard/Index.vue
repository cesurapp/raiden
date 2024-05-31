<template>
  <q-page>
    <PageHeader liquid>Dashboard</PageHeader>
    <PageContent borderless liquid> Dashboard </PageContent>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import PageContent from 'components/Layout/PageContent.vue';
import PageHeader from 'components/Layout/PageHeader.vue';

export default defineComponent({
  name: 'AdminDashboard',
  components: { PageHeader, PageContent },
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t('Dashboard'),
      };
    }),
  ],
});
</script>
