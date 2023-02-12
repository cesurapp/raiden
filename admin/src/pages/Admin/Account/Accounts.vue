<template>
  <q-page>
    <!--Page Header-->
    <PageHeader liquid>
      <!--<template #tabs>
        <q-route-tab to="/" name="mailss" label="Home" no-caps />
        <q-route-tab to="/accounts" name="mails" label="Change Password" no-caps />
      </template>-->
      <!--<template #actions>
        <q-btn rounded size="md" color="primary" icon="add" v-if="$authStore.hasPermission('ROLE_ACCOUNT_CREATE')">
          <q-tooltip>{{ $t('Create') }}</q-tooltip>
        </q-btn>
      </template>-->
    </PageHeader>

    <!--Page Content-->
    <PageContent liquid></PageContent>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
import PageHeader from '../Components/Layout/PageHeader.vue';
import PageContent from '../Components/Layout/PageContent.vue';

export default defineComponent({
  name: 'AccountListing',
  components: { PageHeader, PageContent },
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t(String(this.$route.meta.breadcrumb)),
      };
    }),
  ],
});
</script>
