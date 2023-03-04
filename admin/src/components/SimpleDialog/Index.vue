<template>
  <q-dialog v-model="active">
    <q-card :style="{ minWidth: width }">
      <q-card-section v-if="$slots.header" class="flex items-center">
        <slot name="header" />
        <q-space />
        <q-btn :icon="mdiClose" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section class="q-pt-none">
        <slot name="content" />
      </q-card-section>

      <q-card-actions align="right" v-if="$slots.actions">
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
