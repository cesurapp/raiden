<template>
  <q-dialog v-model="active" :maximized="$q.screen.lt.md" :no-backdrop-dismiss="persistent">
    <q-card class="simple-editor">
      <!--Header-->
      <q-toolbar class="bg-primary text-white">
        <q-btn flat dense disable v-if="icon" :icon="icon" size="15px" style="opacity: 1 !important" />
        <q-toolbar-title class="title">{{ updating ? $t(titleUpdate || '') : $t(titleCreate || '') }}</q-toolbar-title>
        <q-btn flat round dense :icon="mdiClose" @click="active = !active" />
      </q-toolbar>

      <!--Form Content-->
      <q-card-section :class="{ 'bg-dark-page': $q.dark.isActive }" class="q-pt-lg content">
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
    updating: Boolean,
    icon: String,
    titleCreate: String,
    titleUpdate: String,
  },
  data: () => ({
    active: false,
  }),
  methods: {
    toggle() {
      this.active = !this.active;
    },
  },
});
</script>

<style lang="scss">
.simple-editor {
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
}

.screen--md,
.screen--lg,
.screen--xl {
  .simple-editor {
    max-width: 1140px;
    width: 650px;
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
