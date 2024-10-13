<template>
  <q-btn
    flat
    dense
    round
    class="q-mr-sm navToggle"
    :icon="mdiMenu"
    @click="$appStore.navMini = !$appStore.navMini"
    v-if="$q.platform.is.desktop"
  />
  <q-btn flat dense round class="q-mr-sm navToggle" :icon="mdiMenu" @click="$appStore.navMenu = !$appStore.navMenu" v-else />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiMenu } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'NavigationToggle',
  setup: () => ({ mdiMenu }),
});
</script>

<style lang="scss">
.navToggle {
  margin-left: -7px;
}
</style>
