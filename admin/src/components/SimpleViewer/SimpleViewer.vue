<template>
  <div class="simple-viewer">
    <q-drawer v-model="open" side="right" overlay elevated behavior="mobile" :width="width">
      <div v-if="$slots.title" class="title q-py-sm q-px-md text-weight-medium bg-primary text-white text-subtitle1 overflow-hidden ellipsis sticky-top shadow-bottom">
        <div class="flex items-center gap-x-md no-wrap full-width">
          <slot name="icon"></slot>
          <span class="overflow-hidden ellipsis"><slot name="title"></slot></span>
          <q-btn class="q-ml-auto" style="margin-right: -8px" flat round dense :icon="mdiClose" @click="toggle" />
        </div>
      </div>

      <div class="content">
        <slot name="content"></slot>
      </div>

      <q-inner-loading :showing="$appStore.isBusy" style="margin-top: 60px">
        <q-spinner-gears size="50px" color="primary" />
      </q-inner-loading>
    </q-drawer>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiClose } from '@quasar/extras/mdi-v7';

export default defineComponent({
  setup: () => ({mdiClose}),
  props: {
    width: {
      type: Number,
      default: 350,
    },
  },
  data: () => ({
    open: false,
  }),
  methods: {
    init() {
      this.open = true;
    },
    toggle() {
      this.open = !this.open;
    },
  },
});
</script>

<style lang="scss">
.simple-viewer {
  .q-drawer {
    max-width: 87vw;
  }
  .q-drawer__content {
    padding-top: 0 !important;
  }

  .title {
    display: flex;
    min-height: 52px;
    padding-top: max(env(safe-area-inset-top), 8px);
  }

  .q-table th:first-of-type,
  .q-table td:first-of-type {
    padding-left: 16px !important;
  }
  .q-table td:last-of-type {
    padding-right: 16px !important;
  }
}
</style>
