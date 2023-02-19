<template>
  <div class="page-content">
    <div
      :class="{
        'bg-dark dark-shadow-1': $q.dark.isActive && !borderless,
        'bg-white shadow-1': !$q.dark.isActive && !borderless,
        'rounded-borders q-pa-md': !borderless,
        'content-fixed': !liquid,
        'content-liquid': liquid,
        'q-mx-md q-mx-lg-lg q-my-md': !clear,
        'cleared': clear,
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

  .content-fixed {
    width: 100%;
    max-width: 1140px;
  }

  .content-liquid {
    width: 100%;
  }

  .cleared {
    .q-table__card{
      border-radius: 0;
    }

    .q-table th:first-of-type,
    .q-table td:first-of-type {
      padding-left: 24px;
    }
    .q-table th:last-of-type,
    .q-table td:last-of-type {
      padding-right: 24px;
    }
  }
}
</style>
