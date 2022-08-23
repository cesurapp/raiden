<template>
  <q-page class="row items-center justify-evenly">
    <q-btn label="asdass" @click="test"></q-btn>
  </q-page>
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import {createMetaMixin} from 'quasar';

export default defineComponent({
  name: 'AdminDashboard',
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t('Dashboard')
      }
    })
  ],
  methods: {
    test() {
      this.$api.accountMe()
    }
  }
});
</script>
