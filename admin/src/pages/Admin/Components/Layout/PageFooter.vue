<template>
  <div class="page-footer">
    <div
      :class="{
        'bg-dark dark-shadow-1': $q.dark.isActive && !borderless,
        'bg-white shadow-1': !$q.dark.isActive && !borderless,
        'rounded-borders q-pa-md': !borderless,
        'content-fixed': !liquid,
        'content-liquid': liquid,
        'q-mx-md q-mx-lg-lg': !clear,
        'borderless': borderless
      }"
    >
      <slot></slot>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'PageFooter',
  props: {
    liquid: {
      type: Boolean,
      default: false,
    },
    borderless: {
      type: Boolean,
      default: false,
    },
    clear: {
      type: Boolean,
      default: false,
    },
  },
});
</script>

<style lang="scss">
.page-footer {
  display: flex;
  justify-content: center;

  .content-fixed {
    width: 100%;
    max-width: 1140px;
  }

  .content-liquid {
    width: 100%;
  }
}
</style>
