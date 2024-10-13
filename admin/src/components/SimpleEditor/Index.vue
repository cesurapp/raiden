<template>
  <!--Modal Mode-->
  <q-dialog v-if="!inline" v-model="active" :maximized="$q.screen.lt.md" :no-backdrop-dismiss="persistent" class="dialog-main">
    <q-card class="simple-editor" :style="width ? `width: ${width}px` : ''">
      <!--Header-->
      <q-toolbar class="text-white">
        <q-btn flat dense class="q-mr-xs" disable v-if="icon" :icon="icon" style="opacity: 1 !important" />
        <q-toolbar-title class="title">{{ updating ? $t(titleUpdate || '') : $t(titleCreate || '') }}</q-toolbar-title>

        <slot name="headerActions"></slot>
        <q-btn class="q-ml-sm" flat round dense :icon="mdiClose" @click="active = !active" />
      </q-toolbar>

      <q-card-section :class="{ 'bg-dark-page': $q.dark.isActive, 'q-pa-none': clean, raw: $slots.content }" class="content">
        <!--Raw Content-->
        <slot v-if="$slots.content" name="content" />

        <!--Tabs-->
        <template v-else-if="$q.screen.lt.sm">
          <q-tabs
            v-model="getTab"
            inline-label
            align="justify"
            class="tab-horz shadow-1"
            :class="[$q.dark.isActive ? 'q-dark' : 'bg-grey-3']"
            ><slot name="tabs"></slot
          ></q-tabs>
          <q-tab-panels v-model="getTab" animated vertical transition-prev="jump-up" transition-next="jump-up" class="borderless">
            <slot name="tabsContent"></slot>
          </q-tab-panels>
        </template>
        <template v-else>
          <q-splitter
            :model-value="$q.screen.lt.sm ? 65 : 150"
            :class="[$q.dark.isActive ? 'q-dark' : 'bg-grey-3']"
            unit="px"
            :limits="[$q.screen.lt.sm ? 65 : 150, Infinity]"
            class="full-height"
            separator-style="background: transparent; width: 0px"
          >
            <template v-slot:before>
              <q-tabs v-model="getTab" vertical class="q-py-sm vertical-tabs">
                <slot name="tabs"></slot>
              </q-tabs>
            </template>
            <template v-slot:after>
              <q-tab-panels
                v-model="getTab"
                animated
                vertical
                transition-prev="jump-up"
                transition-next="jump-up"
                class="borderless full-height"
              >
                <slot name="tabsContent"></slot>
              </q-tab-panels>
            </template>
          </q-splitter>
        </template>
      </q-card-section>

      <!--Actions-->
      <q-card-actions
        align="between"
        v-if="$slots.actionsLeft || $slots.actionsRight"
        :class="[$q.dark.isActive ? 'bg-dark' : 'bg-grey-3']"
      >
        <div><slot name="actionsLeft" /></div>
        <div class="q-gutter-xs">
          <q-btn flat :label="$t('Cancel')" v-if="!noCancel" :icon="mdiClose" color="negative" v-close-popup />
          <slot name="actionsRight" />
        </div>
      </q-card-actions>
    </q-card>
  </q-dialog>

  <!--Inline Mode-->
  <div v-else class="full-width inline-mode">
    <div class="content">
      <!--Raw Content-->
      <slot v-if="$slots.content" name="content" />

      <!--Tabs-->
      <template v-else>
        <q-tabs v-model="getTab" inline-label narrow-indicator align="left" class="z3">
          <slot name="tabs"></slot>
        </q-tabs>
        <q-tab-panels
          v-model="getTab"
          animated
          vertical
          transition-prev="jump-up"
          transition-next="jump-up"
          class="borderless q-mx-lg-sm q-pt-sm q-mt-xs"
          :class="{ 'bg-dark-page': $q.dark.isActive }"
        >
          <slot name="tabsContent"></slot>
        </q-tab-panels>
      </template>
    </div>

    <!--Actions-->
    <div class="flex justify-between q-mx-md q-mx-lg-lg q-mb-md">
      <div class="flex items-center q-gutter-sm" v-if="$slots.actionsLeft"><slot name="actionsLeft" /></div>
      <div class="flex items-center q-gutter-sm"><slot name="actionsRight" /></div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import { mdiClose, mdiAccount } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'SimpleEditor',
  setup: () => ({ mdiClose, mdiAccount }),
  props: {
    persistent: {
      type: Boolean,
      default: true,
    },
    clean: {
      type: Boolean,
      default: false,
    },
    noCancel: {
      type: Boolean,
      default: false,
    },
    inline: {
      type: Boolean,
      default: false,
    },
    width: {
      type: Number,
    },
    tab: String,
    updating: Boolean,
    icon: String,
    titleCreate: String,
    titleUpdate: String,
  },
  data: () => ({
    active: false,
    splitterModel: 65,
  }),
  computed: {
    getTab: {
      get() {
        return this.tab;
      },
      set(val) {
        this.$emit('update:tab', val);
      },
    },
  },
  methods: {
    toggle() {
      this.active = !this.active;
    },
    close() {
      this.active = false;
    },
  },
});
</script>

<style lang="scss">
.simple-editor {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: transparent;

  .q-toolbar__title {
    line-height: 24px;
    padding-left: 4px;
  }

  .q-toolbar {
    background: var(--q-primary);
    padding: max(6.5px, calc(env(safe-area-inset-top))) calc(env(safe-area-inset-right) / 2 + 12px) 6.5px
      calc(env(safe-area-inset-left) / 2 + 16px);
    position: sticky;
    top: 0;
    z-index: 3;
  }

  .q-card__actions {
    position: sticky;
    bottom: 0;
    z-index: 3;
    padding-bottom: calc(env(safe-area-inset-bottom) / 2 + 8px);
    padding-left: calc(env(safe-area-inset-left) / 2 + 12px);
    padding-right: calc(env(safe-area-inset-right) / 2 + 12px);
  }

  .content {
    overflow: auto;
    background: #fff;
    &.raw {
      padding-left: calc(env(safe-area-inset-left) / 2 + 16px);
      padding-right: calc(env(safe-area-inset-right) / 2 + 16px);

      &.q-pa-none {
        padding-left: calc(env(safe-area-inset-left) / 2);
        padding-right: calc(env(safe-area-inset-right) / 2);
      }
    }
  }

  // Vertical Tab
  .q-splitter__panel.q-splitter__before {
    position: sticky;
    top: 0;
  }
  .q-splitter--vertical > .q-splitter__after {
    height: auto;
  }

  .q-tabs--vertical .q-tab {
    min-height: 72px;
    border-radius: 0;
  }

  .q-tabs--horizontal .q-tab {
    border-radius: 0;
  }
  .tab-horz {
    position: sticky;
    top: 0;
    z-index: 3;
  }
  .q-tab-panels.q-dark {
    background: var(--q-dark-page);
  }
  .content:not(.raw) .q-panel > div {
    height: auto;
    padding-right: calc(env(safe-area-inset-right) / 2 + 16px);
  }
}

.screen--xs {
  .simple-editor {
    .vertical-tabs {
      .q-tab__label {
        display: none;
      }
    }
  }
}
.screen--md,
.screen--lg,
.screen--xl {
  .simple-editor {
    max-width: 1140px;
    width: 850px;
  }
}

.q-dialog__inner--maximized .simple-editor {
  display: flex;
  flex-direction: column;

  width: 100% !important;

  .content {
    flex: 1;
  }
}

body.q-android-padding .dialog-main,
body.q-ios-padding .dialog-main {
  .q-dialog__inner {
    padding-top: 0 !important;

    & > div {
      height: 100vh !important;
      max-height: 100vh !important;
    }
  }
}

.inline-mode {
  .q-tabs {
    position: sticky;
    top: 50px;

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
}
</style>
