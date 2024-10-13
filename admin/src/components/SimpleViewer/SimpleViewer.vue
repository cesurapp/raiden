<template>
  <div class="simple-viewer">
    <q-drawer v-model="open" side="right" overlay elevated behavior="mobile" :width="width">
      <div
        v-if="$slots.title"
        class="title q-pa-md text-weight-medium bg-primary text-white text-subtitle1 overflow-hidden ellipsis sticky-top shadow-bottom"
      >
        <div class="flex items-center gap-x-md">
          <slot name="icon"></slot>
          <slot name="title"></slot>
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

export default defineComponent({
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
    padding-top: max(env(safe-area-inset-top), 16px);
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
