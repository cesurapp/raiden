<template>
  <q-select :options="getOptions" emit-value map-options></q-select>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { UserType } from 'src/api/Enum/UserType';

export default defineComponent({
  name: 'UserTypeInput',
  setup: () => ({ UserType }),
  computed: {
    getOptions() {
      return Object.values(UserType).map((item) => ({
        label: this.$t(item),
        value: item,
      }));
    },
  },
});
</script>
