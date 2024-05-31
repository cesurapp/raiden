<template>
  <div class="page-content">
    <div
      :class="{
        'bg-dark dark-shadow-1': $q.dark.isActive && !borderless,
        'bg-white shadow-1': !$q.dark.isActive && !borderless,
        'rounded-borders q-pa-md': !borderless,
        'content-fixed': !liquid,
        'content-liquid': liquid,
        'q-my-md q-pt-sm': !clear,
        cleared: clear,
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
  flex: 1;
  width: 100%;

  .body--dark & {
    background: var(--q-dark-page);
  }
  .content-fixed {
    width: 100%;
    max-width: 1140px;
    &:not(.cleared) {
      #margin-left: calc(env(safe-area-inset-left) / 2 + 32px);
      #margin-right: calc(env(safe-area-inset-right) / 2 + 32px);
    }
  }

  .content-liquid {
    width: 100%;
    &:not(.cleared) {
      margin-left: calc(env(safe-area-inset-left) / 2 + 32px);
      margin-right: calc(env(safe-area-inset-right) / 2 + 32px);

      .screen--xs &,
      .screen--sm & {
        margin-left: calc(env(safe-area-inset-left) / 2 + 16px);
        margin-right: calc(env(safe-area-inset-right) / 2 + 16px);
      }
    }
  }

  .cleared {
    .q-table__card {
      border-radius: 0;
    }

    .q-table th:first-of-type,
    .q-table td:first-of-type,
    .q-table__bottom {
      padding-left: 32px;
      .screen--xs &,
      .screen--sm & {
        padding-left: 16px;
      }
    }
    .q-table th:last-of-type,
    .q-table td:last-of-type,
    .q-table__bottom {
      padding-right: 32px;
    }
  }
}
</style>
