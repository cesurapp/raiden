<template>
  <q-page>
    <PageContent borderless liquid> </PageContent>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import PageContent from 'pages/Admin/Components/Layout/PageContent.vue';

export default defineComponent({
  name: 'AdminDashboard',
  components: { PageContent },
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t('Dashboard'),
      };
    }),
  ],
});
</script>
