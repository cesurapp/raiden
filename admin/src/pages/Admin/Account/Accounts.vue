<template>
  <q-page class="q-pa-md">
    <q-btn label="Deneme"></q-btn>
  </q-page>
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import {createMetaMixin} from 'quasar';

export default defineComponent({
  name: 'AccountListing',
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t(String(this.$route.meta.breadcrumb))
      }
    })
  ],
});
</script>
