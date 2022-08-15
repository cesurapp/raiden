<template>
  <q-page>
    <!--Page Header-->
    <PageHeader>
      <template #tabs>
        <q-route-tab to="/" name="mailss" label="Home" no-caps/>
        <q-route-tab to="/accounts" name="mails" label="Şifremi Değiştir" no-caps/>
      </template>
      <template #actions>
        <q-btn rounded no-caps size="md" color="primary" icon="add" />
        <q-btn rounded no-caps size="md" color="primary" icon="add" />
      </template>
    </PageHeader>

    <!--Page Content-->
    <PageContent>
      content
    </PageContent>

    <!--Page Footer-->
    <PageFooter borderless liquid>
      Footer
    </PageFooter>
  </q-page>
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import {createMetaMixin} from 'quasar';
import PageHeader from '../Components/PageHeader.vue';
import PageContent from '../Components/PageContent.vue';
import PageFooter from '../Components/PageFooter.vue';

export default defineComponent({
  name: 'AccountListing',
  components: {PageHeader, PageContent, PageFooter},
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t(String(this.$route.meta.breadcrumb))
      }
    })
  ],
  data: () => ({
    tab: null
  })
});
</script>
