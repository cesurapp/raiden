<template>
  <div
    class="page-header"
    :class="{
      bordered: !borderless,
      'bg-dark': $q.dark.isActive && !borderless,
      'tabbed': $slots.tabs
    }"
  >
    <div :class="{ 'content-fixed q-mx-md q-mx-lg-lg': !liquid, 'content-liquid': liquid }">
      <div class="q-pt-smh flex items-center justify-between full-height" :class="{ 'q-px-md q-px-lg-lg': liquid, 'q-pb-smh': !borderless && !$slots.tabs }">
        <!--Title-->
        <div class='title text-h4'><slot name="title">{{ $t($route.meta?.breadcrumb ?? '') }}</slot></div>

        <!--Header Actions-->
        <div class="header-actions" v-if="$slots.headerActions">
          <q-btn-group unelevated class="xs-hide" v-if='!$q.screen.xs'><slot name="headerActions"></slot></q-btn-group>
          <q-btn-dropdown
            v-else
            dropdown-icon="more_vert"
            content-class="shadow-0 transparent-dropdown"
            dense
            outline
            rounded
            color="primary"
            :menu-offset='[0,12]'
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

export default defineComponent({
  name: 'PageHeader',
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
  data: () => ({
    tabs: null,
  }),
});
</script>

<style lang="scss">
.page-tabs {
  &.borderless::before {
    border-bottom: 1px solid #c3cfdd;
    content: ' ';
    position: absolute;
    bottom: 0;
    left: $flex-gutter-md;
    right: $flex-gutter-md;
  }

  .q-tab__indicator {
    height: 3px;
    border-radius: 3px 3px 0 0;
  }

  &.q-tabs--dense .q-tab {
    min-height: 40px;
  }
}

.body--dark {
  .page-tabs {
    &::before {
      border-bottom-color: rgba(255, 255, 255, 0.12);
    }
  }

  .page-header {
    &.bordered:before {
      background: rgba(0, 0, 0, 0.25);
    }
  }
}

.page-header {
  display: flex;
  justify-content: center;
  position: relative;
  min-height: 60px;

  &.bordered:before {
    background: rgba(0, 0, 0, 0.12);
    position: absolute;
    height: 1px;
    left: 0;
    right: 0;
    bottom: 0;
    content: ' ';
  }

  .title {
    font-size: 1.8rem;
    line-height: 1.8rem;
  }

  .content-fixed {
    width: 100%;
    max-width: 1140px;
  }

  .content-liquid {
    width: 100%;
  }

  .title-area:not(:empty) + .title {
    display: none;
  }
}
</style>
