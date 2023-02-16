<template>
  <div class="page-content">
    <div
      :class="{
        'bg-dark dark-shadow-1': $q.dark.isActive && !borderless,
        'bg-white shadow-1': !$q.dark.isActive && !borderless,
        'rounded-borders q-pa-md': !borderless,
        'content-fixed q-my-md': !liquid,
        'content-liquid q-my-md': liquid,
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
  name: 'PageContent',
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
.page-content {
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
