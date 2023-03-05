<template>
  <q-dialog v-model="active" :maximized="$q.screen.lt.md" :no-backdrop-dismiss="persistent">
    <q-card class="simple-editor">
      <!--Header-->
      <q-toolbar class="bg-primary text-white">
        <q-btn flat dense disable v-if="icon" :icon="icon" size="15px" style="opacity: 1 !important" />
        <q-toolbar-title class="title">{{ updating ? $t(titleUpdate || '') : $t(titleCreate || '') }}</q-toolbar-title>
        <q-btn flat round dense :icon="mdiClose" @click="active = !active" />
      </q-toolbar>

      <q-card-section :class="{ 'bg-dark-page': $q.dark.isActive, 'q-pa-none': clean }" class="content">
        <!--Horizontal Tabs-->
        <template v-if="$slots.tabsHorizontal">
          <q-tabs
            v-model="getTab"
            inline-label
            align="justify"
            class="tab-horz shadow-1"
            :class="[$q.dark.isActive ? 'q-dark' : 'bg-grey-3']"
            ><slot name="tabsHorizontal"></slot
          ></q-tabs>
          <q-tab-panels
            v-model="getTab"
            animated
            swipeable
            vertical
            transition-prev="jump-up"
            transition-next="jump-up"
            class="borderless"
          >
            <slot name="tabsContent"></slot>
          </q-tab-panels>
        </template>

        <!--Vertical Tabs-->
        <q-splitter
          v-if="$slots.tabsVertical"
          v-model="splitterModel"
          :class="{ 'q-dark': $q.dark.isActive }"
          class="full-height"
        >
          <template v-slot:before
            ><q-tabs v-model="getTab" vertical class="q-py-sm" :class="{ 'q-dark': $q.dark.isActive }"
              ><slot name="tabsVertical"></slot></q-tabs
          ></template>
          <template v-slot:after>
            <q-tab-panels
              v-model="getTab"
              animated
              swipeable
              vertical
              transition-prev="jump-up"
              transition-next="jump-up"
              class="borderless"
            >
              <slot name="tabsContent"></slot>
            </q-tab-panels>
          </template>
        </q-splitter>

        <!--Raw Content-->
        <slot name="content" />
      </q-card-section>

      <!--Actions-->
      <q-card-actions align="between" :class="[$q.dark.isActive ? 'bg-dark' : 'bg-grey-3']">
        <div><slot name="actionsLeft" /></div>
        <div>
          <q-btn flat :label="$t('Cancel')" :icon="mdiClose" color="negative" v-close-popup />
          <slot name="actionsRight" />
        </div>
      </q-card-actions>
    </q-card>
  </q-dialog>
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
    tab: String,
    updating: Boolean,
    icon: String,
    titleCreate: String,
    titleUpdate: String,
  },
  data: () => ({
    active: false,
    splitterModel: 20,
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
  },
});
</script>

<style lang="scss">
.simple-editor {
  display: flex;
  flex-direction: column;
  overflow: hidden;

  .q-toolbar__title {
    line-height: 24px;
    padding-left: 4px;
  }

  .q-toolbar {
    position: sticky;
    top: 0;
    z-index: 3;
  }

  .q-card__actions {
    position: sticky;
    bottom: 0;
    z-index: 3;
  }

  .content {
    overflow: auto;
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
    min-height: 46px;
    border-radius: 0;
  }
  .tab-horz {
    position: sticky;
    top: 0;
    z-index: 3;
  }

  .q-tab-panels {
    height: 100%;
  }
  .q-tab-panels.q-dark {
    background: var(--q-dark-page);
  }
  .q-panel > div {
    height: auto;
  }
}

.screen--md,
.screen--lg,
.screen--xl {
  .simple-editor {
    max-width: 1140px;
    width: 700px;
  }
}

.q-dialog__inner--maximized .simple-editor {
  display: flex;
  flex-direction: column;

  .content {
    flex: 1;
  }
}
</style>
