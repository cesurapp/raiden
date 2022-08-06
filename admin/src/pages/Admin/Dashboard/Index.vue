<template>
  <q-page class="row items-center justify-evenly">
    <q-btn label="asdass" @click="test"></q-btn>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'AdminDashboard',
  methods:{
    test() {
      this.$api.accountMe()
    }
  }
});
</script>
