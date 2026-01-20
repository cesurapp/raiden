<template>
  <q-btn
    flat
    dense
    round
    class="q-mr-sm navToggle"
    :icon="$appStore.navMini ? mdiMenuClose : mdiMenu"
    @click="$appStore.navMini = !$appStore.navMini"
    v-if="$q.platform.is.desktop"
    size="16px"
  />
  <q-btn
    flat
    dense
    round
    class="q-mr-sm navToggle"
    :icon="!$appStore.navMenu ? mdiMenu : mdiMenuClose"
    @click="$appStore.navMenu = !$appStore.navMenu"
    v-else
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiMenu, mdiMenuClose } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'NavigationToggle',
  setup: () => ({ mdiMenu, mdiMenuClose }),
});
</script>

<style lang="scss">
.navToggle {
  margin-left: -8px;
}
</style>
