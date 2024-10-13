<template>
  <q-input
    :label="$t('Date')"
    :model-value="getDate ? `${getDate.from} - ${getDate.to}` : ''"
    v-bind="getAttr"
    @clear="getDate = null"
    clearable
    :debounce="75"
    dense
    readonly
    @click="$refs.dateProxy.show()"
  >
    <template #append>
      <q-icon :name="mdiCalendar" class="cursor-pointer">
        <q-popup-proxy :breakpoint="600" cover transition-show="scale" transition-hide="scale" ref="dateProxy" class="datepopup">
          <q-date
            v-model="getDate"
            range
            minimal
            :mask="$appStore.dateFormat"
            @rangeEnd="$refs.dateProxy.hide()"
            :locale="getCurrentLocale()"
          ></q-date>
        </q-popup-proxy>
      </q-icon>
      <q-separator v-if="getDate" inset vertical spaced></q-separator>
      <q-icon v-if="getDate" :name="mdiCloseCircle" @click.prevent="getDate = null" class="cursor-pointer" />
    </template>
  </q-input>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiCalendar, mdiCloseCircle } from '@quasar/extras/mdi-v7';
import { getCurrentLocale } from 'src/helper/DateHelper';

export default defineComponent({
  name: 'DateRangeInput',
  inheritAttrs: false,
  setup: () => ({ mdiCalendar, mdiCloseCircle, getCurrentLocale }),
  computed: {
    getDate: {
      get() {
        return this.$attrs.modelValue
          ? {
              from: this.$appStore.formatDate(this.$attrs.modelValue.from, false),
              to: this.$appStore.formatDate(this.$attrs.modelValue.to, false),
            }
          : null;
      },
      set(val) {
        this.$emit(
          'update:modelValue',
          val
            ? {
                from: this.$appStore.inputDate(val.from, false),
                to: this.$appStore.inputDate(val.to, false),
              }
            : null,
        );
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
