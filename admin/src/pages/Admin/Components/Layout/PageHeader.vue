<template>
  <div
    class="page-header"
    :class="{
      bordered: !borderless,
      'bg-dark': $q.dark.isActive && !borderless,
      'bg-white': !$q.dark.isActive && !borderless,
    }"
  >
    <div
      :class="{
        'content-fixed q-mx-md q-mx-lg-lg': !liquid,
        'content-liquid': liquid,
      }"
    >
      <div
        class="title-area q-pt-md flex items-center justify-between"
        :class="{ 'q-px-md q-px-lg-lg': liquid, 'q-pb-md': !borderless && !$slots.tabs }"
      >
        <!--Title-->
        <div class="title text-h4">
          <slot name="title">{{ $t($route.meta?.breadcrumb ?? '') }}</slot>
        </div>

        <!--Actions-->
        <div class="actions" v-if="$slots.actions">
          <q-btn-group class="xs-hide"><slot name="actions"></slot></q-btn-group>
          <div class="sm-hide md-hide lg-hide xl-hide">
            <q-btn-dropdown
              dropdown-icon="more_vert"
              content-class="transparent shadow-0"
              dense
              outline
              auto-close
              rounded
              color="primary"
              class="action_dropdown"
            >
              <div class="column q-gutter-sm q-py-sm">
                <slot name="actions"></slot>
              </div>
            </q-btn-dropdown>
          </div>
        </div>
      </div>

      <!--Tabs-->
      <q-tabs
        v-if="$slots.tabs"
        v-model="tabs"
        dense
        align="left"
        :breakpoint="200"
        :narrow-indicator="liquid"
        class="bg-transparent text-primary page-tabs q-mt-sm"
        :class="{ borderless: borderless, 'q-mx-lg-sm': liquid }"
      >
        <slot name="tabs"></slot>
      </q-tabs>
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
}

.page-header {
  display: flex;
  justify-content: center;
  position: relative;

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
    font-size: 2rem;
  }

  .content-fixed {
    width: 100%;
    max-width: 1140px;
  }

  .content-liquid {
    width: 100%;
  }
}
</style>
