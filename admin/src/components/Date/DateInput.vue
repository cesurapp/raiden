<template>
  <q-input
    lazy-rules
    readonly
    clearable
    @clear="getDate = null"
    v-bind="getAttr"
    :model-value="getDate"
    @click="($refs.dateProxy as any).show()"
  >
    <template v-slot:append>
      <q-icon :name="mdiCalendar" class="cursor-pointer">
        <q-popup-proxy :breakpoint="600" cover ref="dateProxy" transition-show="scale" transition-hide="scale" class="datepopup">
          <q-date
            :options="dateRules ? dateRules : undefined"
            v-model="getDate"
            :mask="timer ? $appStore.dateTimeFormat : $appStore.dateFormat"
            :locale="getCurrentLocale()"
            @update:modelValue="($refs.dateProxy as any).hide()"
            today-btn
            :no-unset="!clearable"
          ></q-date>
        </q-popup-proxy>
      </q-icon>
      <q-icon v-if="timer" :name="mdiClockOutline" class="cursor-pointer q-ml-xs">
        <q-popup-proxy :breakpoint="600" cover transition-show="scale" transition-hide="scale" class="datepopup">
          <q-time v-model="getDate" :mask="timer ? $appStore.dateTimeFormat : $appStore.dateFormat" format24h now-btn></q-time>
        </q-popup-proxy>
      </q-icon>
      <template v-if="clearable && getDate">
        <q-separator class="q-ml-sm" inset vertical spaced></q-separator>
        <q-icon :name="mdiCloseCircle" @click.prevent="getDate = null" class="cursor-pointer" />
      </template>
    </template>
  </q-input>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiCalendar, mdiClockOutline, mdiCloseCircle } from '@quasar/extras/mdi-v7';
import { getCurrentLocale } from 'src/helper/DateHelper';

export default defineComponent({
  name: 'DateInput',
  inheritAttrs: false,
  props: {
    timer: {
      type: Boolean,
      default: false,
    },
    clearable: {
      type: Boolean,
      default: true,
    },
    dateRules: null,
  },
  setup: () => ({ mdiCalendar, mdiClockOutline, mdiCloseCircle, getCurrentLocale }),
  computed: {
    getDate: {
      get() {
        return this.$attrs.modelValue ? this.$appStore.formatDate(this.$attrs.modelValue, this.timer) : null;
      },
      set(val) {
        this.$emit('update:modelValue', val ? this.$appStore.inputDate(val, this.timer) : null);
      },
    },
    getAttr() {
      let attr = { ...this.$attrs };
      delete attr.modelValue;
      delete attr['onUpdate:modelValue'];
      return attr;
    },
  },
});
</script>
