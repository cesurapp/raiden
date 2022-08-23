<template>
  <div class="page-header">
    <div class="title-area q-px-md q-pt-md q-pb-none flex items-center justify-between">
      <!--Title-->
      <div class="title text-h4">
        <slot name="title">{{ $t($route.meta?.breadcrumb ?? '') }}</slot>
      </div>

      <!--Actions-->
      <div class="actions" v-if="$slots.actions">
        <q-btn-group class="xs-hide"><slot name="actions"></slot></q-btn-group>
        <div class="sm-hide md-hide lg-hide">
          <q-btn-dropdown dropdown-icon="more_vert" content-class="transparent shadow-0" dense outline auto-close rounded color="primary" class="action_dropdown">
            <div class="column q-gutter-sm q-py-sm">
              <slot name="actions"></slot>
            </div>
          </q-btn-dropdown>
        </div>
      </div>
    </div>

    <!--Tabs-->
    <q-tabs v-if="$slots.tabs" v-model="tabs" dense align="left" :breakpoint="200" narrow-indicator class="bg-transparent text-primary page-tabs q-mt-sm">
      <slot name="tabs"></slot>
    </q-tabs>
  </div>
</template>

<script lang="ts">
import {defineComponent} from 'vue';

export default defineComponent({
  name: 'PageHeader',
  data: () => ({
    tabs: null,
  })
})
</script>

<style lang="scss">
.page-tabs{
  &::before {
    border-bottom: 1px solid #c3cfdd;
    content: " ";
    position: absolute;
    bottom: 0;
    left: $flex-gutter-md;
    right: $flex-gutter-md;
  }

  .q-tab__indicator{
    height: 3px;
    border-radius: 3px 3px 0 0;
  }

  &.q-tabs--dense .q-tab{
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
  .title {
    font-size: 2rem;
  }
}
</style>
