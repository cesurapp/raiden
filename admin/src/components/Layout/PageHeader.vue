<template>
  <div
    class="page-header"
    :class="{
      borderless: borderless,
      tabbed: $slots.tabs,
      'bg-white': borderless && !$q.dark.isActive,
      'bg-light': !borderless && !$q.dark.isActive,
      'bg-dark-page': borderless && $q.dark.isActive,
      'bg-dark': !borderless && $q.dark.isActive,
    }"
  >
    <div :class="{ 'content-fixed q-mx-md q-mx-lg-lg': !liquid, 'content-liquid': liquid }">
      <div
        class="flex items-center justify-between wrapper"
        :class="{
          'q-px-md q-px-lg-lg': liquid && !borderless,
          'q-mx-md q-mx-lg-lg': borderless,
        }"
      >
        <div class="flex items-center">
          <NavigationToggle></NavigationToggle>
          <div class="title text-h6">
            <slot name="title">{{ $t($route.meta?.breadcrumb as string ?? '') }}</slot>
          </div>
        </div>
        <div class="header-actions" v-if="$slots.headerActions">
          <q-btn-group unelevated class="xs-hide" v-if="!$q.screen.xs"><slot name="headerActions"></slot></q-btn-group>
          <q-btn-dropdown
            v-else
            size="12px"
            padding="5px 6px"
            :dropdown-icon="mdiDotsVertical"
            content-class="shadow-0 transparent-dropdown"
            dense
            outline
            rounded
            :menu-offset="[0, 10]"
          >
            <div class="column q-gutter-sm"><slot name="headerActions"></slot></div>
          </q-btn-dropdown>
        </div>
      </div>

      <!--Tabs-->
      <slot name="tabs"></slot>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import NavigationToggle from 'components/Layout/NavigationToggle.vue';
import { mdiDotsVertical } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'PageHeader',
  setup: () => ({ mdiDotsVertical }),
  components: { NavigationToggle },
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
.page-header {
  display: flex;
  justify-content: center;
  position: sticky;
  top: 0;
  z-index: 3;
  // box-shadow: 0 0 3px 1px rgba(0, 0, 0, 0.1);

  .wrapper {
    padding-bottom: 6px;
    padding-top: max(calc(env(safe-area-inset-top) + 4px), 6px);
    min-height: var(--header-size);
    position: relative;
    z-index: 2;
    .mobile & {
      padding-bottom: 12px;
    }
  }

  .q-tabs {
    position: sticky;
    top: 50px;
    margin-top: -4px;

    &:after {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: $separator-color;
      content: ' ';
      z-index: -1;

      .body--dark & {
        background: rgba(255, 255, 255, 0.13);
      }
    }

    .q-tab {
      border-radius: 0;
      @media (min-width: $breakpoint-lg-min) {
        padding: 0 24px;
      }
    }
  }
  .q-tab-panel {
    padding-bottom: 0;
  }

  .title{
    font-size: 1.15rem;
  }

  &.borderless {
    //box-shadow: none;

    &.tabbed .wrapper {
      border-bottom: none;
    }

    .wrapper {
      border-bottom: 1px solid $separator-color;
      margin-left: 0;
      margin-right: 0;
      padding-left: calc(env(safe-area-inset-left) / 2 + 16px);
      padding-right: calc(env(safe-area-inset-right) / 2 + 16px);
    }

    .content-fixed .wrapper {
      padding-left: 0;
      padding-right: 0;
      margin-left: calc(env(safe-area-inset-left) / 2);
      margin-right: calc(env(safe-area-inset-right) / 2);
    }

    &.dark {
      .wrapper {
        border-bottom-color: rgba(255, 255, 255, 0.15);
      }
    }
  }

  .content-fixed {
    width: 100%;
    max-width: 1140px;
    .wrapper {
      padding-left: calc(env(safe-area-inset-left) / 2);
      padding-right: calc(env(safe-area-inset-right) / 2);
    }
  }

  .content-liquid {
    width: 100%;
    .wrapper {
      padding-left: calc(env(safe-area-inset-left) / 2 + 16px);
      padding-right: calc(env(safe-area-inset-right) / 2 + 16px);
    }
  }

  .title-area:not(:empty) + .title {
    display: none;
  }
}

.screen--xl,
.screen--lg {
  .page-header {
    &.borderless {
      .content-fixed .wrapper {
        margin-left: 0;
        margin-right: 0;
      }
      .wrapper {
        padding-left: calc(env(safe-area-inset-left) / 2 + 24px);
        padding-right: calc(env(safe-area-inset-right) / 2 + 24px);
      }
    }

    .wrapper {
      padding-left: calc(env(safe-area-inset-left) / 2 + 24px);
      padding-right: calc(env(safe-area-inset-right) / 2 + 24px);
    }

    .content-fixed {
      .wrapper {
        padding-left: calc(env(safe-area-inset-left) / 2);
        padding-right: calc(env(safe-area-inset-right) / 2);
      }
    }
  }
}
</style>
