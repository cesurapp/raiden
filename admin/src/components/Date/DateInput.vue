<template>
  <q-input
    lazy-rules
    clearable
    readonly
    @clear="getDate = null"
    v-bind="getAttr"
    :model-value="getDate"
    @click="$refs.dateProxy.show()"
  >
    <template v-slot:append>
      <q-icon :name="mdiCalendar" class="cursor-pointer q-mr-sm">
        <q-popup-proxy
          :breakpoint="600"
          cover
          ref="dateProxy"
          transition-show="scale"
          transition-hide="scale"
          class="datepopup"
        >
          <q-date
            minimal
            :options="dateRules ? dateRules : undefined"
            v-model="getDate"
            :mask="timer ? $appStore.dateTimeFormat : $appStore.dateFormat"
            :locale="getCurrentLocale()"
            @update:modelValue="$refs.dateProxy.hide()"
          ></q-date>
        </q-popup-proxy>
      </q-icon>
      <q-icon v-if="timer" :name="mdiClockOutline" class="cursor-pointer">
        <q-popup-proxy :breakpoint="600" cover transition-show="scale" transition-hide="scale" class="datepopup">
          <q-time v-model="getDate" :mask="timer ? $appStore.dateTimeFormat : $appStore.dateFormat" format24h></q-time>
        </q-popup-proxy>
      </q-icon>
      <q-separator v-if="getDate" inset vertical spaced></q-separator>
      <q-icon v-if="getDate" :name="mdiCloseCircle" @click.prevent="getDate = null" class="cursor-pointer" />
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
    dateRules: null,
  },
  setup: () => ({ mdiCalendar, mdiClockOutline, mdiCloseCircle, getCurrentLocale }),
  computed: {
    getDate: {
      get() {
        return this.$attrs.modelValue ? this.$appStore.formatDate(this.$attrs.modelValue) : null;
      },
      set(val) {
        this.$emit('update:modelValue', val ? this.$appStore.inputDate(val) : null);
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
