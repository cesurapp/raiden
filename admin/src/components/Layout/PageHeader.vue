<template>
  <div
    class="page-header"
    :class="{
      borderless: borderless,
      tabbed: $slots.tabs,
      dark: $q.dark.isActive,
      'bg-white text-black': !borderless && !$q.dark.isActive,
      'bg-dark text-white': !borderless && $q.dark.isActive,
      'bg-dark-page': borderless && $q.dark.isActive,
      'bg-white': borderless,
    }"
  >
    <div :class="{ 'content-fixed q-mx-lg': !liquid, 'content-liquid': liquid }">
      <div
        class="flex items-center justify-between wrapper"
        :class="{
          'q-px-lg': liquid && !borderless,
          'q-mx-lg': borderless,
        }"
      >
        <div class="flex items-center">
          <NavigationToggle></NavigationToggle>
          <div class="title text-h6">
            <slot name="title">{{ $t($route.meta?.breadcrumb ?? '') }}</slot>
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

        <div class="header-actions" v-if="$slots.headerActionsNot">
          <slot name="headerActionsNot"></slot>
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
  min-height: 60px;
  box-shadow: 0 0 23px -5px rgba(0, 0, 0, 0.1);

  .body--dark & {
    box-shadow: 0 0 23px -5px rgba(0, 0, 0, 0.4);
  }

  .wrapper {
    padding-bottom: 8px;
    padding-top: max(env(safe-area-inset-top), 8px);
    min-height: 60px;
    position: relative;
    z-index: 2;
  }

  &.borderless {
    box-shadow: none;
    .wrapper {
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      padding-left: 0;
      padding-right: 0;
      margin-left: calc(env(safe-area-inset-left) / 2 + 32px);
      margin-right: calc(env(safe-area-inset-right) / 2 + 32px);
    }

    .content-fixed .wrapper {
      padding-left: 0;
      padding-right: 0;
      margin-left: calc(env(safe-area-inset-left) / 2);
      margin-right: calc(env(safe-area-inset-right) / 2);
    }

    .content-liquid .wrapper {
      padding-left: 0;
      padding-right: 0;
    }

    &.dark {
      .wrapper {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
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

.screen--md,
.screen--xl,
.screen--lg {
  .page-header {
    &.borderless {
      .content-fixed .wrapper {
        margin-left: 0;
        margin-right: 0;
      }
      .wrapper {
        padding-left: 0;
        padding-right: 0;
        margin-left: calc(env(safe-area-inset-left) / 2 + 32px);
        margin-right: calc(env(safe-area-inset-right) / 2 + 32px);
      }
    }

    .wrapper {
      padding-left: calc(env(safe-area-inset-left) / 2 + 32px);
      padding-right: calc(env(safe-area-inset-right) / 2 + 32px);
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
