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
        borderless: borderless,
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
    margin-left: calc(env(safe-area-inset-left) / 2 + 16px);
    margin-right: calc(env(safe-area-inset-right) / 2 + 16px);
  }

  .content-liquid {
    width: 100%;
    margin-left: calc(env(safe-area-inset-left) / 2 + 16px);
    margin-right: calc(env(safe-area-inset-right) / 2 + 16px);
  }
}

.screen--xl,
.screen--lg {
  .page-footer .content-liquid {
    margin-left: calc(env(safe-area-inset-left) / 2 + 24px);
    margin-right: calc(env(safe-area-inset-right) / 2 + 24px);
  }
}
</style>
