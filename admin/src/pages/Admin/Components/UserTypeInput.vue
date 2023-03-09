<template>
  <q-select :options="getOptions" emit-value map-options></q-select>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { UserType } from 'src/api/Enum/UserType';

export default defineComponent({
  name: 'UserTypeInput',
  setup: () => ({ UserType }),
  props: {
    excluded: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
    getOptions() {
      return Object.values(UserType)
        .filter((x) => !this.excluded.includes(x))
        .map((item) => ({
          label: this.$t(item),
          value: item,
        }));
    },
  },
});
</script>
