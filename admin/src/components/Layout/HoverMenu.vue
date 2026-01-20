<template>
  <q-item @mouseover="isOpen = true; cancelClose()" @mouseleave="scheduleClose">
    <!--SetIcon-->
    <q-item-section avatar v-if="icon" @click.stop.prevent>
      <q-icon :name="icon"/>
    </q-item-section>

    <!--Set Menu-->
    <q-menu
      class="main-nav hover-menu-list bg-dark"
      autoClose
      v-model="isOpen"
      anchor="top end"
      self="top start"
      :offset="[10, 36]"
      transition-hide="jump-up"
      transition-show="jump-down"
      @mouseover="cancelClose()"
      @mouseleave="scheduleClose"
    >
      <slot></slot>
    </q-menu>
  </q-item>
</template>

<script lang="ts">
import {defineComponent} from 'vue';

export default defineComponent({
  name: 'HoverMenu',
  props: {
    icon: {
      type: String,
      default: '',
    },
  },
  data: () => ({
    isOpen: false,
    closeTimer: null as number | null
  }),
  methods: {
    scheduleClose() {
      if (this.closeTimer) {
        clearTimeout(this.closeTimer);
        this.closeTimer = null;
      }
      this.closeTimer = window.setTimeout(() => {
        this.isOpen = false;
        this.closeTimer = null;
      }, 150);
    },
    cancelClose() {
      if (this.closeTimer) {
        clearTimeout(this.closeTimer);
        this.closeTimer = null;
      }
    }
  }
});
</script>

<style lang="scss">
.hover-menu-list{
  min-width: 170px;
  .q-item{
    min-height: 34px;
  }

  .q-item__section--avatar{
    min-width: 30px !important;
    padding-right: 10px !important;
  }
}
</style>
