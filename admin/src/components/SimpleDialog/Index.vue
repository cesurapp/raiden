<template>
  <q-dialog v-model="active">
    <q-card :style="{ minWidth: width }" class="simple-dialog">
      <q-card-section v-if="$slots.header" class="flex items-center header q-pb-sm">
        <slot name="header" />
        <q-space />
        <q-btn :icon="mdiClose" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section
        class="scroll content"
        :class="{ 'q-pt-none': clean, 'q-pa-none': cleanForce, 'q-py-sm': !cleanForce }"
      >
        <slot name="content" />
      </q-card-section>

      <q-card-actions align="right" v-if="$slots.actions" :class="[$q.dark.isActive ? 'bg-dark' : 'bg-white']">
        <slot name="actions" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import { defineComponent } from 'vue';
import { mdiClose } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'SimpleDialog',
  setup: () => ({ mdiClose }),
  props: {
    width: {
      type: String,
      default: '320px',
    },
    clean: {
      type: Boolean,
      default: false,
    },
    cleanForce: {
      type: Boolean,
      default: false,
    },
  },
  data: () => ({
    active: false,
  }),
  methods: {
    toggle() {
      this.active = !this.active;
    },
  },
});
</script>

<style lang="scss">
.simple-dialog {
  .q-card__actions {
    position: sticky;
    bottom: 0;
  }
}
</style>
