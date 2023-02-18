<template>
  <q-page class="row q-mx-md q-my-md borderless" style="width: 200px"> </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';

export default defineComponent({
  name: 'AdminDashboard',
  data: () => ({
    confirm: false,
    current: 5,
    options: ['Google', 'Facebook', 'Twitter', 'Apple', 'Oracle'],
    group: 'op1',
    options2: [
      {
        label: 'Option 1',
        value: 'op1',
      },
      {
        label: 'Option 2',
        value: 'op2',
      },
      {
        label: 'Option 3',
        value: 'op3',
      },
    ],
    model22: { from: '2020/07/08', to: '2020/07/17' },
    model2: null,
    model: null,
    teal: true,
    orange: false,
    red: false,
    cyan: true,
    value: false,
  }),
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t('Dashboard'),
      };
    }),
  ],
  methods: {
    test() {
      // Super
      this.$authStore.switchUser('asd@sadsa.com');
    },
  },
});
</script>
