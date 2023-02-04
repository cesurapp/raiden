<template>
  <div class="page-footer">
    <div
      :class="{
        'bg-dark': $q.dark.isActive && !borderless,
        'bg-white': !$q.dark.isActive && !borderless,
        'rounded-borders shadow-1 q-pa-md': !borderless,
        'content-fixed q-mx-md': !liquid,
        'content-liquid q-mx-md': liquid,
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
